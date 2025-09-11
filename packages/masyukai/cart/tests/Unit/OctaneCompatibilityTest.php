<?php

declare(strict_types=1);

use MasyukAI\Cart\Listeners\ResetCartState;
use MasyukAI\Cart\Support\CartMoney;

describe('Octane Compatibility Tests', function () {
    beforeEach(function () {
        // Ensure config is in a known state
        config(['cart.display.formatting_enabled' => false]);
    });

    it('resets static state in CartMoney', function () {
        // Set some static state
        CartMoney::enableFormatting();
        CartMoney::setCurrency('EUR');

        // Verify state is set
        expect(CartMoney::shouldFormat())->toBeTrue();

        // Reset state using Octane listener
        $listener = new ResetCartState;
        $listener->handle();

        // Verify state is reset
        expect(CartMoney::shouldFormat())->toBeFalse();
    });

    it('handles multiple state resets correctly', function () {
        for ($i = 0; $i < 5; $i++) {
            // Set different state each time
            CartMoney::enableFormatting();
            CartMoney::setCurrency('GBP');

            expect(CartMoney::shouldFormat())->toBeTrue();

            // Reset
            $listener = new ResetCartState;
            $listener->handle();

            expect(CartMoney::shouldFormat())->toBeFalse();
        }
    });

    it('does not cause memory leaks with repeated operations', function () {
        $initialMemory = memory_get_usage();

        // Perform many operations that could cause leaks
        for ($i = 0; $i < 100; $i++) {
            CartMoney::enableFormatting();
            CartMoney::setCurrency('USD');
            $money = CartMoney::fromAmount(99.99);
            $money->format();

            $listener = new ResetCartState;
            $listener->handle();
        }

        $finalMemory = memory_get_usage();
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be minimal (less than 1MB)
        expect($memoryIncrease)->toBeLessThan(1024 * 1024);
    });

    it('maintains independent formatting state across simulated requests', function () {
        // Simulate first request
        CartMoney::enableFormatting();
        CartMoney::setCurrency('EUR');
        $money1 = CartMoney::fromAmount(100.00);
        $firstRequestResult = $money1->format();

        // End first request (Octane operation terminated)
        $listener = new ResetCartState;
        $listener->handle();

        // Simulate second request with different settings
        CartMoney::enableFormatting();
        CartMoney::setCurrency('USD');
        $money2 = CartMoney::fromAmount(100.00);
        $secondRequestResult = $money2->format();

        // Results should be different (different currencies) if currency symbols are shown
        config(['cart.display.show_currency_symbol' => true]);
        $money3 = CartMoney::fromAmount(100.00);
        $firstRequestResultWithSymbol = $money3->format();

        CartMoney::setCurrency('EUR');
        $money4 = CartMoney::fromAmount(100.00);
        $eurResultWithSymbol = $money4->format();

        // The EUR and USD formatted results should be different when currency symbols are enabled
        expect($firstRequestResultWithSymbol)->not->toBe($eurResultWithSymbol);

        // End second request
        $listener->handle();

        // State should be clean
        expect(CartMoney::shouldFormat())->toBeFalse();
    });
});
