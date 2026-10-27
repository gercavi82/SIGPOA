<?php

use App\Enums\ExecutionClosingStatus;
use App\Models\BudgetAccrual;
use App\Models\ExecutionClosing;
use App\Models\Permission;
use App\Models\PlanningPeriod;
use App\Models\PoaActivity;
use App\Models\PoaActivityMonth;
use App\Models\PoaProcess;
use App\Models\Role;
use App\Models\User;

function executionClosingUser(array $permissions = ['execution.close', 'execution.import']): User
{
    $user = User::factory()->create();
    $role = Role::factory()->create();
    foreach ($permissions as $code) {
        $permission = Permission::query()->firstOrCreate(['code' => $code], ['name' => $code, 'module' => 'execution']);
        $role->permissions()->syncWithoutDetaching($permission);
    }
    $user->roles()->attach($role);

    return $user;
}

test('authorized user performs monthly execution closing calculating planned vs accrued', function () {
    $user = executionClosingUser();

    $period = PlanningPeriod::factory()->create(['year' => 2026]);
    $process = PoaProcess::factory()->create(['planning_period_id' => $period->id]);
    $activity = PoaActivity::factory()->create(['poa_process_id' => $process->id]);

    PoaActivityMonth::query()->create([
        'poa_activity_id' => $activity->id,
        'month' => 9,
        'amount' => 5000.00,
        'target' => 10,
    ]);

    BudgetAccrual::factory()->create([
        'poa_activity_id' => $activity->id,
        'year' => 2026,
        'month' => 9,
        'accrued_amount' => 4200.00,
    ]);

    $response = $this->actingAs($user)->post(route('execution-closings.store'), [
        'year' => 2026,
        'month' => 9,
        'notes' => 'Cierre formal septiembre 2026',
    ]);

    $response->assertRedirect();

    $closing = ExecutionClosing::query()->where('year', 2026)->where('month', 9)->firstOrFail();
    expect($closing->status)->toBe(ExecutionClosingStatus::CLOSED)
        ->and($closing->total_planned)->toEqual('5000.00')
        ->and($closing->total_accrued)->toEqual('4200.00');

    $this->get(route('execution-closings.index'))->assertOk();
});

test('rejects duplicate monthly closure for the same period', function () {
    $user = executionClosingUser();

    ExecutionClosing::factory()->create([
        'year' => 2026,
        'month' => 10,
        'status' => ExecutionClosingStatus::CLOSED,
    ]);

    $response = $this->actingAs($user)->post(route('execution-closings.store'), [
        'year' => 2026,
        'month' => 10,
    ]);

    $response->assertSessionHasErrors('closing');
});

test('user without execution.close permission is forbidden', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('execution-closings.store'), [
        'year' => 2026,
        'month' => 11,
    ]);

    $response->assertForbidden();
});
