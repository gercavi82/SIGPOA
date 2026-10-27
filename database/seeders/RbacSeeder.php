<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = collect([
            'poa' => ['view', 'create', 'edit', 'submit', 'review', 'approve'],
            'certification' => ['create', 'submit', 'review', 'approve', 'reject'],
            'modification' => ['create', 'verify_budget', 'review', 'approve', 'reject'],
            'execution' => ['import', 'validate', 'process', 'close'],
            'reports' => ['view'],
            'admin' => ['users', 'roles', 'permissions', 'parameters'],
        ])->flatMap(fn (array $actions, string $module) => collect($actions)->map(fn (string $action) => [
            'code' => "$module.$action",
            'name' => str($module.' '.$action)->replace('_', ' ')->headline()->toString(),
            'module' => $module,
        ]));

        $permissions->each(fn (array $permission) => Permission::query()->updateOrCreate(['code' => $permission['code']], $permission));

        $roles = [
            'gestion_interna' => 'Gestión Interna requirente',
            'direccion_area' => 'Dirección de Área',
            'analista_dpge' => 'Analista DPGE',
            'director_dpge' => 'Director DPGE',
            'financiero' => 'Financiero / Presupuesto',
            'administrador' => 'Administrador',
        ];

        foreach ($roles as $code => $name) {
            Role::query()->updateOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }

        Role::query()->where('code', 'administrador')->firstOrFail()->permissions()->sync(Permission::query()->pluck('id'));
    }
}
