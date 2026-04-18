<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('uses runtime store name on the welcome page and in the admin interface', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'status' => UserStatus::Active,
    ]);

    app(SettingsService::class)->set('general.store_name', 'Runtime Shop');

    $this->get('/')
        ->assertOk()
        ->assertSee('<title>Runtime Shop</title>', false);

    $this->actingAs($admin)
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('<title>Runtime Shop</title>', false)
        ->assertSee('&copy; '.now()->year.' Runtime Shop', false);
});

it('falls back to app name when runtime store name is blank', function () {
    $appName = config('app.name');

    app(SettingsService::class)->set('general.store_name', '   ');

    $this->get('/')
        ->assertOk()
        ->assertSee('<title>'.$appName.'</title>', false);
});
