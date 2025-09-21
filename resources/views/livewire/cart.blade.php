<div class="relative min-h-screen overflow-hidden bg-[#0f0218] text-white">
    <div class="pointer-events-none absolute -top-48 -left-40 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-pink-500/35 via-purple-500/25 to-rose-500/40 blur-3xl"></div>

    <div class="pointer-events-none absolute top-1/4 -right-36 h-[540px] w-[540px] rounded-full bg-gradient-to-br from-fuchsia-500/25 via-rose-500/25 to-orange-400/35 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-64 left-1/2 h-[520px] w-[520px] -translate-x-1/2 rounded-full bg-gradient-to-br from-purple-600/30 via-indigo-500/20 to-pink-400/30 blur-3xl"></div>

    <div class="relative z-10">
        <x-brand-header>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.32em] text-white/70 transition hover:border-white/50 hover:text-white">
                <flux:icon.arrow-left class="h-4 w-4" />
                <span class="hidden md:inline">Sambung pilih buku</span>
            </a>
        </x-brand-header>

        <main class="space-y-12 pb-24 sm:space-y-16">
            <section class="pt-20">
                <div class="mx-auto max-w-7xl px-6 sm:px-8">
                    <div class="relative mx-auto max-w-3xl">
                        <div class="absolute left-6 right-6 top-6 hidden h-px bg-white/15 sm:block"></div>
                        <ol class="relative flex items-center justify-between gap-6 text-xs font-semibold uppercase tracking-[0.28em] text-white/60">
                            <li class="flex flex-col items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full border border-white/40 bg-gradient-to-br from-pink-500 via-rose-500 to-purple-500 text-white shadow-[0_16px_45px_rgba(236,72,153,0.45)]">
                                    <flux:icon.shopping-bag class="h-5 w-5" />
                                </div>
                                <span class="text-white">Troli</span>
                            </li>
                            <li class="flex flex-col items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full border border-white/20 bg-white/5 text-white/60">
                                    <flux:icon.credit-card class="h-5 w-5" />
                                </div>
                                <span>Bayaran</span>
                            </li>
                            <li class="flex flex-col items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full border border-white/20 bg-white/5 text-white/60">
                                    <flux:icon.sparkles class="h-5 w-5" />
                                </div>
                                <span>Pesanan</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </section>
            @if(empty($cartItems))
                <section class="pt-10">
                    <div class="mx-auto max-w-5xl px-6 sm:px-8">
                        <div class="rounded-[34px] border border-white/10 bg-white/5 px-6 py-12 text-center backdrop-blur-xl shadow-[0_35px_110px_rgba(15,3,37,0.45)] sm:px-12">
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-5 py-2 text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Ritual cinta paling chill</span>
                            <h1 class="mt-6 font-display text-4xl leading-tight sm:text-5xl">Troli awak masih sunyi 💫</h1>
                            <p class="mt-4 text-base leading-relaxed text-white/80 sm:text-lg">Belum terlambat nak cari buku manja. Pilih satu yang buat hati senyum, cuba ritual malam ini, dan tengok macam mana vibe rumah terus hangat.</p>
                            <div class="mt-8 flex flex-wrap justify-center gap-4">
                                <flux:button variant="primary" href="{{ route('home') }}" class="cart-button-primary px-8 py-4 text-lg">
                                    <flux:icon.sparkles class="h-5 w-5" />
                                    Jom pilih buku fun
                                </flux:button>
                                <a href="#recommended" class="rounded-full border border-white/25 px-8 py-3 text-sm font-semibold uppercase tracking-[0.28em] text-white/70 transition hover:border-white/60 hover:text-white">
                                    Lihat cadangan Kak Kay
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            @else
                <section>
                    <div class="mx-auto max-w-7xl px-6 sm:px-8">
                        <div class="lg:grid lg:grid-cols-[minmax(0,1fr)_360px] xl:grid-cols-[minmax(0,1fr)_420px] lg:gap-12">
                            <div class="space-y-6">
                                @foreach($cartItems as $item)
                                    <div wire:key="cart-item-{{ $item['id'] }}" class="cart-item-card relative overflow-hidden rounded-[32px] border border-white/15 bg-white/5 p-6 shadow-[0_25px_70px_rgba(12,5,24,0.4)] sm:p-7">
                                        <div class="flex flex-col gap-6 sm:flex-row">
                                            <div class="relative w-full sm:w-36 sm:flex-shrink-0">
                                                <div class="absolute -inset-5 rounded-[36px] bg-gradient-to-br from-yellow-300/40 via-amber-400/30 to-pink-400/20 opacity-80 blur-2xl"></div>
                                                <div class="relative rounded-[28px] p-[3px] bg-gradient-to-br from-yellow-400 via-amber-400 to-pink-400 shadow-[0_0_32px_4px_rgba(255,215,0,0.18)]">
                                                    <div class="relative rounded-[25px] bg-[#14021f]/60 p-[3px] shadow-[0_8px_32px_rgba(255,215,0,0.10)]">
                                                        <div class="overflow-hidden rounded-[22px] border-2 border-yellow-300/80 bg-[#14021f]/40 shadow-[0_12px_45px_rgba(255,215,0,0.10)]">
                                                            <img src="{{ asset('storage/images/cover/' . $item['slug'] . '.png') }}" alt="{{ $item['name'] }}" class="block h-full w-full object-cover">
                                                        </div>
                                                    </div>
                                                    <div class="pointer-events-none absolute inset-0 rounded-[28px] ring-2 ring-yellow-200/30 ring-offset-2 ring-offset-yellow-100/10"></div>
                                                </div>
                                            </div>
                                            <div class="relative flex flex-1 flex-col justify-between gap-6">
                                                <div class="space-y-4">
                                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                        <a href="/{{ $item['slug'] }}" target="_blank" rel="noopener" class="text-xl font-semibold text-white transition hover:text-pink-200">
                                                            {{ $item['name'] }}
                                                        </a>
                                                        <span class="text-sm font-semibold text-white/80">Seunit {{ $item['price_formatted'] ?? \Akaunting\Money\Money::MYR($item['price'])->format() }}</span>
                                                    </div>
                                                    <p class="text-sm leading-relaxed text-white/70">
                                                        Modul Kak Kay ni siap dengan skrip mesra dan ritual harian. Selak satu bab, terus boleh praktik malam ini.
                                                    </p>
                                                </div>
                                                <div class="flex flex-wrap items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <flux:button variant="subtle" size="sm"
                                                            wire:click="decrementQuantity('{{ $item['id'] }}')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="decrementQuantity('{{ $item['id'] }}')"
                                                            class="cursor-pointer flex h-11 w-11 items-center justify-center rounded-xl border border-white/15 bg-white/10 transition hover:bg-white/20 disabled:opacity-50">
                                                            <flux:icon.minus class="h-4 w-4" wire:loading.remove wire:target="decrementQuantity('{{ $item['id'] }}')"/>
                                                            <svg wire:loading wire:target="decrementQuantity('{{ $item['id'] }}')" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </flux:button>

                                                        <span class="min-w-[3rem] rounded-xl border border-white/15 bg-white/15 px-4 py-2 text-center text-lg font-semibold text-white">
                                                            {{ $item['quantity'] }}
                                                        </span>

                                                        <flux:button variant="subtle" size="sm"
                                                            wire:click="incrementQuantity('{{ $item['id'] }}')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="incrementQuantity('{{ $item['id'] }}')"
                                                            class="cursor-pointer flex h-11 w-11 items-center justify-center rounded-xl border border-white/15 bg-white/10 transition hover:bg-white/20 disabled:opacity-50">
                                                            <flux:icon.plus class="h-4 w-4" wire:loading.remove wire:target="incrementQuantity('{{ $item['id'] }}')"/>
                                                            <svg wire:loading wire:target="incrementQuantity('{{ $item['id'] }}')" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </flux:button>
                                                    </div>

                                                    <div class="flex flex-col text-right">
                                                        <span class="text-xs uppercase tracking-[0.28em] text-white/60">Jumlah kecil</span>
                                                        <span class="text-lg font-semibold text-white">{{ $item['subtotal_formatted'] ?? \Akaunting\Money\Money::MYR($item['subtotal'])->format() }}</span>
                                                    </div>

                                                    <flux:button variant="subtle" color="red"
                                                        wire:click="removeItem('{{ $item['id'] }}')"
                                                        class="cursor-pointer rounded-xl border border-red-400/60 px-5 py-2.5 font-semibold text-red-300 transition duration-200 hover:bg-red-500/30 hover:text-white">
                                                        <flux:icon.trash class="h-5 w-5" />
                                                        {{-- <span class="ml-2 hidden text-sm sm:inline">Buang</span> --}}
                                                    </flux:button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <aside class="mt-10 lg:mt-0">
                                <div class="cart-summary-card sticky p-6 sm:p-8">
                                    <div class="absolute -top-20 right-0 h-48 w-48 rounded-full bg-gradient-to-br from-pink-400/30 via-purple-400/20 to-orange-300/30 blur-3xl"></div>
                                    <div class="relative space-y-6">
                                        <div>
                                            <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1 text-[0.7rem] font-semibold uppercase tracking-[0.32em] text-white/70">
                                                Langkah seterusnya
                                            </span>
                                            <h3 class="mt-4 font-display text-3xl text-white">Ringkasan pesanan</h3>
                                            <p class="mt-2 text-sm leading-relaxed text-white/70">Semak jumlah dan klik terus untuk secure ritual pilihan awak. Penghantaran disusun rapi — tinggal tunggu stok sampai.</p>
                                        </div>

                                        <div class="space-y-4 text-sm text-white/80">
                                            <div class="flex justify-between">
                                                <span>Subtotal</span>
                                                <span class="font-semibold text-white">{{ $this->getSubtotal()->format() }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Penghantaran standard</span>
                                                <span class="font-semibold text-white">{{ $this->getShipping()->format() }}</span>
                                            </div>
                                            <hr class="border-white/20">
                                            <div class="flex justify-between text-xl font-bold text-white">
                                                <span>Jumlah</span>
                                                <span class="cart-text-accent">{{ $this->getTotal()->format() }}</span>
                                            </div>
                                        </div>

                                        <flux:button variant="primary" href="{{ route('checkout') }}"
                                            class="cart-button-primary flex w-full items-center justify-center gap-2 px-6 py-4 text-lg font-semibold">
                                            <flux:icon.credit-card class="h-5 w-5" />
                                            Terus ke bayaran
                                        </flux:button>
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>
                </section>

            @endif
        </main>

        @if($suggestedProducts && $suggestedProducts->count() > 0)
            <section id="recommended" class="mt-8 pb-20">
                <div class="mx-auto max-w-7xl px-6 sm:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h3 class="font-display text-3xl text-white sm:text-4xl">Yang lain ni orang beli juga</h3>
                        <p class="mt-3 text-lg leading-relaxed text-white/80">Tambah satu lagi judul untuk lebih lengkap. Semua ni antara pilihan feveret geng KKDI.</p>
                    </div>
                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($suggestedProducts as $product)
                            <div wire:key="suggested-product-{{ $product->id }}" class="group relative flex h-full flex-col overflow-hidden rounded-[30px] border border-white/15 bg-white/5 p-5 text-left text-white/80 shadow-[0_25px_70px_rgba(12,5,24,0.4)] transition duration-300 hover:-translate-y-2 hover:shadow-[0_35px_90px_rgba(236,72,153,0.3)]">
                                <a href="/{{ $product->slug }}" class="block">
                                    <div class="relative overflow-hidden rounded-[22px]">
                                        <img src="{{ asset('storage/images/cover/' . $product->slug . '.png') }}" alt="{{ $product->name }}" class="w-full rounded-[22px] border border-white/20 object-cover shadow-[0_20px_60px_rgba(17,0,34,0.45)]">
                                        <div class="absolute inset-0 bg-gradient-to-t from-[#0f0218]/80 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></div>
                                        <div class="absolute bottom-4 left-4 right-4 flex justify-end text-xs uppercase tracking-[0.28em] text-white/80">
                                            {{-- <span>Buku Kak Kay</span> --}}
                                            <span>{{ \Akaunting\Money\Money::MYR($product->price)->format() }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-5 space-y-3">
                                        <h4 class="text-xl font-semibold text-white">{{ $product->name }}</h4>
                                        <p class="line-clamp-3 text-sm text-white/70">{{ $product->description }}</p>
                                        <div class="inline-flex items-center gap-2 text-sm font-semibold text-pink-200">
                                            Selak detail
                                            <flux:icon.arrow-right class="h-4 w-4" />
                                        </div>
                                    </div>
                                </a>
                                <button type="button" wire:click="addToCart({{ $product->id }})" wire:loading.attr="disabled" wire:target="addToCart({{ $product->id }})" class="group mt-5 flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 px-6 py-3 text-sm font-semibold text-white shadow-[0_22px_52px_rgba(236,72,153,0.45)] ring-1 ring-white/20 transition-all duration-300 ease-out hover:scale-[1.02] hover:shadow-[0_28px_64px_rgba(236,72,153,0.6)] hover:ring-2 hover:ring-pink-300/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0f0218] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100">
                                    <span wire:loading.remove wire:target="addToCart({{ $product->id }})" class="flex items-center gap-2 transition-all duration-200">
                                        <flux:icon.shopping-cart class="h-4 w-4 transition-transform duration-200 group-hover:scale-110" />
                                        <span class="tracking-wide">Tambah</span>
                                    </span>
                                    <span wire:loading wire:target="addToCart({{ $product->id }})" class="flex items-center justify-center gap-2">
                                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <div class="container">
            <x-footer />
        </div>

        <div style="background: rgba(0,0,0,0.25); border-top: 1px solid rgba(255,255,255,0.1); padding: 0.6rem 1rem; font-size: 0.8rem; color: rgba(255,255,255,0.7); text-align: center; margin-top: 1rem;">
            <strong style="color: #ff69b4;">DEBUG:</strong>
            @auth
                <span style="color: #4ade80;">✓ Authenticated User</span> -
                <span>{{ auth()->user()->name ?? 'No Name' }}</span>
                (<span>{{ auth()->user()->email ?? 'No Email' }}</span>)
            @else
                <span style="color: #f87171;">✗ Guest User</span> - Not logged in
            @endauth
            | Role: {{ auth()->user()->role ?? 'guest' }}
        </div>
    </div>
</div>
