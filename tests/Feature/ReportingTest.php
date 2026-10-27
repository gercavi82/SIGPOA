<?php

use App\Models\BudgetAccrual;
use App\Models\OrganizationalUnit;
use App\Models\Permission;
use App\Models\PlanningPeriod;
use App\Models\PoaActivity;
use App\Models\PoaProcess;
use App\Models\Role;
use App\Models\User;

function reportingViewerUser(): User
{
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $permission = Permission::query()->firstOrCreate(['code' => 'reports.view'], ['name' => 'Ver Reportes', 'module' => 'reports']);
    $role->permissions()->syncWithoutDetaching($permission);
    $user->roles()->attach($role);

    return $user;
}

test('renders reports center and specialized views for authorized user', function () {
    $user = reportingViewerUser();
    $unit = OrganizationalUnit::factory()->create(['name' => 'Dirección de Navegación Aérea']);
    $period = PlanningPeriod::factory()->create(['year' => 2026]);
    $process = PoaProcess::factory()->create(['planning_period_id' => $period->id]);

    $activity = PoaActivity::factory()->create([
        'poa_process_id' => $process->id,
        'organizational_unit_id' => $unit->id,
        'number' => 'ACT-REP-01',
        'description' => 'Mantenimiento de radares',
        'annual_amount' => 8000.00,
    ]);

    BudgetAccrual::factory()->create([
        'poa_activity_id' => $activity->id,
        'year' => 2026,
        'month' => 8,
        'accrued_amount' => 3000.00,
    ]);

    // Portal de Reportes
    $this->actingAs($user)->get(route('reports.index'))->assertOk();

    // Reporte 1: POA por Dirección
    $this->actingAs($user)->get(route('reports.poa-by-unit', ['year' => 2026]))
        ->assertOk()
        ->assertSee('Mantenimiento de radares')
        ->assertSee('Dirección de Navegación Aérea');

    // Reporte 2: Ejecución Comparada
    $this->actingAs($user)->get(route('reports.execution-comparison', ['year' => 2026]))
        ->assertOk()
        ->assertSee('$8,000.00')
        ->assertSee('$3,000.00')
        ->assertSee('$5,000.00')
        ->assertSee('37.5%');

    // Reporte 3: Certificaciones
    $this->actingAs($user)->get(route('reports.certifications', ['year' => 2026]))->assertOk();

    // Reporte 4: Modificaciones
    $this->actingAs($user)->get(route('reports.modifications', ['year' => 2026]))->assertOk();
});

test('exports reports to csv stream successfully', function () {
    $user = reportingViewerUser();
    $unit = OrganizationalUnit::factory()->create();
    $period = PlanningPeriod::factory()->create(['year' => 2026]);
    $process = PoaProcess::factory()->create(['planning_period_id' => $period->id]);

    PoaActivity::factory()->create([
        'poa_process_id' => $process->id,
        'organizational_unit_id' => $unit->id,
        'number' => 'ACT-CSV-01',
        'description' => 'Actividad exportable',
        'annual_amount' => 12000.00,
    ]);

    $response = $this->actingAs($user)->get(route('reports.poa-by-unit', ['year' => 2026, 'export' => 'csv']));
    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

test('user without reports.view permission is forbidden', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('reports.index'))->assertForbidden();
    $this->actingAs($user)->get(route('reports.poa-by-unit'))->assertForbidden();
    $this->actingAs($user)->get(route('reports.execution-comparison'))->assertForbidden();
});
