<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'active', 'permission:reports.view'])
        ->get('/protected-test-route', fn () => 'authorized');
});

test('user without permission is forbidden', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/protected-test-route')->assertForbidden();
});

test('user with permission can access protected route', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['is_active' => true]);
    $permission = Permission::factory()->create(['code' => 'reports.view']);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role);

    expect($user->hasPermission('reports.view'))->toBeTrue();
    $this->actingAs($user)->get('/protected-test-route')->assertSuccessful()->assertSee('authorized');
});
