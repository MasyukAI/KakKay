<?php

declare(strict_types=1);

it('debugs currency issue', function () {
    echo "Default currency from config: " . config('cart.money.default_currency') . PHP_EOL;
    echo "Type: " . gettype(config('cart.money.default_currency')) . PHP_EOL;
    
    try {
        $money = \MasyukAI\Cart\Support\CartMoney::fromAmount(100.00);
        echo "Success: Currency = " . $money->getCurrency() . PHP_EOL;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . PHP_EOL;
    }
});
