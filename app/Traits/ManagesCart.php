<?php

namespace App\Traits;

use MasyukAI\Cart\Facades\Cart;
use Illuminate\Support\Facades\Auth;

trait ManagesCart
{
    protected function setCartSession(?string $instance = null): void
    {
        $sessionKey = Auth::check() ? (string) Auth::id() : session()->getId();
        $instanceName = $instance ?? 'user_' . $sessionKey;
        
        // Set cart instance
        Cart::setInstance($instanceName);
    }
    
    protected function getCartCount(?string $instance = null): int
    {
        $this->setCartSession($instance);
        return Cart::getTotalQuantity();
    }
    
    protected function getCartSubtotal(?string $instance = null): int
    {
        $this->setCartSession($instance);
        $subtotal = Cart::getSubTotal();
        return is_numeric($subtotal) ? (int) ($subtotal * 100) : 0; // Convert to cents
    }
    
    protected function getCartTotal(?string $instance = null): int
    {
        $this->setCartSession($instance);
        $total = Cart::getTotal();
        return is_numeric($total) ? (int) ($total * 100) : 0; // Convert to cents
    }

    protected function getCartContent(?string $instance = null)
    {
        $this->setCartSession($instance);
        return Cart::getContent();
    }
}
