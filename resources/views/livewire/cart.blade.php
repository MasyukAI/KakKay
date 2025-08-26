<?php

use App\Traits\ManagesCart;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Joelwmale\Cart\Facades\CartFacade as Cart;
use Illuminate\Support\Facades\Log;

new class extends Component {
    use ManagesCart;
    
    public array $cartItems = [];
    public string $voucherCode = '';

    public function mount(): void
    {
        $this->loadCartItems();
    }

    public function loadCartItems(): void
    {
        try {
            $this->setCartSession();
            
            $cartContents = Cart::getContent();
            
            if ($cartContents->isEmpty()) {
                $this->cartItems = [];
                return;
            }
            
            $this->cartItems = $cartContents->map(function ($item) {
                return [
                    'id' => (int) $item->id,
                    'name' => (string) $item->name,
                    'price' => (int) $item->price,
                    'quantity' => (int) $item->quantity,
                    'attributes' => $item->attributes ? $item->attributes->toArray() : [],
                    'image' => $item->attributes['image'] ?? 'https://flowbite.s3.amazonaws.com/blocks/e-commerce/book-placeholder.svg',
                ];
            })->values()->toArray();
        } catch (\Exception $e) {
            $this->cartItems = [];
            Log::error('Cart loading error: ' . $e->getMessage());
        }
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        $this->setCartSession();

        if ($quantity <= 0) {
            $this->removeItem($itemId);
            return;
        }

        Cart::update($itemId, [
            'quantity' => [
                'relative' => false,
                'value' => $quantity
            ]
        ]);

        $this->loadCartItems();
        $this->dispatch('product-added-to-cart');
    }

    public function removeItem(int $itemId): void
    {
        $this->setCartSession();
        Cart::remove($itemId);
        $this->loadCartItems();
        $this->dispatch('product-added-to-cart');
        
        session()->flash('success', 'Item removed from cart.');
    }

    public function applyVoucher(): void
    {
        if (!empty($this->voucherCode)) {
            session()->flash('success', "Voucher '{$this->voucherCode}' applied successfully!");
            $this->voucherCode = '';
        }
    }

    #[Computed]
    public function getSubtotal(): int
    {
        return $this->getCartSubtotal();
    }

    #[Computed]
    public function getSavings(): int
    {
        return 29900; // $299.00 in cents
    }

    #[Computed]
    public function getShipping(): int
    {
        return 9900; // $99.00 in cents
    }

    #[Computed]
    public function getTax(): int
    {
        return (int) ($this->getSubtotal() * 0.105); // 10.5% tax
    }

    #[Computed]
    public function getTotal(): int
    {
        return $this->getSubtotal() - $this->getSavings() + $this->getShipping() + $this->getTax();
    }

    public function formatPrice(int $cents): string
    {
        return '$' . number_format($cents / 100, 2);
    }
}; ?>

