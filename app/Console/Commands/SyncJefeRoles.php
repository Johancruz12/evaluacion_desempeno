<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class SyncJefeRoles extends Command
{
    protected $signature = 'roles:sync-jefes';
    protected $description = 'Assign jefe_area role to users whose position implies jefe/coordinador/supervisor/director/líder';

    public function handle(): int
    {
        $jefeRole = Role::where('slug', 'jefe_area')->first();
        if (!$jefeRole) {
            $this->error('Role jefe_area not found.');
            return 1;
        }

        $users = User::where('is_active', true)
            ->whereHas('positionType', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereRaw('LOWER(name) LIKE ?', ['%jefe%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%coordinador%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%director%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%supervisor%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%lider%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%líder%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%gerente%']);
                });
            })
            ->whereDoesntHave('roles', fn ($q) => $q->where('slug', 'jefe_area'))
            ->get();

        $count = 0;
        foreach ($users as $user) {
            $user->roles()->attach($jefeRole->id);
            $count++;
        }

        $this->info("Assigned jefe_area role to {$count} user(s).");
        return 0;
    }
}
