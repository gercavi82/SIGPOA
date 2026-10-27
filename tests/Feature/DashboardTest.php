<?php

use App\Models\BudgetAccrual;
use App\Models\OrganizationalUnit;
use App\Models\Permission;
use App\Models\PlanningPeriod;
use App\Models\PoaActivity;
use App\Models\PoaActivityMonth;
use App\Models\PoaProcess;
use App\Models\Role;
use App\Models\User;

function dashboardUser(): User
{
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $permission = Permission::query()->firstOrCreate(['code' => 'reports.view'], ['name' => 'Ver Reportes', 'module' => 'reports']);
    $role->permissions()->syncWithoutDetaching($permission);
    $user->roles()->attach($role);

    return $user;
}

test('dashboard computes and renders accurate execution kpis and monthly progress', function () {
    $user = dashboardUser();
    $unit = OrganizationalUnit::factory()->create(['name' => 'Dirección de Inspección']);
    $period = PlanningPeriod::factory()->create(['year' => 2026]);
    $process = PoaProcess::factory()->create(['planning_period_id' => $period->id]);

    $activity = PoaActivity::factory()->create([
        'poa_process_id' => $process->id,
        'organizational_unit_id' => $unit->id,
        'annual_amount' => 10000.00,
        'annual_target' => 50,
    ]);

    PoaActivityMonth::query()->create([
        'poa_activity_id' => $activity->id,
        'month' => 8,
        'amount' => 5000.00,
        'target' => 25,
    ]);

    BudgetAccrual::factory()->create([
        'poa_activity_id' => $activity->id,
        'year' => 2026,
        'month' => 8,
        'accrued_amount' => 4000.00,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard', ['year' => 2026]));
    $response->assertOk()
        ->assertSee('Tablero de Control')
        ->assertSee('$10,000.00') // Total POA
        ->assertSee('$4,000.00')  // Devengado
        ->assertSee('$6,000.00')  // Saldo
        ->assertSee('40.0%')      // Cumplimiento
        ->assertSee('Dirección de Inspección');
});
