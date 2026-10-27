<?php

use App\DTOs\Legacy\LegacyExecutionDto;
use App\DTOs\Legacy\LegacyPoaActivityDto;
use App\DTOs\Legacy\LegacyPoaCertificationDto;
use App\Integrations\Ibmi\IbmiCertificationRepository;
use App\Integrations\Ibmi\IbmiExecutionRepository;
use App\Integrations\Ibmi\IbmiPoaRepository;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Migration\MigrationService;
use App\Services\Migration\ReconciliationService;

function adminMigrationUser(): User
{
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $permission = Permission::query()->firstOrCreate(['code' => 'admin.parameters'], ['name' => 'Parámetros Admin', 'module' => 'admin']);
    $role->permissions()->syncWithoutDetaching($permission);
    $user->roles()->attach($role);

    return $user;
}

test('reconciliation service produces formal report with sha256 checksum and relationship classification', function () {
    $service = app(ReconciliationService::class);
    $report = $service->generateReport(2026);

    expect($report)->toHaveKeys(['year', 'generated_at', 'integrity_hash', 'overall_status', 'tables', 'relationship_classification'])
        ->and($report['integrity_hash'])->toBeString()
        ->and($report['tables'])->toHaveKeys(['PLAARC', 'TECARC', 'CERAR2', 'CERA01', 'SOLAR1', 'LINAR1']);
});

test('migration service executes migration inserting legacy data into postgresql and returns reconciliation', function () {
    $user = adminMigrationUser();

    // Mock adaptadores para inyectar datos legacy DTO controlados
    $poaRepoMock = Mockery::mock(IbmiPoaRepository::class);
    $certRepoMock = Mockery::mock(IbmiCertificationRepository::class);
    $execRepoMock = Mockery::mock(IbmiExecutionRepository::class);

    $activityDto = new LegacyPoaActivityDto(
        year: 2026,
        unitCode: 'DIR-OPS',
        activityNumber: 'ACT-MIG-01',
        description: 'Operación radar migrada',
        objective: 'Objetivo radar',
        priority: 1,
        annualTarget: 100,
        annualAmount: 25000.00,
        status: 'APROBADA',
        months: [
            ['month' => 1, 'target' => 50, 'amount' => 12500.00],
            ['month' => 2, 'target' => 50, 'amount' => 12500.00],
        ]
    );

    $certDto = new LegacyPoaCertificationDto(
        year: 2026,
        certificationNumber: 'CER-MIG-001',
        unitCode: 'DIR-OPS',
        subject: 'Certificación Migrada',
        totalAmount: 12500.00,
        status: 'APROBADA',
        lines: [
            ['activity_number' => 'ACT-MIG-01', 'amount' => 12500.00, 'description' => 'Línea 1'],
        ]
    );

    $execDto = new LegacyExecutionDto(
        year: 2026,
        month: 1,
        curNumber: 'CUR-MIG-999',
        curLine: 1,
        activityNumber: 'ACT-MIG-01',
        certificationNumber: null,
        accrualDate: '2026-01-15',
        accruedAmount: 5000.00,
        status: 'ACTIVO'
    );

    $poaRepoMock->shouldReceive('getPoaActivities')->with(2026)->andReturn(collect([$activityDto]));
    $poaRepoMock->shouldReceive('getBudgetCeilings')->with(2026)->andReturn(collect());
    $certRepoMock->shouldReceive('getPoaCertifications')->with(2026)->andReturn(collect([$certDto]));
    $certRepoMock->shouldReceive('getBudgetCertifications')->with(2026)->andReturn(collect());
    $execRepoMock->shouldReceive('getExecutionAccruals')->with(2026)->andReturn(collect([$execDto]));

    $this->app->instance(IbmiPoaRepository::class, $poaRepoMock);
    $this->app->instance(IbmiCertificationRepository::class, $certRepoMock);
    $this->app->instance(IbmiExecutionRepository::class, $execRepoMock);

    $migrationService = app(MigrationService::class);
    $result = $migrationService->migrateYear(2026, $user, dryRun: false);

    expect($result['migrated_activities'])->toBe(1)
        ->and($result['migrated_certifications'])->toBe(1)
        ->and($result['migrated_accruals'])->toBe(1)
        ->and($result['reconciliation_report'])->toBeArray();

    $this->assertDatabaseHas('poa_activities', [
        'number' => 'ACT-MIG-01',
        'annual_amount' => 25000.00,
    ]);

    $this->assertDatabaseHas('poa_certifications', [
        'number' => 'CER-MIG-001',
        'total_amount' => 12500.00,
    ]);

    $this->assertDatabaseHas('budget_accruals', [
        'cur_number' => 'CUR-MIG-999',
        'accrued_amount' => 5000.00,
    ]);
});

test('artisan commands sigpoa:reconcile-legacy and sigpoa:migrate-legacy run successfully', function () {
    $this->artisan('sigpoa:reconcile-legacy', ['--year' => 2026])
        ->assertSuccessful();

    $this->artisan('sigpoa:migrate-legacy', ['--year' => 2026, '--dry-run' => true])
        ->assertSuccessful();
});

test('reconciliation web portal is accessible for admin and forbidden for unauthorized users', function () {
    $admin = adminMigrationUser();
    $regularUser = User::factory()->create();

    $this->actingAs($admin)->get(route('migration.reconciliation', ['year' => 2026]))
        ->assertOk()
        ->assertSee('Convivencia y Reconciliación DB2')
        ->assertSee('Checksum de Integridad SHA-256');

    $this->actingAs($regularUser)->get(route('migration.reconciliation'))
        ->assertForbidden();
});
