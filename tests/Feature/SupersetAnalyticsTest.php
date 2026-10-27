<?php

use App\Models\OrganizationalUnit;
use App\Models\Permission;
use App\Models\PlanningPeriod;
use App\Models\PoaActivity;
use App\Models\PoaProcess;
use App\Models\Role;
use App\Models\User;

function biViewerUser(): User
{
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $permission = Permission::query()->firstOrCreate(['code' => 'reports.view'], ['name' => 'Ver Reportes', 'module' => 'reports']);
    $role->permissions()->syncWithoutDetaching($permission);
    $user->roles()->attach($role);

    return $user;
}

test('analytics endpoint returns formatted dataset for superset BI compatibility', function () {
    $user = biViewerUser();
    $unit = OrganizationalUnit::factory()->create(['code' => 'DIR-TIC', 'name' => 'Dirección de TICs']);
    $period = PlanningPeriod::factory()->create(['year' => 2026]);
    $process = PoaProcess::factory()->create(['planning_period_id' => $period->id]);

    PoaActivity::factory()->create([
        'poa_process_id' => $process->id,
        'organizational_unit_id' => $unit->id,
        'number' => 'ACT-BI-01',
        'description' => 'Servicios de conectividad',
        'annual_amount' => 15000.00,
    ]);

    $response = $this->actingAs($user)->getJson(route('analytics.dataset', ['year' => 2026]));
    $response->assertOk()
        ->assertJsonStructure([
            'meta' => ['view', 'year', 'total_records', 'generated_at'],
            'schema',
            'data' => [
                '*' => [
                    'ejercicio',
                    'unidad_codigo',
                    'unidad_nombre',
                    'actividad_numero',
                    'actividad_descripcion',
                    'monto_planificado',
                    'monto_devengado',
                    'saldo_disponible',
                    'porcentaje_ejecucion',
                    'estado',
                ],
            ],
        ])
        ->assertJsonPath('meta.view', 'V_PRO_SIGPOA_EJE')
        ->assertJsonPath('data.0.actividad_numero', 'ACT-BI-01')
        ->assertJsonPath('data.0.unidad_codigo', 'DIR-TIC');
});