<div class="min-h-screen bg-white dark:bg-gray-900">
    <!-- Header Navigation -->
    <div class="container mx-auto px-4">
        <header class="py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center text-gray-900 dark:text-white">
                        <svg class="h-8 w-8 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                        <span class="text-xl font-bold">Kak Kay</span>
                    </a>
                </div>
                
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        Continue Shopping
                    </a>
                    
                    <div class="relative">
                        <a href="{{ route('cart') }}" 
                           class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5L21 18M7 13v6a2 2 0 002 2h6a2 2 0 002-2v-6m-8 0V9a2 2 0 012-2h4a2 2 0 012 2v4.01"></path>
                            </svg>
                        </a>
                        @livewire('cart-counter')
                    </div>
                </div>
            </div>
        </header>
    </div>

    <!-- Cart Content -->
    <section class="bg-white py-8 antialiased dark:bg-gray-900 md:py-16">
    <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">Shopping Cart</h2>

        @if(session('success'))
            <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(empty($cartItems))
            <div class="mt-6 text-center">
                <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Your cart is empty</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Start adding some products to your cart!</p>
                    <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        Continue Shopping
                        <svg class="ml-2 h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4" />
                        </svg>
                    </a>
                </div>
            </div>
        @else
            <div class="mt-6 sm:mt-8 md:gap-6 lg:flex lg:items-start xl:gap-8">
                <div class="mx-auto w-full flex-none lg:max-w-2xl xl:max-w-4xl">
                    <div class="space-y-6">
                        @foreach($cartItems as $index => $item)
                            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 md:p-6">
                                <div class="space-y-4 md:flex md:items-center md:justify-between md:gap-6 md:space-y-0">
                                    <a href="#" class="shrink-0 md:order-1">
                                        <img class="h-20 w-20" src="{{ $item['image'] }}" alt="{{ $item['name'] }}" />
                                    </a>

                                    <div class="flex items-center justify-between md:order-3 md:justify-end">
                                        <div class="flex items-center">
                                            <button 
                                                type="button" 
                                                wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                                class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                                                <svg class="h-2.5 w-2.5 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16" />
                                                </svg>
                                            </button>
                                            
                                            <input 
                                                type="text" 
                                                value="{{ $item['quantity'] }}" 
                                                wire:change="updateQuantity({{ $item['id'] }}, $event.target.value)"
                                                class="w-10 shrink-0 border-0 bg-transparent text-center text-sm font-medium text-gray-900 focus:outline-none focus:ring-0 dark:text-white" 
                                                min="1" />
                                            
                                            <button 
                                                type="button" 
                                                wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                                class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                                                <svg class="h-2.5 w-2.5 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="text-end md:order-4 md:w-32">
                                            <p class="text-base font-bold text-gray-900 dark:text-white">{{ $this->formatPrice($item['price'] * $item['quantity']) }}</p>
                                        </div>
                                    </div>

                                    <div class="w-full min-w-0 flex-1 space-y-4 md:order-2 md:max-w-md">
                                        <a href="#" class="text-base font-medium text-gray-900 hover:underline dark:text-white">{{ $item['name'] }}</a>

                                        <div class="flex items-center gap-4">
                                            <button type="button" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 hover:underline dark:text-gray-400 dark:hover:text-white">
                                                <svg class="me-1.5 h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.01 6.001C6.5 1 1 8 5.782 13.001L12.011 20l6.23-7C23 8 17.5 1 12.01 6.002Z" />
                                                </svg>
                                                Add to Favorites
                                            </button>

                                            <button 
                                                type="button" 
                                                wire:click="removeItem({{ $item['id'] }})"
                                                wire:confirm="Are you sure you want to remove this item from your cart?"
                                                class="inline-flex items-center text-sm font-medium text-red-600 hover:underline dark:text-red-500">
                                                <svg class="me-1.5 h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                                                </svg>
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mx-auto mt-6 max-w-4xl flex-1 space-y-6 lg:mt-0 lg:w-full">
                    <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                        <p class="text-xl font-semibold text-gray-900 dark:text-white">Order summary</p>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Subtotal</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $this->formatPrice($this->getSubtotal()) }}</dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Savings</dt>
                                    <dd class="text-base font-medium text-green-600">-{{ $this->formatPrice($this->getSavings()) }}</dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Shipping</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $this->formatPrice($this->getShipping()) }}</dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-base font-normal text-gray-500 dark:text-gray-400">Tax</dt>
                                    <dd class="text-base font-medium text-gray-900 dark:text-white">{{ $this->formatPrice($this->getTax()) }}</dd>
                                </dl>
                            </div>

                            <dl class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                                <dt class="text-base font-bold text-gray-900 dark:text-white">Total</dt>
                                <dd class="text-base font-bold text-gray-900 dark:text-white">{{ $this->formatPrice($this->getTotal()) }}</dd>
                            </dl>
                        </div>

                        <a href="{{ route('checkout') }}" class="flex w-full items-center justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Proceed to Checkout</a>

                        <div class="flex items-center justify-center gap-2">
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400"> or </span>
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-medium text-primary-700 underline hover:no-underline dark:text-primary-500">
                                Continue Shopping
                                <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                        <form wire:submit="applyVoucher" class="space-y-4">
                            <div>
                                <label for="voucher" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Do you have a voucher or gift card?</label>
                                <input 
                                    type="text" 
                                    id="voucher" 
                                    wire:model="voucherCode"
                                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500" 
                                    placeholder="Enter voucher code" />
                            </div>
                            <button type="submit" class="flex w-full items-center justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Apply Code</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
</div>
