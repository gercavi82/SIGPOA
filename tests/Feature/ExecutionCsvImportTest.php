<?php

use App\Enums\ImportBatchRowStatus;
use App\Enums\ImportBatchStatus;
use App\Models\ImportBatch;
use App\Models\Permission;
use App\Models\PoaActivity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;

function executionImportUser(array $permissions = ['execution.import', 'execution.validate', 'execution.process']): User
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

test('imports valid execution csv file into batch with rows', function () {
    $user = executionImportUser();
    $activity = PoaActivity::factory()->create(['number' => 'ACT-TEST-01']);

    $csvContent = "cur_number,cur_line,certification_number,activity_code,accrual_date,amount\n"
        ."CUR-1001,1,CP-001,ACT-TEST-01,2026-08-10,350.50\n"
        ."CUR-1002,1,CP-001,ACT-TEST-01,2026-08-11,420.00\n";

    $file = UploadedFile::fake()->createWithContent('esigef_cur_valid.csv', $csvContent);

    $response = $this->actingAs($user)->post(route('execution-batches.store'), [
        'year' => 2026,
        'month' => 8,
        'file' => $file,
    ]);

    $response->assertRedirect();

    $batch = ImportBatch::query()->latest('id')->firstOrFail();
    expect($batch->total_rows)->toBe(2)
        ->and($batch->valid_rows)->toBe(2)
        ->and($batch->invalid_rows)->toBe(0)
        ->and($batch->status)->toBe(ImportBatchStatus::VALIDATED);

    $this->get(route('execution-batches.show', $batch))->assertOk();
});

test('rejects duplicate csv file based on sha256 checksum for idempotency', function () {
    $user = executionImportUser();
    $activity = PoaActivity::factory()->create(['number' => 'ACT-DUP-01']);

    $csvContent = "cur_number,cur_line,certification_number,activity_code,accrual_date,amount\n"
        ."CUR-9999,1,CP-999,ACT-DUP-01,2026-08-12,800.00\n";

    $file1 = UploadedFile::fake()->createWithContent('cur_first.csv', $csvContent);
    $this->actingAs($user)->post(route('execution-batches.store'), [
        'year' => 2026,
        'month' => 8,
        'file' => $file1,
    ])->assertRedirect();

    // Segunda subida con el mismo contenido
    $file2 = UploadedFile::fake()->createWithContent('cur_duplicate.csv', $csvContent);
    $response = $this->actingAs($user)->post(route('execution-batches.store'), [
        'year' => 2026,
        'month' => 8,
        'file' => $file2,
    ]);

    $response->assertSessionHasErrors('file');
});

test('detects unmatched activity code and allows manual reconciliation', function () {
    $user = executionImportUser();
    $validActivity = PoaActivity::factory()->create(['number' => 'ACT-REAL-01']);

    $csvContent = "cur_number,cur_line,certification_number,activity_code,accrual_date,amount\n"
        ."CUR-2001,1,CP-002,ACT-UNKNOWN-CODE,2026-08-10,500.00\n";

    $file = UploadedFile::fake()->createWithContent('cur_unmatched.csv', $csvContent);

    $this->actingAs($user)->post(route('execution-batches.store'), [
        'year' => 2026,
        'month' => 8,
        'file' => $file,
    ])->assertRedirect();

    $batch = ImportBatch::query()->latest('id')->firstOrFail();
    expect($batch->unmatched_rows)->toBe(1)
        ->and($batch->status)->toBe(ImportBatchStatus::FAILED);

    $row = $batch->rows()->firstOrFail();
    expect($row->status)->toBe(ImportBatchRowStatus::UNMATCHED);

    // Conciliación manual
    $reconcileResponse = $this->actingAs($user)->put(route('execution-reconciliation.update', $row), [
        'poa_activity_id' => $validActivity->id,
    ]);

    $reconcileResponse->assertRedirect();
    expect($row->refresh()->status)->toBe(ImportBatchRowStatus::MATCHED)
        ->and($row->poa_activity_id)->toBe($validActivity->id)
        ->and($batch->refresh()->status)->toBe(ImportBatchStatus::VALIDATED);
});
