<?php

declare(strict_types=1);

use Livewire\Volt\Volt;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    // Set a dummy cart migration cache value to avoid null sessionId error
    $dummySessionId = 'dummy-session-id-123';
    $dummyEmail = 'test@example.com';
    Illuminate\Support\Facades\Cache::put("cart_migration_{$dummyEmail}", $dummySessionId);

    $response = Volt::test('auth.register')
        ->set('name', 'Test User')
        ->set('email', $dummyEmail)
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});
