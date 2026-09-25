<?php

use App\Models\User;

test('guests are redirected to the panel login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('a signed-in user can open the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Dashboard');
});

test('in production only configured admin emails may enter the panel', function () {
    app()->detectEnvironment(fn (): string => 'production');
    config(['portfolio.admin_emails' => ['owner@example.com']]);

    expect(User::factory()->make(['email' => 'Owner@Example.com'])->canAccessPanel(filament()->getPanel('admin')))->toBeTrue()
        ->and(User::factory()->make(['email' => 'intruder@example.com'])->canAccessPanel(filament()->getPanel('admin')))->toBeFalse();
});
