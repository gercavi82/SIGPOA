<?php

use App\Enums\ActivityStatus;
use App\Enums\ModificationStatus;
use App\Enums\ModificationType;
use App\Enums\ProcessStatus;
use App\Models\Document;
use App\Models\OrganizationalUnit;
use App\Models\Permission;
use App\Models\PlanningPeriod;
use App\Models\PoaActivity;
use App\Models\PoaModificationRequest;
use App\Models\PoaModificationResult;
use App\Models\PoaProcess;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function modificationUser(array $permissions = ['modification.create', 'modification.review', 'modification.verify_budget']): User
{
    $user = User::factory()->create();
    $role = Role::factory()->create(['is_active' => true]);

    foreach ($permissions as $code) {
        $permission = Permission::query()->firstOrCreate(['code' => $code], [
            'name' => $code,
            'module' => 'modification',
        ]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role);

    return $user;
}

test('guest is redirected to login when accessing modifications', function () {
    $this->get(route('modifications.index'))->assertRedirect(route('login'));
    $this->get(route('modifications.create'))->assertRedirect(route('login'));
});

test('user without modification.create permission is forbidden on create form', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('modifications.create'))->assertForbidden();
});

test('renders modification create form for authorized user', function () {
    $user = modificationUser(['modification.create']);
    $period = PlanningPeriod::factory()->create();
    PoaProcess::factory()->create(['planning_period_id' => $period, 'status' => ProcessStatus::Open]);
    OrganizationalUnit::factory()->create();
    PoaActivity::factory()->create(['status' => ActivityStatus::Approved]);

    $this->actingAs($user)
        ->get(route('modifications.create'))
        ->assertSuccessful()
        ->assertSee('Nueva Solicitud de Modificación POA')
        ->assertSee('Líneas de Movimiento');
});

test('creates modification request with lines, months and document via UI', function () {
    Storage::fake('local');
    $user = modificationUser(['modification.create', 'modification.review']);
    $unit = OrganizationalUnit::factory()->create();
    $period = PlanningPeriod::factory()->create();
    $process = PoaProcess::factory()->create(['planning_period_id' => $period, 'status' => ProcessStatus::Open]);
    $actSource = PoaActivity::factory()->create(['poa_process_id' => $process, 'organizational_unit_id' => $unit, 'status' => ActivityStatus::Approved]);
    $actDest = PoaActivity::factory()->create(['poa_process_id' => $process, 'organizational_unit_id' => $unit, 'status' => ActivityStatus::Approved]);

    $payload = [
        'number' => 'MOD-2026-UI-01',
        'type' => ModificationType::WithoutBudgetImpact->value,
        'poa_process_id' => $process->id,
        'organizational_unit_id' => $unit->id,
        'justification' => 'Reasignación de recursos entre actividades operativas.',
        'lines' => [
            [
                'source_activity_id' => $actSource->id,
                'destination_activity_id' => $actDest->id,
                'debit_amount' => 500.00,
                'credit_amount' => 500.00,
                'description' => 'Traspaso de fondos por optimización de metas',
                'months' => [
                    ['month' => 1, 'debit_amount' => 250.00, 'credit_amount' => 0],
                    ['month' => 2, 'debit_amount' => 250.00, 'credit_amount' => 500.00],
                ],
            ],
        ],
        'document_type' => 'SOLICITUD_MODIFICACION',
        'document' => UploadedFile::fake()->create('memorando_solicitud.pdf', 200, 'application/pdf'),
    ];

    $response = $this->actingAs($user)->post(route('modifications.store'), $payload);

    $mod = PoaModificationRequest::query()->where('number', 'MOD-2026-UI-01')->firstOrFail();

    $response->assertRedirect(route('modifications.show', $mod));
    expect($mod->lines)->toHaveCount(1)
        ->and($mod->lines->first()->months)->toHaveCount(2)
        ->and($mod->documents)->toHaveCount(1)
        ->and(Document::query()->sole()->checksum)->toHaveLength(64);
});

