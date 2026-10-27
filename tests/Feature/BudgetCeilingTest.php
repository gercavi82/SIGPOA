<?php

use App\Enums\BudgetCeilingStatus;
use App\Models\BudgetCeiling;
use App\Models\OrganizationalUnit;
use App\Models\Permission;
use App\Models\PlanningPeriod;
use App\Models\Role;
use App\Models\User;

function ceilingApprover(): User
{
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $permission = Permission::factory()->create(['code' => 'poa.approve']);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role);

    return $user;
}

test('creates and approves a fully distributed institutional ceiling', function () {
    $user = ceilingApprover();
    $period = PlanningPeriod::factory()->create();
    $unit = OrganizationalUnit::factory()->create();
    $payload = ['planning_period_id' => $period->id, 'source' => 'MEF', 'current_expenditure' => 60, 'investment_expenditure' => 40, 'methodology' => 'Distribución validada', 'units' => [['organizational_unit_id' => $unit->id, 'amount' => 100]]];
    $this->actingAs($user)->post(route('budget-ceilings.store'), $payload)->assertRedirect();
    $ceiling = BudgetCeiling::query()->sole();
    $this->post(route('budget-ceilings.transition', $ceiling), ['status' => BudgetCeilingStatus::UnderAnalysis->value, 'comment' => 'Analizar', 'version' => 1])->assertSessionHasNoErrors();
    $this->post(route('budget-ceilings.transition', $ceiling), ['status' => BudgetCeilingStatus::Approved->value, 'comment' => 'Aprobar', 'version' => 2])->assertSessionHasNoErrors();
    expect($ceiling->refresh()->status)->toBe(BudgetCeilingStatus::Approved);
});

test('rejects distributions over ceiling and incomplete approval', function () {
    $user = ceilingApprover();
    $period = PlanningPeriod::factory()->create();
    $unit = OrganizationalUnit::factory()->create();
    $this->actingAs($user)->post(route('budget-ceilings.store'), ['planning_period_id' => $period->id, 'source' => 'MEF', 'current_expenditure' => 60, 'investment_expenditure' => 40, 'methodology' => 'Método', 'units' => [['organizational_unit_id' => $unit->id, 'amount' => 101]]])->assertSessionHasErrors('ceiling');
});
