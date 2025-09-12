<?php

use App\Models\Product;
use Livewire\Volt\Component;
use MasyukAI\Cart\Facades\Cart;

new class extends Component {
    
    public Product $product;
    public int $quantity = 1;

    public function addToCart(): void
    {
        // Middleware handles cart instance switching
        Cart::add(
            (string) $this->product->id,
            $this->product->name,
            $this->product->price / 100, // Convert from cents to major units for cart
            $this->quantity,
            [
                'imageUrl' => $this->product->getMedia('product-image-main')->first()?->getUrl() ?? 'https://flowbite.s3.amazonaws.com/blocks/e-commerce/book-placeholder.svg',
                'description' => $this->product->description,
            ]
        );
        
        $this->dispatch('product-added-to-cart');
        
        session()->flash('success', "'{$this->product->name}' has been added to your cart!");
    }

    public function incrementQuantity(): void
    {
        $this->quantity++;
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }
}; ?>

<div>
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center gap-4">
        <div class="flex items-center border border-gray-300 rounded-lg">
            <button 
                type="button" 
                wire:click="decrementQuantity"
                class="p-2 hover:bg-gray-100 text-gray-600 rounded-l-lg">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </button>
            
            <span class="px-4 py-2 text-gray-900 min-w-[3rem] text-center">{{ $quantity }}</span>
            
            <button 
                type="button" 
                wire:click="incrementQuantity"
                class="p-2 hover:bg-gray-100 text-gray-600 rounded-r-lg">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
        </div>

        <button 
            type="button" 
            wire:click="addToCart"
            class="btn primary flex items-center gap-2 px-6 py-2 text-white bg-gradient-to-r from-pink-500 to-purple-600 rounded-lg hover:from-pink-600 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5L21 18M7 13v6a2 2 0 002 2h6a2 2 0 002-2v-6m-8 0V9a2 2 0 012-2h4a2 2 0 012 2v4.01"></path>
            </svg>
            Add to Cart
        </button>
    </div>
</div>
