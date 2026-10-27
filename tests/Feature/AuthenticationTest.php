<?php

use App\Models\AuditLog;
use App\Models\User;

test('guest is redirected to login', function () {
    $this->get('/')->assertRedirect(route('login'));
});

test('active user can authenticate and logout', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    $this->post('/login', ['email' => $user->email, 'password' => 'secret-password'])
        ->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
    expect(AuditLog::query()->where('event', 'login')->exists())->toBeTrue();

    $this->post('/logout')->assertRedirect(route('login'));
    $this->assertGuest();
    expect(AuditLog::query()->where('event', 'logout')->exists())->toBeTrue();
});

test('inactive user cannot authenticate', function () {
    $user = User::factory()->create(['password' => 'secret-password', 'is_active' => false]);

    $this->post('/login', ['email' => $user->email, 'password' => 'secret-password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('login is validated', function () {
    $this->post('/login', ['email' => 'invalid', 'password' => ''])
        ->assertSessionHasErrors(['email', 'password']);
});
