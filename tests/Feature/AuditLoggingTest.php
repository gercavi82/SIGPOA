<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Context;

test('audit entry records actor entity and correlation id', function () {
    $user = User::factory()->create();
    Context::add('correlation_id', '8d9b26a8-0294-4552-a9e7-9dbde80de900');

    $this->actingAs($user);
    app(AuditService::class)->record('updated', $user, 'administration', ['name' => 'Antes'], ['name' => 'Después']);

    $auditLog = AuditLog::query()->sole();
    expect($auditLog->user_id)->toBe($user->id)
        ->and($auditLog->auditable_type)->toBe($user->getMorphClass())
        ->and($auditLog->correlation_id)->toBe('8d9b26a8-0294-4552-a9e7-9dbde80de900')
        ->and($auditLog->new_values)->toBe(['name' => 'Después']);
});
