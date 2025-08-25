<?php

namespace App\Traits;

use Joelwmale\Cart\Facades\CartFacade as Cart;
use Illuminate\Support\Facades\Auth;

trait ManagesCart
{
    protected function setCartSession(): void
    {
        Cart::setSessionKey(Auth::check() ? (string) Auth::id() : session()->getId());
    }
    
    protected function getCartCount(): int
    {
        $this->setCartSession();
        return Cart::getTotalQuantity();
    }
    
    protected function getCartSubtotal(): int
    {
        $this->setCartSession();
        $subtotal = Cart::getSubTotal();
        return is_numeric($subtotal) ? (int) $subtotal : 0;
    }
    
    protected function getCartTotal(): int
    {
        $this->setCartSession();
        $total = Cart::getTotal();
        return is_numeric($total) ? (int) $total : 0;
    }
}
