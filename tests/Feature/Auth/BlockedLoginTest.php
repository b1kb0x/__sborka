<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not allow a blocked user to log in', function () {
    $user = User::factory()->create([
        'email' => 'blocked-login@example.test',
        'password' => 'password',
        'role' => UserRole::Customer,
        'status' => UserStatus::Blocked,
    ]);

    $this->from(route('login'))
        ->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email']);

    $this->assertGuest();
});

it('logs a blocked customer out before entering the customer cabinet', function () {
    $user = User::factory()->create([
        'role' => UserRole::Customer,
        'status' => UserStatus::Blocked,
    ]);

    $this->actingAs($user)
        ->get(route('customer.dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email']);

    $this->assertGuest();
});

it('logs a blocked admin out before entering the admin cabinet', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Blocked,
    ]);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email']);

    $this->assertGuest();
});
