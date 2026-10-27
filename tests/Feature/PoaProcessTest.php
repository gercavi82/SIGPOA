<?php

use App\Enums\PoaPhase;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\PlanningPeriod;
use App\Models\PoaProcess;
use App\Models\Role;
use App\Models\User;

function phaseTwoAdministrator(): User
{
    $user = User::factory()->create();
    $permission = Permission::factory()->create(['code' => 'admin.parameters']);
    $role = Role::factory()->create();
    $role->permissions()->attach($permission);
    $user->roles()->attach($role);

    return $user;
}

test('authorized user creates a poa process with audit', function () {
    $actor = phaseTwoAdministrator();
    $responsible = User::factory()->create();
    $period = PlanningPeriod::query()->create(['year' => 2027, 'starts_at' => '2027-01-01', 'ends_at' => '2027-12-31']);

    $this->actingAs($actor)->post(route('poa-processes.store'), ['planning_period_id' => $period->id, 'phase' => PoaPhase::PoaNecesidades->value, 'responsible_user_id' => $responsible->id])
        ->assertRedirect(route('poa-processes.index'));

    expect(PoaProcess::query()->sole()->created_by)->toBe($actor->id)
        ->and(AuditLog::query()->where('event', 'created')->exists())->toBeTrue();
});

test('invalid and concurrent transitions are rejected', function () {
    $actor = phaseTwoAdministrator();
    $period = PlanningPeriod::query()->create(['year' => 2028, 'starts_at' => '2028-01-01', 'ends_at' => '2028-12-31']);
    $process = PoaProcess::factory()->create(['planning_period_id' => $period, 'responsible_user_id' => $actor, 'created_by' => $actor, 'phase' => PoaPhase::PoaNecesidades]);

    $this->actingAs($actor)->post(route('poa-processes.transition', $process), ['status' => 'CERRADO', 'comment' => 'Intento inválido', 'version' => 1])->assertSessionHasErrors('status');
    $this->post(route('poa-processes.transition', $process), ['status' => 'ABIERTO', 'comment' => 'Abrir proceso', 'version' => 99])->assertSessionHasErrors('status');
});
