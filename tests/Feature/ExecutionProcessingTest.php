<?php

use App\Enums\ExecutionClosingStatus;
use App\Enums\ImportBatchRowStatus;
use App\Enums\ImportBatchStatus;
use App\Models\BudgetAccrual;
use App\Models\ExecutionClosing;
use App\Models\ImportBatch;
use App\Models\ImportBatchRow;
use App\Models\Permission;
use App\Models\PoaActivity;
use App\Models\Role;
use App\Models\User;

function executionProcessorUser(): User
{
    $user = User::factory()->create();
    $role = Role::factory()->create();
    foreach (['execution.import', 'execution.validate', 'execution.process'] as $code) {
        $permission = Permission::query()->firstOrCreate(['code' => $code], ['name' => $code, 'module' => 'execution']);
        $role->permissions()->syncWithoutDetaching($permission);
    }
    $user->roles()->attach($role);

    return $user;
}

test('processes batch into budget accruals (LINAR1) and marks batch as processed', function () {
    $user = executionProcessorUser();
    $activity = PoaActivity::factory()->create();

    $batch = ImportBatch::factory()->create([
        'year' => 2026,
        'month' => 8,
        'status' => ImportBatchStatus::VALIDATED,
        'valid_rows' => 1,
        'unmatched_rows' => 0,
        'invalid_rows' => 0,
    ]);

    $row = ImportBatchRow::factory()->create([
        'import_batch_id' => $batch->id,
        'poa_activity_id' => $activity->id,
        'cur_number' => 'CUR-5544',
        'cur_line' => 1,
        'accrual_date' => '2026-08-20',
        'amount' => 1750.00,
        'status' => ImportBatchRowStatus::VALID,
    ]);

    $response = $this->actingAs($user)->post(route('execution-batches.process', $batch));
    $response->assertRedirect();

    expect($batch->refresh()->status)->toBe(ImportBatchStatus::PROCESSED)
        ->and($row->refresh()->status)->toBe(ImportBatchRowStatus::PROCESSED);

    $accrual = BudgetAccrual::query()->where('cur_number', 'CUR-5544')->firstOrFail();
    expect($accrual->accrued_amount)->toEqual('1750.00')
        ->and($accrual->poa_activity_id)->toBe($activity->id)
        ->and($accrual->year)->toBe(2026)
        ->and($accrual->month)->toBe(8);

    $this->get(route('budget-accruals.index'))->assertOk();
});

test('prevents processing batch when the execution period is closed', function () {
    $user = executionProcessorUser();
    $activity = PoaActivity::factory()->create();

    ExecutionClosing::factory()->create([
        'year' => 2026,
        'month' => 8,
        'status' => ExecutionClosingStatus::CLOSED,
    ]);

    $batch = ImportBatch::factory()->create([
        'year' => 2026,
        'month' => 8,
        'status' => ImportBatchStatus::VALIDATED,
    ]);

    ImportBatchRow::factory()->create([
        'import_batch_id' => $batch->id,
        'poa_activity_id' => $activity->id,
        'cur_number' => 'CUR-CLOSED',
        'accrual_date' => '2026-08-20',
        'amount' => 500.00,
        'status' => ImportBatchRowStatus::VALID,
    ]);

    $response = $this->actingAs($user)->post(route('execution-batches.process', $batch));
    $response->assertSessionHasErrors('process');
});
