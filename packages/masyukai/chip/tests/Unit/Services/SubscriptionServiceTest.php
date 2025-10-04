<?php

declare(strict_types=1);

use MasyukAI\Chip\DataObjects\Purchase;
use MasyukAI\Chip\Exceptions\ChipValidationException;
use MasyukAI\Chip\Services\ChipCollectService;
use MasyukAI\Chip\Services\SubscriptionService;

beforeEach(function () {
    $this->collectService = Mockery::mock(ChipCollectService::class);
    $this->service = new SubscriptionService($this->collectService);
});

it('uses the CHIP brand id from the collect service when none is provided', function () {
    $trialPayload = [
        'client' => ['email' => 'subscriber@example.com'],
        'payment_method_whitelist' => ['visa'],
    ];

    $this->collectService->shouldReceive('getBrandId')->andReturn('brand-from-config');
    $this->collectService->shouldReceive('createPurchase')
        ->once()
        ->with(Mockery::on(function (array $payload) {
            expect($payload['brand_id'])->toBe('brand-from-config');

            return true;
        }))
        ->andReturn(Mockery::mock(Purchase::class));

    $this->service->createWithFreeTrial($trialPayload);
});

it('throws when no brand id can be resolved for subscription helpers', function () {
    $this->collectService->shouldReceive('getBrandId')->andReturn('');

    expect(fn () => $this->service->createWithFreeTrial([
        'client' => ['email' => 'subscriber@example.com'],
    ]))->toThrow(ChipValidationException::class);
});

afterEach(function () {
    Mockery::close();
});