test('rejects creation when debits and credits are unbalanced for internal modification', function () {
    $user = modificationUser(['modification.create']);
    $unit = OrganizationalUnit::factory()->create();
    $period = PlanningPeriod::factory()->create();
    $process = PoaProcess::factory()->create(['planning_period_id' => $period, 'status' => ProcessStatus::Open]);

    $payload = [
        'number' => 'MOD-UNBALANCED-01',
        'type' => ModificationType::WithoutBudgetImpact->value,
        'poa_process_id' => $process->id,
        'organizational_unit_id' => $unit->id,
        'justification' => 'Desbalanceado intencional',
        'lines' => [
            [
                'debit_amount' => 1000.00,
                'credit_amount' => 800.00,
                'description' => 'Desbalance',
            ],
        ],
    ];

    $this->actingAs($user)
        ->from(route('modifications.create'))
        ->post(route('modifications.store'), $payload)
        ->assertRedirect(route('modifications.create'))
        ->assertSessionHasErrors('modification');

    expect(PoaModificationRequest::query()->where('number', 'MOD-UNBALANCED-01')->exists())->toBeFalse();
});

test('shows modification detail page with lines, monthly distributions and documents', function () {
    $user = modificationUser(['modification.review']);
    $unit = OrganizationalUnit::factory()->create();
    $period = PlanningPeriod::factory()->create();
    $process = PoaProcess::factory()->create(['planning_period_id' => $period]);
    $actSource = PoaActivity::factory()->create(['poa_process_id' => $process, 'number' => 'ACT-SRC-100']);
    $actDest = PoaActivity::factory()->create(['poa_process_id' => $process, 'number' => 'ACT-DST-200']);

    $mod = PoaModificationRequest::query()->create([
        'number' => 'MOD-SHOW-01',
        'type' => ModificationType::WithoutBudgetImpact,
        'status' => ModificationStatus::Draft,
        'poa_process_id' => $process->id,
        'organizational_unit_id' => $unit->id,
        'justification' => 'Justificación detallada de la prueba.',
        'requested_by' => $user->id,
    ]);

    $line = $mod->lines()->create([
        'source_activity_id' => $actSource->id,
        'destination_activity_id' => $actDest->id,
        'debit_amount' => 300.00,
        'credit_amount' => 300.00,
        'description' => 'Detalle de la línea de prueba',
    ]);

    $line->months()->create([
        'month' => 3,
        'debit_amount' => 300.00,
        'credit_amount' => 300.00,
    ]);

    $this->actingAs($user)
        ->get(route('modifications.show', $mod))
        ->assertSuccessful()
        ->assertSee('MOD-SHOW-01')
        ->assertSee('ACT-SRC-100')
        ->assertSee('ACT-DST-200')
        ->assertSee('Detalle de la línea de prueba')
        ->assertSee('300.00');
});

test('user can execute state transitions from show page', function () {
    $user = modificationUser(['modification.review']);
    $unit = OrganizationalUnit::factory()->create();
    $period = PlanningPeriod::factory()->create();
    $process = PoaProcess::factory()->create(['planning_period_id' => $period]);

    $mod = PoaModificationRequest::query()->create([
        'number' => 'MOD-TRANS-01',
        'type' => ModificationType::WithoutBudgetImpact,
        'status' => ModificationStatus::Draft,
        'poa_process_id' => $process->id,
        'organizational_unit_id' => $unit->id,
        'justification' => 'Flujo de aprobación',
        'requested_by' => $user->id,
        'version' => 1,
    ]);

    $response = $this->actingAs($user)->post(route('modifications.transition', $mod), [
        'status' => ModificationStatus::SentArea->value,
        'comment' => 'Envío formal a Dirección de Área',
        'version' => 1,
    ]);

    $response->assertSessionHasNoErrors();
    expect($mod->refresh()->status)->toBe(ModificationStatus::SentArea)
        ->and($mod->version)->toBe(2)
        ->and($mod->workflowTransitions)->toHaveCount(1)
        ->and($mod->workflowTransitions->first()->to_status)->toBe('ENVIADA_AREA');
});

test('invalid transition from show page fails with error', function () {
    $user = modificationUser(['modification.review']);
    $mod = PoaModificationRequest::factory()->create([
        'status' => ModificationStatus::Draft,
        'version' => 1,
    ]);

    $this->actingAs($user)->post(route('modifications.transition', $mod), [
        'status' => ModificationStatus::Approved->value, // Draft cannot go directly to Approved
        'comment' => 'Intento inválido',
        'version' => 1,
    ])->assertSessionHasErrors('status');

    expect($mod->refresh()->status)->toBe(ModificationStatus::Draft);
});

