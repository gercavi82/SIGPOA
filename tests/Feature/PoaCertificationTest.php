<?php

use App\Enums\ActivityStatus;
use App\Enums\CertificationStatus;
use App\Models\Document;
use App\Models\OrganizationalUnit;
use App\Models\Permission;
use App\Models\PlanningPeriod;
use App\Models\PoaActivity;
use App\Models\PoaCertification;
use App\Models\PoaProcess;
use App\Models\Role;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function certificationUser(): User
{
    $user = User::factory()->create();
    $role = Role::factory()->create();
    foreach (['certification.create', 'certification.review'] as $code) {
        $role->permissions()->attach(Permission::factory()->create(['code' => $code]));
    } $user->roles()->attach($role);

    return $user;
}

test('creates certification with private versioned document', function () {
    Storage::fake('local');
    $user = certificationUser();
    $unit = OrganizationalUnit::factory()->create();
    $period = PlanningPeriod::factory()->create();
    $process = PoaProcess::factory()->create(['planning_period_id' => $period, 'responsible_user_id' => $user, 'created_by' => $user]);
    $activity = PoaActivity::factory()->create(['poa_process_id' => $process, 'organizational_unit_id' => $unit, 'responsible_user_id' => $user, 'created_by' => $user, 'status' => ActivityStatus::Approved]);
    $payload = ['poa_process_id' => $process->id, 'organizational_unit_id' => $unit->id, 'number' => 'CERT-001', 'subject' => 'Solicitud', 'details' => [['poa_activity_id' => $activity->id, 'amount' => 50]], 'document_type' => 'SOLICITUD', 'document' => UploadedFile::fake()->create('solicitud.pdf', 100, 'application/pdf')];
    $this->actingAs($user)->post(route('poa-certifications.store'), $payload)->assertRedirect();
    expect(PoaCertification::query()->sole()->total_amount)->toBe('50.00')->and(Document::query()->sole()->checksum)->toHaveLength(64);
});

test('workflow signs area and final approvals through provider contract', function () {
    Storage::fake('local');
    $user = certificationUser();
    $unit = OrganizationalUnit::factory()->create();
    $period = PlanningPeriod::factory()->create();
    $process = PoaProcess::factory()->create(['planning_period_id' => $period, 'responsible_user_id' => $user, 'created_by' => $user]);
    $activity = PoaActivity::factory()->create(['poa_process_id' => $process, 'organizational_unit_id' => $unit, 'responsible_user_id' => $user, 'created_by' => $user]);
    $this->actingAs($user)->post(route('poa-certifications.store'), ['poa_process_id' => $process->id, 'organizational_unit_id' => $unit->id, 'number' => 'CERT-002', 'subject' => 'Solicitud', 'details' => [['poa_activity_id' => $activity->id, 'amount' => 20]], 'document_type' => 'SOLICITUD', 'document' => UploadedFile::fake()->create('solicitud.pdf', 100, 'application/pdf')]);
    $cert = PoaCertification::query()->sole();
    foreach ([CertificationStatus::SentArea, CertificationStatus::ApprovedArea, CertificationStatus::ReviewDpge, CertificationStatus::Preapproved, CertificationStatus::Approved] as $target) {
        $this->post(route('poa-certifications.transition', $cert), ['status' => $target->value, 'comment' => 'Avanzar', 'version' => $cert->refresh()->version])->assertSessionHasNoErrors();
    }
    expect(Signature::query()->count())->toBe(2);
});
