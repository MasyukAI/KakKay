<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use AIArmada\Cart\Facades\Cart;
use App\Services\ShippingService;
use Livewire\Component;

final class ShippingMethodSelector extends Component
{
    public string $selectedMethod = 'standard';

    public array $availableMethods = [];

    public bool $shippingRequired = true;

    protected $listeners = ['refresh-shipping-methods' => 'refreshShippingMethods'];

    public function mount(ShippingService $shippingService)
    {
        $this->availableMethods = $shippingService->getAvailableShippingMethods();
        $this->shippingRequired = $this->checkIfShippingRequired();
        $this->initializeSelectedMethod();

        // Make sure shipping is applied even if none was previously set
        if (Cart::getShippingValue() === null && $this->shippingRequired) {
            $this->updateShippingMethod($this->selectedMethod);
        }
    }

    public function initializeSelectedMethod()
    {
        // Check if shipping method is already set in the cart
        $shippingMethod = Cart::getShippingMethod();

        if ($shippingMethod !== null) {
            // Check if the method ID exists in our available methods
            foreach ($this->availableMethods as $method) {
                if ($method['id'] === $shippingMethod) {
                    $this->selectedMethod = $method['id'];

                    return;
                }
            }
        }

        // If no method is set or the method doesn't match, check if we can match by price
        $shippingValue = Cart::getShippingValue();
        if ($shippingValue !== null) {
            // Try to match the shipping value with an available method
            foreach ($this->availableMethods as $method) {
                if ($method['price'] === (int) ($shippingValue * 100)) {
                    $this->selectedMethod = $method['id'];

                    return;
                }
            }
        }

        // Default to standard if no match found
        $this->selectedMethod = 'standard';
    }

    public function updatedSelectedMethod($value)
    {
        $this->updateShippingMethod($value);
    }

    public function updateShippingMethod($methodId)
    {
        $method = $this->findMethodById($methodId);

        if (! $method) {
            return;
        }

        // Convert from cents to dollars
        $priceInDollars = $method['price'] / 100;

        // Add new shipping condition with method ID as the shipping method
        // This will automatically remove any existing shipping conditions
        Cart::addShipping($method['name'], $priceInDollars, $methodId);

        // Emit event to update cart summary
        $this->dispatch('shipping-method-updated', [
            'method' => $methodId,
            'price' => $method['price'],
        ]);

        // Also dispatch cart-updated event to ensure cart summary refreshes
        $this->dispatch('cart-updated');
    }

    public function refreshShippingMethods()
    {
        $this->shippingRequired = $this->checkIfShippingRequired();
    }

    public function render()
    {
        return view('livewire.checkout.shipping-method-selector', [
            'methods' => $this->shippingRequired ? $this->availableMethods : [],
        ]);
    }

    // This method is no longer needed as Cart::addShipping now automatically removes existing shipping conditions
    protected function removeExistingShippingConditions()
    {
        Cart::removeShipping();
    }

    protected function findMethodById($id)
    {
        foreach ($this->availableMethods as $method) {
            if ($method['id'] === $id) {
                return $method;
            }
        }

        return null;
    }

    protected function checkIfShippingRequired()
    {
        $cartItems = Cart::content();
        foreach ($cartItems as $item) {
            $attributes = $item->attributes ?? [];
            // If any item requires shipping, shipping is required
            if (! ($attributes['is_digital'] ?? false)) {
                return true;
            }
        }

        return false;
    }
}