test('edit page is accessible in draft state and forbidden in approved state', function () {
    $user = modificationUser(['modification.create']);
    $modDraft = PoaModificationRequest::factory()->create(['status' => ModificationStatus::Draft]);
    $modApproved = PoaModificationRequest::factory()->create(['status' => ModificationStatus::Approved]);

    $this->actingAs($user)->get(route('modifications.edit', $modDraft))->assertSuccessful()->assertSee('Corregir Solicitud');
    $this->actingAs($user)->get(route('modifications.edit', $modApproved))->assertForbidden();
});

test('updates and versions modification request in draft state', function () {
    $user = modificationUser(['modification.create']);
    $unit = OrganizationalUnit::factory()->create();
    $period = PlanningPeriod::factory()->create();
    $process = PoaProcess::factory()->create(['planning_period_id' => $period, 'status' => ProcessStatus::Open]);

    $mod = PoaModificationRequest::query()->create([
        'number' => 'MOD-EDIT-01',
        'type' => ModificationType::WithoutBudgetImpact,
        'status' => ModificationStatus::Draft,
        'poa_process_id' => $process->id,
        'organizational_unit_id' => $unit->id,
        'justification' => 'Texto anterior',
        'requested_by' => $user->id,
        'version' => 1,
    ]);

    $payload = [
        'number' => 'MOD-EDIT-01',
        'type' => ModificationType::WithoutBudgetImpact->value,
        'poa_process_id' => $process->id,
        'organizational_unit_id' => $unit->id,
        'justification' => 'Texto corregido y ampliado',
        'version' => 1,
        'lines' => [
            [
                'debit_amount' => 200.00,
                'credit_amount' => 200.00,
                'description' => 'Línea corregida',
            ],
        ],
    ];

    $this->actingAs($user)
        ->put(route('modifications.update', $mod), $payload)
        ->assertRedirect(route('modifications.show', $mod));

    $mod->refresh();
    expect($mod->version)->toBe(2)
        ->and($mod->justification)->toBe('Texto corregido y ampliado')
        ->and($mod->lines)->toHaveCount(1)
        ->and($mod->lines->first()->description)->toBe('Línea corregida');
});

test('records technical viability report on modification', function () {
    $user = modificationUser(['modification.review']);
    $mod = PoaModificationRequest::factory()->create([
        'status' => ModificationStatus::DpgeReview,
    ]);

    $this->actingAs($user)->post(route('modifications.viability', $mod), [
        'viability_report' => 'Dictamen favorable. Se valida la conveniencia técnica de la redistribución.',
    ])->assertSessionHasNoErrors();

    $result = PoaModificationResult::query()->where('poa_modification_request_id', $mod->id)->firstOrFail();
    expect($result->viability_report)->toContain('Dictamen favorable')
        ->and($result->approved_by)->toBe($user->id);
});

test('records esigef reform number and cur', function () {
    $user = modificationUser(['modification.verify_budget']);
    $mod = PoaModificationRequest::factory()->create([
        'requires_esigef' => true,
        'status' => ModificationStatus::Authorized,
    ]);

    $this->actingAs($user)->post(route('modifications.esigef', $mod), [
        'esigef_reform_number' => 'REF-ESIGEF-2026-99',
        'reform_cur' => 'CUR-REF-8844',
    ])->assertSessionHasNoErrors();

    $mod->refresh();
    expect($mod->esigef_reform_number)->toBe('REF-ESIGEF-2026-99')
        ->and($mod->reform_cur)->toBe('CUR-REF-8844');
});

test('attaches additional enabling document to modification request', function () {
    Storage::fake('local');
    $user = modificationUser(['modification.create']);
    $mod = PoaModificationRequest::factory()->create();

    $this->actingAs($user)->post(route('modifications.documents.store', $mod), [
        'document_type' => 'RESOLUCION_DPGE',
        'document' => UploadedFile::fake()->create('resolucion.pdf', 150, 'application/pdf'),
    ])->assertSessionHasNoErrors();

    expect($mod->documents)->toHaveCount(1)
        ->and($mod->documents->first()->document_type)->toBe('RESOLUCION_DPGE');
});
