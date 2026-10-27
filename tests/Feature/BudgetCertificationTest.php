<?php

use App\Models\BudgetCertification;
use App\Models\Permission;
use App\Models\PoaActivity;
use App\Models\Role;
use App\Models\User;

function budgetCertUser(array $permissions = ['certification.create', 'execution.import']): User
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

test('creates budget certification with detail lines and audit log', function () {
    $user = budgetCertUser();
    $activity = PoaActivity::factory()->create();

    $payload = [
        'certification_number' => 'CP-2026-0099',
        'certification_date' => '2026-08-15',
        'year' => 2026,
        'total_amount' => 1250.00,
        'funding_source' => '001',
        'concept' => 'Certificación presupuestaria para adquisición de insumos aeronáuticos',
        'lines' => [
            [
                'line_number' => 1,
                'concept' => 'Insumos lote A',
                'poa_activity_id' => $activity->id,
                'funding_source' => '001',
                'amount' => 1250.00,
            ],
        ],
    ];

    $response = $this->actingAs($user)->post(route('budget-certifications.store'), $payload);
    $response->assertRedirect();

    $cert = BudgetCertification::query()->where('certification_number', 'CP-2026-0099')->firstOrFail();
    expect($cert->total_amount)->toEqual('1250.00')
        ->and($cert->lines)->toHaveCount(1)
        ->and($cert->lines->first()->poa_activity_id)->toBe($activity->id);

    $this->get(route('budget-certifications.show', $cert))->assertOk();
});

test('rejects creation when lines sum does not match total amount', function () {
    $user = budgetCertUser();
    $activity = PoaActivity::factory()->create();

    $payload = [
        'certification_number' => 'CP-2026-0100',
        'certification_date' => '2026-08-15',
        'year' => 2026,
        'total_amount' => 2000.00,
        'concept' => 'Monto inconsistente',
        'lines' => [
            [
                'line_number' => 1,
                'poa_activity_id' => $activity->id,
                'amount' => 1500.00,
            ],
        ],
    ];

    $response = $this->actingAs($user)->post(route('budget-certifications.store'), $payload);
    $response->assertSessionHasErrors('lines');
});
