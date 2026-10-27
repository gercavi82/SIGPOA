<?php

use App\Enums\ActivityStatus;
use App\Enums\PoaPhase;
use App\Enums\ProcessStatus;
use App\Models\EntityVersion;
use App\Models\OrganizationalUnit;
use App\Models\Permission;
use App\Models\PlanningPeriod;
use App\Models\PoaActivity;
use App\Models\PoaProcess;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowTransition;

function planningUser(): User
{
    $user = User::factory()->create();
    $role = Role::factory()->create();
    foreach (['poa.view', 'poa.create', 'poa.review'] as $code) {
        $role->permissions()->attach(Permission::factory()->create(['code' => $code]));
    }
    $user->roles()->attach($role);

    return $user;
}

function openNeedsProcess(User $user): PoaProcess
{
    $period = PlanningPeriod::query()->create(['year' => 2031, 'starts_at' => '2031-01-01', 'ends_at' => '2031-12-31']);

    return PoaProcess::factory()->create(['planning_period_id' => $period, 'phase' => PoaPhase::PoaNecesidades, 'status' => ProcessStatus::Open, 'responsible_user_id' => $user, 'created_by' => $user]);
}

test('creates activity with variable monthly schedule and immutable version', function () {
    $user = planningUser();
    $process = openNeedsProcess($user);
    $unit = OrganizationalUnit::factory()->create();
    $payload = ['poa_process_id' => $process->id, 'organizational_unit_id' => $unit->id, 'number' => 'ACT-001', 'description' => 'Actividad', 'objective' => 'Objetivo', 'annual_target' => 2, 'annual_amount' => 100, 'responsible_user_id' => $user->id, 'months' => [['month' => 1, 'target' => 1, 'amount' => 40], ['month' => 2, 'target' => 1, 'amount' => 60]]];
    $this->actingAs($user)->post(route('poa-activities.store'), $payload)->assertRedirect();
    expect(PoaActivity::query()->sole()->months)->toHaveCount(2)->and(EntityVersion::query()->count())->toBe(1);
});

test('rejects monthly amount mismatch', function () {
    $user = planningUser();
    $process = openNeedsProcess($user);
    $unit = OrganizationalUnit::factory()->create();
    $this->actingAs($user)->post(route('poa-activities.store'), ['poa_process_id' => $process->id, 'organizational_unit_id' => $unit->id, 'number' => 'ACT-002', 'description' => 'Actividad', 'objective' => 'Objetivo', 'annual_target' => 1, 'annual_amount' => 100, 'responsible_user_id' => $user->id, 'months' => [['month' => 1, 'target' => 1, 'amount' => 90]]])->assertSessionHasErrors('activity');
});

test('records workflow transitions and rejects invalid transition', function () {
    $user = planningUser();
    $activity = PoaActivity::factory()->create(['poa_process_id' => openNeedsProcess($user), 'responsible_user_id' => $user, 'created_by' => $user]);
    $this->actingAs($user)->post(route('poa-activities.transition', $activity), ['status' => ActivityStatus::Submitted->value, 'comment' => 'Enviar', 'version' => 1])->assertSessionHasNoErrors();
    expect(WorkflowTransition::query()->count())->toBe(1);
    $this->post(route('poa-activities.transition', $activity), ['status' => ActivityStatus::Approved->value, 'comment' => 'Saltar', 'version' => 2])->assertSessionHasErrors('status');
});
