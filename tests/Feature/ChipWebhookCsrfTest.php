<?php

declare(strict_types=1);

use AIArmada\Chip\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

afterEach(function (): void {
    Mockery::close();
});

test('webhook route configuration accepts optional identifier', function () {
    expect(Route::has('webhooks.chip'))->toBeTrue();

    $route = Route::getRoutes()->getByName('webhooks.chip');

    expect($route->methods())->toContain('POST');
    expect($route->uri())->toBe('webhooks/chip/{webhook?}');
});

test('success callback endpoint responds without CSRF token', function () {
    $response = $this->post('/webhooks/chip', [
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'test_purchase_id',
            'amount' => 10000,
        ],
    ], [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ]);

    expect($response->status())->not->toBe(419);
});

test('webhook endpoint responds without CSRF token', function () {
    $response = $this->post('/webhooks/chip/wh_test', [
        'event' => 'purchase.paid',
        'data' => [
            'id' => 'test_purchase_id',
            'amount' => 10000,
        ],
    ], [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ]);

    expect($response->status())->not->toBe(419);
});

test('success callback verification uses default public key', function () {
    $service = Mockery::mock(WebhookService::class);
    $service->shouldReceive('getPublicKey')
        ->once()
        ->with(null)
        ->andReturn('company-key');
    $service->shouldReceive('verifySignature')
        ->once()
        ->with(Mockery::type(Request::class), null, 'company-key')
        ->andReturnFalse();
    app()->instance(WebhookService::class, $service);

    $response = $this->postJson('/webhooks/chip', [
        'event' => 'purchase.paid',
        'data' => ['id' => 'test_purchase_id'],
    ]);

    $response->assertStatus(401);
});

test('webhook verification loads webhook specific public key', function () {
    $service = Mockery::mock(WebhookService::class);
    $service->shouldReceive('getPublicKey')
        ->once()
        ->with('wh_test')
        ->andReturn('pem-key');
    $service->shouldReceive('verifySignature')
        ->once()
        ->with(Mockery::type(Request::class), null, 'pem-key')
        ->andReturnFalse();
    app()->instance(WebhookService::class, $service);

    $response = $this->postJson('/webhooks/chip/wh_test', [
        'event' => 'purchase.paid',
        'data' => ['id' => 'test_purchase_id'],
    ]);

    $response->assertStatus(401);
});
