<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Person;
use App\Models\PositionType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class UserController extends Controller
{
    /* ──────────────────────────────────
     *  INDEX — unified employees view
     * ────────────────────────────────── */
    public function index()
    {
        $grouped = User::with(['person', 'roles', 'area', 'positionType'])
            ->where('is_active', true)
            ->get()
            ->groupBy(fn ($u) => $u->area_id ?? 0);

        $allUsersGrouped = $grouped->sortBy(fn ($users, $key) => $key === 0 ? 'zzz' : ($users->first()->area?->name ?? 'zzz'));

        $areas = Area::where('is_active', true)->orderBy('name')->get();
        $totalActive   = User::where('is_active', true)->count();
        $totalInactive = User::where('is_active', false)->count();

        $areaStats = User::where('is_active', true)
            ->selectRaw('area_id, count(*) as total')
            ->groupBy('area_id')->with('area')->get();

        $lastImport = cache('employees_last_import');

        return view('admin.employees.index', compact(
            'allUsersGrouped', 'areas', 'totalActive', 'totalInactive', 'areaStats', 'lastImport'
        ));
    }

    /* ──────────────────────────────────
     *  IMPORT — fast bulk sync from Excel
     * ────────────────────────────────── */
    public function importEmployees(Request $request)
    {
        set_time_limit(300);

        $request->validate([
            'employees_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $empleadoRole   = Role::where('slug', 'empleado')->first();
        $directorRhRole = Role::where('slug', 'director_rh')->first();
        $jefeAreaRole   = Role::where('slug', 'jefe_area')->first();

        if (!$empleadoRole || !$directorRhRole || !$jefeAreaRole) {
            return response()->json(['error' => 'No se encontraron los roles requeridos (empleado / director_rh / jefe_area).'], 422);
        }

        try {
            $spreadsheet = IOFactory::load($request->file('employees_file')->getRealPath());
        } catch (Throwable) {
            return response()->json(['error' => 'No se pudo leer el archivo. Verifica que sea un Excel válido.'], 422);
        }

        $sheetRows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        unset($spreadsheet);

        if (count($sheetRows) < 2) {
            return response()->json(['error' => 'El archivo no contiene datos.'], 422);
        }

        $headerRow = array_shift($sheetRows);
        $col = $this->resolveColumns($headerRow);

        if (!isset($col['document_number'])) {
            return response()->json(['error' => 'El archivo debe incluir una columna de cédula/documento.'], 422);
        }

        // ═══ PHASE 1 — Parse rows into memory ═══
        $parsed = [];
        $skipped = 0;
        $uniqueAreaNames = [];
        $uniquePositionNames = [];

        foreach ($sheetRows as $row) {
            $document = preg_replace('/\D/', '', trim((string) ($row[$col['document_number']] ?? '')));
            if ($document === '') { $skipped++; continue; }

            $firstName = trim((string) ($row[$col['first_name'] ?? ''] ?? ''));
            $lastName  = trim((string) ($row[$col['last_name'] ?? ''] ?? ''));
            $fullName  = trim((string) ($row[$col['full_name'] ?? ''] ?? ''));

            if (($firstName === '' || $lastName === '') && $fullName !== '') {
                [$fn, $ln] = $this->splitFullName($fullName);
                $firstName = $firstName ?: $fn;
                $lastName  = $lastName ?: $ln;
            }

            $areaName = trim((string) ($row[$col['area'] ?? ''] ?? ''));
            $posName  = trim((string) ($row[$col['position'] ?? ''] ?? ''));
            $docType  = trim((string) ($row[$col['document_type'] ?? ''] ?? '')) ?: 'CC';
            $isActive = isset($col['is_active']) ? $this->toBoolean((string) ($row[$col['is_active']] ?? '1')) : true;

            if ($areaName !== '') $uniqueAreaNames[$this->normalize($areaName)] = $areaName;
            if ($posName !== '') $uniquePositionNames[] = ['area' => $areaName, 'name' => $posName];

            $isJefeFlag = $this->isJefe($posName);

            $parsed[] = compact('document', 'firstName', 'lastName', 'docType', 'areaName', 'posName', 'isActive', 'isJefeFlag');
        }

        // ═══ PHASE 2 — Ensure areas & positions exist ═══
        $areasByName = [];
        foreach (Area::all() as $a) { $areasByName[$this->normalize($a->name)] = $a; }

        $areasCreated = 0;
        foreach ($uniqueAreaNames as $nk => $originalName) {
            if (!isset($areasByName[$nk])) {
                $areasByName[$nk] = Area::create(['name' => $originalName, 'description' => 'Importación automática', 'is_active' => true]);
                $areasCreated++;
            } elseif (!$areasByName[$nk]->is_active) {
                $areasByName[$nk]->update(['is_active' => true]);
            }
        }

        $positionsByKey = [];
        foreach (PositionType::all() as $p) { $positionsByKey[$p->area_id . '|' . $this->normalize($p->name)] = $p; }

        $positionsCreated = 0;
        $seenPos = [];
        foreach ($uniquePositionNames as $pn) {
            $area = $pn['area'] !== '' ? ($areasByName[$this->normalize($pn['area'])] ?? null) : null;
            $pk = ($area?->id ?? 0) . '|' . $this->normalize($pn['name']);
            if (isset($seenPos[$pk])) continue;
            $seenPos[$pk] = true;
            if (!isset($positionsByKey[$pk])) {
                $positionsByKey[$pk] = PositionType::create(['name' => $pn['name'], 'description' => 'Importación automática', 'area_id' => $area?->id, 'is_active' => true]);
                $positionsCreated++;
            } elseif (!$positionsByKey[$pk]->is_active) {
                $positionsByKey[$pk]->update(['is_active' => true]);
            }
        }

        // ═══ PHASE 3 — Bulk upsert Persons ═══
        $now = now();
        $personBatch = [];
        foreach ($parsed as $p) {
            $personBatch[] = [
                'document_number' => $p['document'],
                'document_type'   => $p['docType'],
                'first_name'      => $p['firstName'] ?: 'Sin nombre',
                'last_name'       => $p['lastName'] ?: 'Sin apellido',
                'updated_at'      => $now,
                'created_at'      => $now,
            ];
        }

        foreach (array_chunk($personBatch, 500) as $chunk) {
            Person::upsert($chunk, ['document_number'], ['document_type', 'first_name', 'last_name', 'updated_at']);
        }

        $docs = array_column($personBatch, 'document_number');
        $personsByDoc = Person::whereIn('document_number', $docs)->get()->keyBy('document_number');

        // ═══ PHASE 4 — Bulk upsert Users ═══
        // Pre-hash ONE password for all new users (all get cédula as password)
        // We hash only ONCE and reuse for users that are new
        $existingUsersByLogin = User::whereIn('login', $docs)->get()->keyBy('login');

        $newUserBatch = [];
        $updateUserBatch = [];
        $superUserLogins = [];
        $jefeLogins = [];
        $passwordCache = []; // document => hashed (lazy, reuse when same doc)

        foreach ($parsed as $p) {
            $person = $personsByDoc[$p['document']] ?? null;
            if (!$person) continue;

            $area = $p['areaName'] !== '' ? ($areasByName[$this->normalize($p['areaName'])] ?? null) : null;
            $pk   = ($area?->id ?? 0) . '|' . $this->normalize($p['posName']);
            $pos  = $positionsByKey[$pk] ?? null;

            if ($this->isSuperUser($p['areaName'], $p['posName'])) {
                $superUserLogins[] = $p['document'];
            }

            if ($p['isJefeFlag']) {
                $jefeLogins[] = $p['document'];
            }

            $userData = [
                'login'            => $p['document'],
                'person_id'        => $person->id,
                'area_id'          => $area?->id,
                'position_type_id' => $pos?->id,
                'is_active'        => $p['isActive'],
                'updated_at'       => $now,
            ];

            // Always compute password hash (needed for upsert INSERT clause in PostgreSQL)
            if (!isset($passwordCache[$p['document']])) {
                $passwordCache[$p['document']] = password_hash($p['document'], PASSWORD_BCRYPT, ['cost' => 4]);
            }

            if ($existingUsersByLogin->has($p['document'])) {
                // Include password so PostgreSQL INSERT ... ON CONFLICT doesn't fail NOT NULL,
                // but password is NOT in upsert's update list, so existing passwords stay intact.
                $userData['password'] = $existingUsersByLogin[$p['document']]->password;
                $updateUserBatch[] = $userData;
            } else {
                $userData['password']             = $passwordCache[$p['document']];
                $userData['must_change_password']  = true;
                $userData['created_at']            = $now;
                $newUserBatch[] = $userData;
            }
        }

        DB::transaction(function () use ($newUserBatch, $updateUserBatch) {
            foreach (array_chunk($newUserBatch, 500) as $chunk) {
                DB::table('users')->insert($chunk);
            }
            foreach (array_chunk($updateUserBatch, 500) as $chunk) {
                DB::table('users')->upsert($chunk, ['login'], ['person_id', 'area_id', 'position_type_id', 'is_active', 'updated_at']);
            }
        });

        // ═══ PHASE 5 — Bulk role assignment ═══
        $processedUsers = User::whereIn('login', $docs)->get();
        $processedIds   = $processedUsers->pluck('id')->toArray();
        $superUserIds   = $processedUsers->whereIn('login', $superUserLogins)->pluck('id')->toArray();
        $jefeUserIds    = $processedUsers->whereIn('login', $jefeLogins)->pluck('id')->toArray();

        DB::transaction(function () use ($processedIds, $superUserIds, $jefeUserIds, $empleadoRole, $directorRhRole, $jefeAreaRole) {
            DB::table('user_has_roles')->whereIn('user_id', $processedIds)->delete();

            $roleRows = [];
            foreach ($processedIds as $uid) {
                $roleRows[] = ['user_id' => $uid, 'role_id' => $empleadoRole->id];
                if (in_array($uid, $superUserIds)) {
                    $roleRows[] = ['user_id' => $uid, 'role_id' => $directorRhRole->id];
                }
                if (in_array($uid, $jefeUserIds)) {
                    $roleRows[] = ['user_id' => $uid, 'role_id' => $jefeAreaRole->id];
                }
            }
            foreach (array_chunk($roleRows, 1000) as $chunk) {
                DB::table('user_has_roles')->insert($chunk);
            }
        });

        // ═══ PHASE 6 — Deactivate employees NOT in file ═══
        $deactivated = 0;
        if (!empty($processedIds)) {
            $deactivated = User::where('is_active', true)
                ->whereNotIn('id', $processedIds)
                ->whereHas('roles', fn ($q) => $q->whereIn('slug', ['empleado']))
                ->update(['is_active' => false]);
        }

        cache(['employees_last_import' => now()->format('d/m/Y H:i')], now()->addYear());

        $stats = [
            'created'       => count($newUserBatch),
            'updated'       => count($updateUserBatch),
            'areas_new'     => $areasCreated,
            'positions_new' => $positionsCreated,
            'skipped'       => $skipped,
            'deactivated'   => $deactivated,
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /* ──────────────────────────────────
     *  API — position types by area
     * ────────────────────────────────── */
    public function getPositionTypes(Area $area)
    {
        return response()->json(
            $area->positionTypes()->where('is_active', true)->get(['id', 'name'])
        );
    }

    /* ──────────────────────────────────
     *  Helpers
     * ────────────────────────────────── */
    private function resolveColumns(array $headerRow): array
    {
        $headerMap = [];
        foreach ($headerRow as $c => $label) {
            $headerMap[$this->normalize((string) $label)] = $c;
        }

        $aliases = [
            'document_number' => ['cedula', 'cédula', 'documento', 'numero documento', 'nro documento', 'identificacion', 'identificación', 'usuario', 'login'],
            'document_type'   => ['tipo documento', 'tipo_doc', 'tipo', 'doc', 'tipo doc'],
            'first_name'      => ['nombres', 'nombre', 'primer nombre'],
            'last_name'       => ['apellidos', 'apellido', 'primer apellido'],
            'full_name'       => ['nombre completo', 'empleado', 'colaborador', 'apellido y nombres', 'apellidos y nombres', 'nombres y apellidos', 'apellido y nombre'],
            'area'            => ['area', 'área', 'nombre area', 'nombre área'],
            'position'        => ['cargo', 'puesto', 'posicion', 'posición'],
            'is_active'       => ['activo', 'estado', 'status', 'habilitado'],
        ];

        $col = [];
        foreach ($aliases as $key => $candidates) {
            foreach ($candidates as $c) {
                if (isset($headerMap[$this->normalize($c)])) {
                    $col[$key] = $headerMap[$this->normalize($c)];
                    break;
                }
            }
        }
        return $col;
    }

    private function normalize(?string $value): string
    {
        return Str::of((string) $value)->ascii()->lower()->replaceMatches('/[^a-z0-9\s]/', ' ')->squish()->value();
    }

    private function splitFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        if (count($parts) <= 1) { return [$parts[0] ?? 'Sin nombre', 'Sin apellido']; }
        $mid = (int) ceil(count($parts) / 2);
        return [implode(' ', array_slice($parts, $mid)), implode(' ', array_slice($parts, 0, $mid))];
    }

    private function toBoolean(string $value): bool
    {
        $n = $this->normalize($value);
        return $n !== '' && !in_array($n, ['0', 'no', 'inactivo', 'inactive', 'false'], true);
    }

    private function isSuperUser(string $areaName, string $positionName): bool
    {
        $a = $this->normalize($areaName);
        $p = $this->normalize($positionName);

        if (str_contains($a, 'recursos humanos') && str_contains($p, 'jefe') && str_contains($p, 'recursos humanos')) {
            return true;
        }
        if (str_contains($a, 'gerencia') && str_contains($p, 'gerente')) {
            return true;
        }
        return false;
    }

    /**
     * Determine if a position implies jefe/coordinador role.
     */
    private function isJefe(string $positionName): bool
    {
        $p = $this->normalize($positionName);

        return str_contains($p, 'jefe')
            || str_contains($p, 'coordinador')
            || str_contains($p, 'coordinadora')
            || str_contains($p, 'director')
            || str_contains($p, 'directora')
            || str_contains($p, 'supervisor')
            || str_contains($p, 'supervisora')
            || str_contains($p, 'lider')
            || str_contains($p, 'gerente');
    }
}
