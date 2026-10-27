<?php

use App\Actions\Modifications\CreateModificationAction;
use App\Actions\Modifications\TransitionModificationAction;
use App\Enums\ModificationStatus;
use App\Enums\ModificationType;
use App\Models\OrganizationalUnit;
use App\Models\PlanningPeriod;
use App\Models\PoaActivity;
use App\Models\PoaModificationRequest;
use App\Models\PoaProcess;
use App\Models\User;

test('creates balanced modification without budget impact', function () {
    $user = User::factory()->create();
    $unit = OrganizationalUnit::factory()->create();
    $period = PlanningPeriod::factory()->create();
    $process = PoaProcess::factory()->create(['planning_period_id' => $period, 'responsible_user_id' => $user, 'created_by' => $user]);
    $source = PoaActivity::factory()->create(['poa_process_id' => $process, 'organizational_unit_id' => $unit, 'responsible_user_id' => $user, 'created_by' => $user]);
    $destination = PoaActivity::factory()->create(['poa_process_id' => $process, 'organizational_unit_id' => $unit, 'responsible_user_id' => $user, 'created_by' => $user]);
    $request = app(CreateModificationAction::class)->handle(['number' => 'MOD-001', 'type' => ModificationType::WithoutBudgetImpact->value, 'poa_process_id' => $process->id, 'organizational_unit_id' => $unit->id, 'justification' => 'Reasignación', 'lines' => [['source_activity_id' => $source->id, 'destination_activity_id' => $destination->id, 'debit_amount' => 50, 'credit_amount' => 50, 'description' => 'Transferencia']]], $user);
    expect($request->lines)->toHaveCount(1)->and($request->requires_esigef)->toBeFalse();
});

test('rejects unbalanced internal modification', function () {
    $user = User::factory()->create();
    $unit = OrganizationalUnit::factory()->create();
    $period = PlanningPeriod::factory()->create();
    $process = PoaProcess::factory()->create(['planning_period_id' => $period, 'responsible_user_id' => $user, 'created_by' => $user]);
    expect(fn () => app(CreateModificationAction::class)->handle(['number' => 'MOD-002', 'type' => ModificationType::WithoutBudgetImpact->value, 'poa_process_id' => $process->id, 'organizational_unit_id' => $unit->id, 'justification' => 'Inválida', 'lines' => [['debit_amount' => 50, 'credit_amount' => 40, 'description' => 'Desbalance']]], $user))->toThrow(DomainException::class);
});

test('budget impact approval requires manually recorded reform and cur', function () {
    $user = User::factory()->create();
    $unit = OrganizationalUnit::factory()->create();
    $period = PlanningPeriod::factory()->create();
    $process = PoaProcess::factory()->create(['planning_period_id' => $period, 'responsible_user_id' => $user, 'created_by' => $user]);
    $request = PoaModificationRequest::query()->create(['number' => 'MOD-003', 'type' => ModificationType::WithBudgetImpact, 'status' => ModificationStatus::Authorized, 'poa_process_id' => $process->id, 'organizational_unit_id' => $unit->id, 'justification' => 'Reforma', 'requires_esigef' => true, 'requested_by' => $user->id]);
    expect(fn () => app(TransitionModificationAction::class)->handle($request, ModificationStatus::Approved, $user, 'Aprobar', 1))->toThrow(DomainException::class);
});
