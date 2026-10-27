<?php

namespace Database\Seeders;

use App\Models\OrganizationalUnit;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        $mainUnit = OrganizationalUnit::query()->firstOrCreate(
            ['code' => 'DPGE'],
            [
                'name' => 'Dirección de Planificación y Gestión Estratégica',
                'type' => 'DIRECCION',
                'is_active' => true,
            ]
        );

        $adminRole = Role::query()->where('code', 'administrador')->first();
        $directorRole = Role::query()->where('code', 'director_dpge')->first();
        $analistaRole = Role::query()->where('code', 'analista_dpge')->first();
        $financieroRole = Role::query()->where('code', 'financiero')->first();
        $areaRole = Role::query()->where('code', 'direccion_area')->first();
        $requirenteRole = Role::query()->where('code', 'gestion_interna')->first();

        // 1. Administrador institucional
        $admin = User::query()->updateOrCreate(
            ['email' => 'administrador@institucion.gob.ec'],
            [
                'name' => 'Administrador del Sistema',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        }
        $admin->organizationalUnits()->syncWithoutDetaching([$mainUnit->id => ['is_primary' => true]]);

        // 2. Administrador DGAC alias
        $adminDgac = User::query()->updateOrCreate(
            ['email' => 'admin@dgac.gob.ec'],
            [
                'name' => 'Administrador DGAC',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        if ($adminRole) {
            $adminDgac->roles()->syncWithoutDetaching([$adminRole->id]);
        }
        $adminDgac->organizationalUnits()->syncWithoutDetaching([$mainUnit->id => ['is_primary' => true]]);

        // 3. Director DPGE
        $director = User::query()->updateOrCreate(
            ['email' => 'director.dpge@institucion.gob.ec'],
            [
                'name' => 'Director de Planificación',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        if ($directorRole) {
            $director->roles()->syncWithoutDetaching([$directorRole->id]);
        }
        $director->organizationalUnits()->syncWithoutDetaching([$mainUnit->id => ['is_primary' => true]]);

        // 4. Analista DPGE
        $analista = User::query()->updateOrCreate(
            ['email' => 'analista.dpge@institucion.gob.ec'],
            [
                'name' => 'Analista de Planificación',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        if ($analistaRole) {
            $analista->roles()->syncWithoutDetaching([$analistaRole->id]);
        }
        $analista->organizationalUnits()->syncWithoutDetaching([$mainUnit->id => ['is_primary' => true]]);

        // 5. Financiero / Presupuesto
        $financiero = User::query()->updateOrCreate(
            ['email' => 'financiero@institucion.gob.ec'],
            [
                'name' => 'Especialista Financiero',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        if ($financieroRole) {
            $financiero->roles()->syncWithoutDetaching([$financieroRole->id]);
        }
        $financiero->organizationalUnits()->syncWithoutDetaching([$mainUnit->id => ['is_primary' => true]]);

        // 6. Director de Área
        $dirArea = User::query()->updateOrCreate(
            ['email' => 'direccion.area@institucion.gob.ec'],
            [
                'name' => 'Director de Área Operativa',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        if ($areaRole) {
            $dirArea->roles()->syncWithoutDetaching([$areaRole->id]);
        }
        $dirArea->organizationalUnits()->syncWithoutDetaching([$mainUnit->id => ['is_primary' => true]]);

        // 7. Gestión Interna / Requirente
        $requirente = User::query()->updateOrCreate(
            ['email' => 'requirente@institucion.gob.ec'],
            [
                'name' => 'Funcionario Requirente',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        if ($requirenteRole) {
            $requirente->roles()->syncWithoutDetaching([$requirenteRole->id]);
        }
        $requirente->organizationalUnits()->syncWithoutDetaching([$mainUnit->id => ['is_primary' => true]]);
    }
}
