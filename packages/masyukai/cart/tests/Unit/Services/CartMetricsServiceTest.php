<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use MasyukAI\Cart\Services\CartMetricsService;

beforeEach(function (): void {
    Cache::store()->flush();
    config()->set('cart.metrics.enabled', true);
    config()->set('cart.metrics.track_conflicts', true);
    config()->set('cart.metrics.log_channel', null);
});

it('aggregates operation metrics and totals', function (): void {
    $service = app(CartMetricsService::class);

    $service->recordOperation('add');
    $service->recordOperation('add');

    $today = now()->format('Y-m-d');

    expect(Cache::get('cart_metrics:operations'))->toBe(2)
        ->and(Cache::get('cart_metrics:operations:add'))->toBe(2)
        ->and(Cache::get("cart_metrics:operations:{$today}"))->toBe(2)
        ->and(Cache::get("cart_metrics:operations:add:{$today}"))->toBe(2);
});

it('skips metrics recording when disabled', function (): void {
    config()->set('cart.metrics.enabled', false);

    app(CartMetricsService::class)->recordOperation('add');

    expect(Cache::get('cart_metrics:operations'))->toBeNull();
});

it('falls back to default logger when channel is missing', function (): void {
    config()->set('cart.metrics.log_channel', 'missing-channel');

    $service = app(CartMetricsService::class);

    expect(fn () => $service->recordOperation('add'))->not->toThrow(Exception::class);
});
