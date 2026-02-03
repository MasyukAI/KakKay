@props([
    'cartQuantity' => null,
    'containerClass' => 'mx-auto max-w-7xl px-6 pt-10 sm:px-8',
    'headerClass' => 'flex items-center justify-between gap-4',
])

@php
    $quantity = $cartQuantity;
    if ($quantity === null && class_exists('AIArmada\\Cart\\Facades\\Cart')) {
        try {
            $quantity = AIArmada\Cart\Facades\Cart::getTotalQuantity();
        } catch (\Throwable $e) {
            $quantity = 0;
        }
    }
    $quantity ??= 0;
    $hasCartItems = $quantity > 0;
@endphp

<div {{ $attributes->merge(['class' => $containerClass]) }}>
    <header class="brand-header {{ $headerClass }}">
        <a href="/" wire:navigate class="brand flex items-center gap-4">
            <div class="space-y-0.5">
                <h1 class="text-xl tracking-tight" style="font-family:'Montserrat',system-ui,sans-serif;font-weight:900;letter-spacing:.3px;">Kak Kay</h1>
                <div class="tagline text-xs sm:text-base" style="font-family:'Poppins','Montserrat',system-ui,sans-serif;opacity:.9;">Counsellor • Therapist • KKDI Creator</div>
            </div>
        </a>

        <div class="flex items-center gap-4">
            @if(isset($slot) && method_exists($slot, 'isEmpty') && ! $slot->isEmpty())
                {{ $slot }}
            @else
                <flux:button href="{{ route('cart') }}" wire:navigate
                    class="group relative flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition-all duration-300 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0f0218]
                        {{ $hasCartItems ? 'bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 text-white shadow-[0_16px_36px_rgba(236,72,153,0.45)] ring-1 ring-white/20' : 'border border-white/20 bg-white/10 text-white/80 shadow-[0_10px_24px_rgba(12,5,24,0.35)] hover:border-white/40 hover:text-white' }}">
                    <span class="pointer-events-none absolute inset-0 rounded-full opacity-0 transition duration-300 group-hover:opacity-50 {{ $hasCartItems ? 'bg-white/20' : 'bg-gradient-to-r from-pink-400/20 via-rose-400/10 to-purple-500/20' }}"></span>
                    <span class="pointer-events-none absolute -inset-1 rounded-full blur-lg opacity-0 transition duration-300 group-hover:opacity-60 {{ $hasCartItems ? 'bg-pink-500/30' : 'bg-white/15' }}"></span>
                    <flux:icon.shopping-bag class="relative z-10 h-5 w-5 {{ $hasCartItems ? 'text-white' : 'text-white/80' }}" />
                    <span class="relative z-10 hidden sm:inline text-white">Troli</span>
                    <span class="absolute -top-2 -right-2 z-20">
                        <livewire:cart-counter defer />
                    </span>
                </flux:button>
            @endif
        </div>
    </header>
</div>
