@props([
    'cartQuantity' => null,
    'containerClass' => 'storefront-container pt-6 sm:pt-8',
    'actionLabel' => null,
    'actionHref' => null,
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

    $actionLabel ??= request()->routeIs('cart', 'checkout') ? 'Akaun Saya' : 'Ambil Ujian KKDI';
    $actionHref ??= request()->routeIs('cart', 'checkout')
        ? route('dashboard')
        : (\Illuminate\Support\Facades\Route::has('kkdi') ? route('kkdi') : '#');

    $navItems = [
        [
            'label' => 'Utama',
            'href' => route('home'),
            'active' => request()->routeIs('home'),
        ],
        [
            'label' => 'Ujian KKDI',
            'href' => \Illuminate\Support\Facades\Route::has('kkdi') ? route('kkdi') : '#',
            'active' => request()->routeIs('kkdi'),
        ],
        [
            'label' => 'Buku',
            'href' => \Illuminate\Support\Facades\Route::has('books') ? route('books') : '#',
            'active' => request()->routeIs('books', 'page.show', 'pages.cara-bercinta'),
        ],
        [
            'label' => 'Konsultasi',
            'href' => \Illuminate\Support\Facades\Route::has('consultation') ? route('consultation') : '#',
            'active' => request()->routeIs('consultation'),
        ],
        [
            'label' => 'Tentang',
            'href' => route('home').'#tentang',
            'active' => false,
        ],
        [
            'label' => 'Sumber',
            'href' => route('home').'#sumber',
            'active' => false,
        ],
    ];
@endphp

<div {{ $attributes->merge(['class' => $containerClass]) }}>
    <header class="storefront-card rounded-[2rem] px-5 py-4 sm:px-7 sm:py-5">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" wire:navigate.hover class="flex items-center gap-3 text-[var(--store-text)] no-underline">
                <div class="space-y-1">
                    <div class="font-brand text-3xl font-black tracking-[-0.04em] text-[var(--store-rose-dark)] sm:text-[2.15rem]">Kak Kay</div>
                    <div class="text-[0.7rem] uppercase tracking-[0.28em] text-[var(--store-text-soft)] sm:text-[0.72rem]">Counsellor • Penulis • Pencipta KKDI</div>
                </div>
            </a>

            <nav class="hidden items-center gap-7 lg:flex">
                @foreach ($navItems as $item)
                    <a
                        href="{{ $item['href'] }}"
                        wire:navigate.hover
                        class="storefront-nav-link text-sm font-medium {{ $item['active'] ? 'is-active' : '' }}"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2 sm:gap-3">
                @if (isset($slot) && method_exists($slot, 'isEmpty') && ! $slot->isEmpty())
                    <div class="hidden xl:block">
                        {{ $slot }}
                    </div>
                @endif

                <button type="button" class="storefront-icon-button hidden sm:inline-flex" aria-label="Cari">
                    <flux:icon.magnifying-glass class="h-5 w-5" />
                </button>

                <a href="{{ route('cart') }}" wire:navigate.hover class="storefront-icon-button relative" aria-label="Troli">
                    <flux:icon.shopping-bag class="h-5 w-5" />
                    @if ($quantity > 0)
                        <span class="absolute -right-1.5 -top-1.5 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-[var(--store-rose)] px-1 text-[0.65rem] font-semibold text-white">
                            {{ $quantity }}
                        </span>
                    @endif
                </a>

                <a href="{{ $actionHref }}" wire:navigate.hover class="storefront-button-primary hidden sm:inline-flex">
                    {{ $actionLabel }}
                </a>
            </div>
        </div>

        <nav class="mt-4 flex gap-2 overflow-x-auto pb-1 lg:hidden">
            @foreach ($navItems as $item)
                <a
                    href="{{ $item['href'] }}"
                    wire:navigate.hover
                    class="whitespace-nowrap rounded-full border px-4 py-2 text-sm font-medium no-underline transition {{ $item['active'] ? 'border-[color:var(--store-border-strong)] bg-[#fff1ea] text-[var(--store-rose)]' : 'border-[color:var(--store-border)] bg-white/70 text-[var(--store-text-soft)]' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
            <a href="{{ $actionHref }}" wire:navigate.hover class="storefront-button-primary whitespace-nowrap sm:hidden">
                {{ $actionLabel }}
            </a>
        </nav>
        @if (isset($slot) && method_exists($slot, 'isEmpty') && ! $slot->isEmpty())
            <div class="mt-4 xl:hidden">
                {{ $slot }}
            </div>
        @endif
    </header>
 </div>
