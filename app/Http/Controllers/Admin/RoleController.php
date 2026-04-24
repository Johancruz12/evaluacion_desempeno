<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /* ────────────── LISTADO ────────────── */
    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])
            ->orderBy('description')
            ->get();

        $totalPermissions = Permission::count();

        return view('admin.roles.index', compact('roles', 'totalPermissions'));
    }

    /* ────────────── FORM CREAR ────────────── */
    public function create()
    {
        $permissions = Permission::orderBy('description')->get();
        return view('admin.roles.create', compact('permissions'));
    }

    /* ────────────── GUARDAR ────────────── */
    public function store(Request $request)
    {
        $data = $request->validate([
            'description'  => ['required', 'string', 'max:100', 'unique:roles,description'],
            'slug'         => ['nullable', 'string', 'max:80', 'alpha_dash', 'unique:roles,slug'],
            'permissions'  => ['array'],
            'permissions.*'=> ['integer', 'exists:permissions,id'],
        ]);

        $slug = $data['slug'] ?? Str::slug($data['description'], '_');
        // Asegurar unicidad si se autogeneró
        $base = $slug; $i = 1;
        while (Role::where('slug', $slug)->exists()) {
            $slug = $base . '_' . (++$i);
        }

        $role = Role::create([
            'description' => $data['description'],
            'slug'        => $slug,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol «{$role->description}» creado correctamente.");
    }

    /* ────────────── FORM EDITAR ────────────── */
    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('description')->get();
        $assignedPermissionIds = $role->permissions()->pluck('permissions.id')->toArray();

        // Usuarios ya asignados y disponibles para asignar
        $assignedUsers = $role->users()
            ->with('person', 'area')
            ->orderBy('login')
            ->get();

        $assignedUserIds = $assignedUsers->pluck('id')->toArray();

        $availableUsers = User::with('person', 'area')
            ->where('is_active', true)
            ->when(! empty($assignedUserIds), fn ($q) => $q->whereNotIn('id', $assignedUserIds))
            ->orderBy('login')
            ->limit(500)
            ->get();

        return view('admin.roles.edit', compact(
            'role', 'permissions', 'assignedPermissionIds', 'assignedUsers', 'availableUsers'
        ));
    }

    /* ────────────── ACTUALIZAR ────────────── */
    public function update(Request $request, Role $role)
    {
        $isSystem = $this->isSystemRole($role);

        $data = $request->validate([
            'description'  => ['required', 'string', 'max:100', Rule::unique('roles', 'description')->ignore($role->id)],
            // El slug de roles de sistema NO puede cambiarse (se usa en código)
            'slug'         => [$isSystem ? 'prohibited' : 'nullable', 'string', 'max:80', 'alpha_dash', Rule::unique('roles', 'slug')->ignore($role->id)],
            'permissions'  => ['array'],
            'permissions.*'=> ['integer', 'exists:permissions,id'],
        ], [
            'slug.prohibited' => 'Este rol es del sistema y su identificador no puede cambiarse.',
        ]);

        $role->update([
            'description' => $data['description'],
            'slug'        => $isSystem ? $role->slug : ($data['slug'] ?? $role->slug),
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('admin.roles.edit', $role)
            ->with('success', "Rol «{$role->description}» actualizado.");
    }

    /* ────────────── ELIMINAR ────────────── */
    public function destroy(Role $role)
    {
        if ($this->isSystemRole($role)) {
            return back()->with('error', 'No se puede eliminar un rol del sistema.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay usuarios asignados a este rol.');
        }

        $name = $role->description;
        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol «{$name}» eliminado.");
    }

    /* ────────────── ASIGNAR USUARIO A ROL ────────────── */
    public function attachUser(Request $request, Role $role)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $role->users()->syncWithoutDetaching([$data['user_id']]);

        return back()->with('success', 'Usuario asignado al rol correctamente.');
    }

    /* ────────────── QUITAR USUARIO DE ROL ────────────── */
    public function detachUser(Request $request, Role $role, User $user)
    {
        // Protección: el Super Admin no puede quitarse a sí mismo el rol director_rh
        if ($user->id === $request->user()->id && $role->slug === 'director_rh' && $user->isSuperAdmin()) {
            return back()->with('error', 'No puedes quitarte el rol de Administrador a ti mismo.');
        }

        $role->users()->detach($user->id);

        return back()->with('success', 'Usuario removido del rol.');
    }

    /* ────────────── HELPERS ────────────── */
    private function isSystemRole(Role $role): bool
    {
        return in_array($role->slug, ['director_rh', 'jefe_area', 'empleado'], true);
    }
}
