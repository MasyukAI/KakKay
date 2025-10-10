<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart\Services;

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Contracts\RulesFactoryInterface;
use MasyukAI\Cart\Facades\Cart as CartFacade;

final class CartInstanceManager
{
    public function __construct(private readonly RulesFactoryInterface $rulesFactory) {}

    public function resolve(string $instance, string $identifier): Cart
    {
        $cart = CartFacade::getCartInstance($instance, $identifier);

        return $cart->withRulesFactory($this->rulesFactory);
    }

    public function prepare(Cart $cart): Cart
    {
        return $cart->withRulesFactory($this->rulesFactory);
    }
}
