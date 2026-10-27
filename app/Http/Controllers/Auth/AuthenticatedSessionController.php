<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, AuthenticateUserAction $authenticate): RedirectResponse
    {
        try {
            $authenticate->handle($request->validated());
        } catch (AuthenticationException) {
            return back()->withErrors(['email' => 'Las credenciales no son válidas o la cuenta está inactiva.'])->onlyInput('email');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request, AuditService $auditService): RedirectResponse
    {
        $auditService->record('logout', $request->user(), 'authentication');
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
