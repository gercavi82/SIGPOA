<?php

namespace App\Actions\Auth;

use App\Services\AuditService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class AuthenticateUserAction
{
    public function __construct(private AuditService $auditService) {}

    /** @param array{email: string, password: string, remember?: bool} $credentials */
    public function handle(array $credentials): void
    {
        $remember = (bool) ($credentials['remember'] ?? false);
        unset($credentials['remember']);

        if (! Auth::attempt([...$credentials, 'is_active' => true], $remember)) {
            $this->auditService->record('login_failed', module: 'authentication', newValues: ['email' => $credentials['email']]);
            throw new AuthenticationException;
        }

        request()->session()->regenerate();
        Auth::user()->forceFill(['last_login_at' => now()])->save();
        $this->auditService->record('login', Auth::user(), 'authentication');
    }
}
