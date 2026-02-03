<div class="relative min-h-screen overflow-hidden bg-[#0f0218] text-white">
    <div class="pointer-events-none absolute -top-48 -left-40 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-pink-500/35 via-purple-500/25 to-rose-500/40 blur-2xl"></div>

    <div class="pointer-events-none absolute top-1/4 -right-36 h-[540px] w-[540px] rounded-full bg-gradient-to-br from-fuchsia-500/25 via-rose-500/25 to-orange-400/35 blur-2xl"></div>
    <div class="pointer-events-none absolute -bottom-64 left-1/2 h-[520px] w-[520px] -translate-x-1/2 rounded-full bg-gradient-to-br from-purple-600/30 via-indigo-500/20 to-pink-400/30 blur-2xl"></div>

    <div class="relative z-10">
        <x-brand-header>
            <a href="{{ route('home') }}" wire:navigate.hover class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.32em] text-white/70 transition hover:border-white/50 hover:text-white">
                <flux:icon.arrow-left class="h-4 w-4" />
                <span class="hidden md:inline">Sambung pilih buku</span>
            </a>
        </x-brand-header>

        <main class="space-y-12 pb-24 sm:space-y-16">
            <section class="pt-19.5">
                <div class="mx-auto max-w-7xl px-6 sm:px-8">
                    <div class="relative mx-auto max-w-3xl">
                        <div class="absolute left-6 right-6 top-6 block h-px bg-white/15"></div>
                        <ol class="relative flex items-center justify-between gap-6 text-xs font-semibold uppercase tracking-[0.28em] text-white/60">
                            <li class="flex flex-col items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full border border-white/40 bg-gradient-to-br from-pink-500 via-rose-500 to-purple-500 text-white shadow-[0_10px_28px_rgba(236,72,153,0.45)]">
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
                        <div class="rounded-[34px] border border-white/10 bg-white/5 px-6 py-12 text-center backdrop-blur-sm shadow-[0_14px_32px_rgba(15,3,37,0.45)] sm:px-12">
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-5 py-2 text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Zon cinta paling chill</span>
                            <h1 class="mt-6 font-display text-4xl leading-tight sm:text-5xl">Troli awak masih sunyi 💫</h1>
                            <p class="mt-4 text-base leading-relaxed text-white/80 sm:text-lg">Belum terlambat nak cari buku manja. Pilih satu yang buat hati senyum, cuba aktiviti malam ini, dan tengok macam mana vibe rumah terus hangat.</p>
                            <div class="mt-8 flex flex-wrap justify-center gap-4">
                                <flux:button variant="primary" href="{{ route('home') }}" wire:navigate.hover class="cart-button-primary flex items-center justify-center gap-2 px-8 py-4 text-lg">
                                    <flux:icon.sparkles class="h-5 w-5" />
                                    Jom pilih buku
                                </flux:button>
                                {{-- <a href="#recommended" class="rounded-full border border-white/25 px-8 py-3 text-sm font-semibold uppercase tracking-[0.28em] text-white/70 transition hover:border-white/60 hover:text-white">
                                    Lihat cadangan Kak Kay
                                </a> --}}
                            </div>
                        </div>
                    </div>
                </section>
            @else
                <section>
                    <div class="mx-auto max-w-7xl px-6 sm:px-8">
                        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px] xl:grid-cols-[minmax(0,1fr)_400px]">
                            <div class="min-w-0 space-y-6">
                                @foreach($cartItems as $item)
                                    <div wire:key="cart-item-{{ $item['id'] }}" class="cart-item-card relative overflow-hidden rounded-[32px] border border-white/15 bg-white/5 p-6 shadow-[0_16px_40px_rgba(12,5,24,0.4)] sm:p-7">
                                        <div class="flex flex-col gap-6 sm:flex-row">
                                            <div class="relative w-full sm:w-36 sm:flex-shrink-0">
                                                <div class="rounded-[20px] border border-white/20 bg-[#14021f]/60 shadow-md">
                                                    <img src="{{ asset('storage/images/cover/' . $item['slug'] . '.webp') }}" alt="{{ $item['name'] }}" class="block h-full w-full object-cover rounded-[18px]">
                                                </div>
                                            </div>
                                            <div class="relative flex flex-1 flex-col justify-between gap-6">
                                                <div class="space-y-4">
                                                    <div class="flex flex-col gap-2">
                                                        <a href="/{{ $item['slug'] }}" target="_blank" rel="noopener" class="text-xl font-semibold text-white transition hover:text-pink-200">
                                                            {{ $item['name'] }}
                                                        </a>
                                                        <div class="flex items-center justify-between gap-4">
                                                            <div class="text-sm text-white/80">by Kak Kay</div>
                                                            <div class="flex items-center gap-2 text-sm">
                                                                <span class="text-white/70">Harga seunit:</span>
                                                                <span class="font-semibold text-white">{{ $item['price'] }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="text-xs text-green-400">In Stock</div>
                                                    </div>
                                                    {{-- <p class="text-sm leading-relaxed text-white/70">
                                                        Modul Kak Kay ni siap dengan skrip mesra dan ritual harian. Selak satu bab, terus boleh praktik malam ini.
                                                    </p> --}}
                                                </div>
                                                <div class="flex flex-wrap items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <flux:button variant="subtle" size="sm"
                                                            wire:click="decrementQuantity('{{ $item['id'] }}')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="decrementQuantity('{{ $item['id'] }}')"
                                                            class="group flex h-11 w-11 items-center justify-center rounded-xl border-2 border-pink-400/40 bg-gradient-to-br from-pink-500/20 via-rose-500/15 to-purple-500/20 text-white shadow-[0_2px_12px_rgba(236,72,153,0.25)] transition duration-300 hover:-translate-y-0.5 hover:scale-[1.02] hover:border-pink-300/70 hover:from-pink-500/40 hover:via-rose-500/30 hover:to-purple-500/35 hover:text-white hover:shadow-[0_4px_14px_rgba(236,72,153,0.35)] disabled:cursor-not-allowed disabled:opacity-50">
                                                            <flux:icon.minus class="h-4 w-4 text-white transition-colors duration-300 group-hover:text-white" wire:loading.remove wire:target="decrementQuantity('{{ $item['id'] }}')"/>
                                                            <svg wire:loading wire:target="decrementQuantity('{{ $item['id'] }}')" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </flux:button>

                                                        <span class="min-w-[3rem] rounded-xl border-2 border-white/40 bg-gradient-to-br from-white/25 via-pink-500/10 to-white/10 px-4 py-2 text-center text-lg font-semibold text-white shadow-[0_2px_10px_rgba(236,72,153,0.2)]">
                                                            {{ $item['quantity'] }}
                                                        </span>

                                                        <flux:button variant="subtle" size="sm"
                                                            wire:click="incrementQuantity('{{ $item['id'] }}')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="incrementQuantity('{{ $item['id'] }}')"
                                                            class="group flex h-11 w-11 items-center justify-center rounded-xl border-2 border-pink-400/40 bg-gradient-to-br from-pink-500/20 via-rose-500/15 to-purple-500/20 text-white shadow-[0_2px_12px_rgba(236,72,153,0.25)] transition duration-300 hover:-translate-y-0.5 hover:scale-[1.02] hover:border-pink-300/70 hover:from-pink-500/40 hover:via-rose-500/30 hover:to-purple-500/35 hover:text-white hover:shadow-[0_4px_14px_rgba(236,72,153,0.35)] disabled:cursor-not-allowed disabled:opacity-50">
                                                            <flux:icon.plus class="h-4 w-4 text-white transition-colors duration-300 group-hover:text-white" wire:loading.remove wire:target="incrementQuantity('{{ $item['id'] }}')"/>
                                                            <svg wire:loading wire:target="incrementQuantity('{{ $item['id'] }}')" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </flux:button>
                                                    </div>
                                                    <flux:button variant="subtle" color="red"
                                                        wire:click="removeItem('{{ $item['id'] }}')"
                                                        class="group rounded-xl border-2 border-red-400/60 bg-gradient-to-br from-red-500/20 via-rose-500/15 to-orange-500/20 px-5 py-2.5 font-semibold text-red-200 shadow-[0_2px_12px_rgba(248,113,113,0.25)] transition duration-300 hover:-translate-y-0.5 hover:scale-[1.02] hover:border-red-300/70 hover:from-red-500/40 hover:via-rose-500/30 hover:to-orange-500/35 hover:text-white hover:shadow-[0_4px_14px_rgba(248,113,113,0.35)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300/60">
                                                        <flux:icon.trash class="h-5 w-5 text-white transition-colors duration-300 group-hover:text-white" />
                                                        {{-- <span class="ml-2 hidden text-sm sm:inline">Buang</span> --}}
                                                    </flux:button>
                                                </div>
                                        
                                            </div>
                                        </div>
                                        <hr class="my-6 border-white/15">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-baseline gap-2 text-white/70">
                                                <span class="text-sm">{{ $item['price'] }}</span>
                                                <flux:icon.x-mark class="h-3 w-3" />
                                                <span class="text-sm font-semibold">{{ $item['quantity'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs uppercase tracking-wider text-white/60">Jumlah Item</span>
                                                <span class="text-lg font-bold text-white bg-gradient-to-r from-pink-400 via-rose-400 to-purple-400 bg-clip-text text-transparent">{{ $item['subtotal'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Removed extra divider between cards --}}
                                @endforeach
                            </div>

                            <aside class="mt-10 lg:mt-0 lg:w-[360px] xl:w-[400px]">
                                <div class="cart-summary-card sticky p-6 sm:p-6.5">
                                    {{-- <div class="absolute -top-20 right-0 h-48 w-48 rounded-full bg-gradient-to-br from-pink-400/30 via-purple-400/20 to-orange-300/30 blur-2xl"></div> --}}
                                    <div class="relative space-y-6">
                                        <div>
                                            {{-- <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1 text-[0.7rem] font-semibold uppercase tracking-[0.32em] text-white/70">
                                                Langkah seterusnya
                                            </span> --}}
                                            <h3 class="font-display text-3xl text-white">Ringkasan Pesanan</h3>
                                            {{-- <p class="mt-2 text-sm leading-relaxed text-white/70">Semak jumlah dan klik terus untuk secure ritual pilihan awak. Penghantaran disusun rapi — tinggal tunggu stok sampai.</p> --}}
                                        </div>

                                        {{-- Voucher Section --}}
                                        <div class="space-y-3">
                                            <h4 class="text-sm font-semibold text-white/80 flex items-center gap-2">
                                                <flux:icon.ticket class="h-4 w-4 text-pink-400" />
                                                Kod Voucher
                                            </h4>

                                            @if($appliedVoucher)
                                                {{-- Applied Voucher Display --}}
                                                <div class="flex items-center justify-between rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3">
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-500/20">
                                                            <flux:icon.check-circle class="h-5 w-5 text-green-400" />
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-semibold text-green-300">{{ $appliedVoucher['code'] }}</p>
                                                            <p class="text-xs text-green-400/70">{{ $appliedVoucher['description'] ?? 'Diskaun digunakan' }}</p>
                                                        </div>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        wire:click="removeVoucher"
                                                        wire:loading.attr="disabled"
                                                        class="rounded-lg p-1.5 text-green-400/70 transition hover:bg-red-500/20 hover:text-red-400"
                                                    >
                                                        <flux:icon.x-mark class="h-4 w-4" />
                                                    </button>
                                                </div>
                                            @else
                                                {{-- Voucher Input Form --}}
                                                <div class="space-y-2">
                                                    <div class="flex gap-2">
                                                        <input
                                                            type="text"
                                                            wire:model="voucherCode"
                                                            wire:keydown.enter="applyVoucher"
                                                            placeholder="Masukkan kod voucher"
                                                            class="flex-1 rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-sm text-white placeholder-white/40 transition focus:border-pink-400/50 focus:bg-white/10 focus:outline-none focus:ring-2 focus:ring-pink-400/20"
                                                        />
                                                        <button
                                                            type="button"
                                                            wire:click="applyVoucher"
                                                            wire:loading.attr="disabled"
                                                            wire:target="applyVoucher"
                                                            class="rounded-xl border border-pink-400/40 bg-pink-500/20 px-4 py-3 text-sm font-semibold text-pink-300 transition hover:bg-pink-500/30 hover:text-pink-200 disabled:cursor-not-allowed disabled:opacity-50"
                                                        >
                                                            <span wire:loading.remove wire:target="applyVoucher">Guna</span>
                                                            <svg wire:loading wire:target="applyVoucher" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    @if($voucherError)
                                                        <p class="text-xs text-red-400">{{ $voucherError }}</p>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <hr class="border-white/10">

                                        <div class="space-y-4 text-sm text-white/80">
                                            <div class="flex justify-between">
                                                <span>Jumlah Harga</span>
                                                <span class="font-semibold text-white">{{ $this->getSubtotal()->format() }}</span>
                                            </div>
                                            @if($this->getSavings()->getAmount() > 0)
                                                <div class="flex justify-between text-green-400">
                                                    <span class="flex items-center gap-1.5">
                                                        <flux:icon.tag class="h-4 w-4" />
                                                        Jimat
                                                    </span>
                                                    <span class="font-semibold">-{{ $this->getSavings()->format() }}</span>
                                                </div>
                                            @endif
                                            <div class="flex justify-between">
                                                <span>Penghantaran</span>
                                                <span class="font-semibold text-white">{{ $this->getShipping()->format() }}</span>
                                            </div>
                                            <hr class="border-white/20">
                                            <div class="flex justify-between text-xl font-bold text-white">
                                                <span>Jumlah</span>
                                                <span class="cart-text-accent">{{ $this->getTotal()->format() }}</span>
                                            </div>
                                        </div>

                                        <flux:button
                                            x-ref="checkoutBtn"
                                            variant="primary"
                                            href="{{ route('checkout') }}" wire:navigate.hover
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

        @island(lazy: true)
            @placeholder
                <section id="recommended" class="mt-8 pb-20">
                    <div class="mx-auto max-w-7xl px-6 sm:px-8">
                        <div class="mx-auto max-w-3xl text-center">
                            <h3 class="font-display text-3xl text-white sm:text-4xl">Orang lain beli ni juga</h3>
                            <p class="mt-3 text-lg leading-relaxed text-white/70">Memuat cadangan untuk anda…</p>
                        </div>
                        <div class="mt-10 flex flex-wrap justify-center gap-6">
                            @foreach(range(1, 3) as $skeleton)
                                <div class="h-[360px] w-full max-w-sm animate-pulse rounded-[30px] border border-white/10 bg-white/5 p-5 sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]">
                                    <div class="h-44 w-full rounded-[22px] bg-white/10"></div>
                                    <div class="mt-5 space-y-3">
                                        <div class="h-5 w-3/4 rounded-full bg-white/10"></div>
                                        <div class="h-4 w-full rounded-full bg-white/10"></div>
                                        <div class="h-4 w-5/6 rounded-full bg-white/10"></div>
                                        <div class="h-9 w-32 rounded-full bg-white/10"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endplaceholder

            <section id="recommended" class="mt-8 pb-20">
                @if($this->suggestedProducts && $this->suggestedProducts->count() > 0)
                    <div class="mx-auto max-w-7xl px-6 sm:px-8">
                        <div class="mx-auto max-w-3xl text-center">
                            <h3 class="font-display text-3xl text-white sm:text-4xl">Orang lain beli ni juga</h3>
                            <p class="mt-3 text-lg leading-relaxed text-white/80">Tambah satu lagi judul untuk lebih lengkap. Semua ni antara pilihan feveret geng Kak Kay.</p>
                        </div>
                        <div class="mt-10 flex flex-wrap justify-center gap-6">
                            @foreach($this->suggestedProducts as $product)
                                <div wire:key="suggested-product-{{ $product->id }}" class="group relative flex h-full flex-col overflow-hidden rounded-[30px] border border-white/15 bg-white/5 p-5 text-left text-white/80 shadow-[0_16px_40px_rgba(12,5,24,0.4)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_14px_32px_rgba(236,72,153,0.3)]" style="min-width:280px;max-width:340px;flex:1 1 320px;">
                                    <a href="/{{ $product->slug }}" wire:navigate.hover class="block">
                                        <div class="relative overflow-hidden rounded-[22px]">
                                            <img src="{{ asset('storage/images/cover/' . $product->slug . '.webp') }}" alt="{{ $product->name }}" class="w-full rounded-[22px] border border-white/20 object-cover shadow-[0_14px_32px_rgba(17,0,34,0.45)]">
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
                                    <button type="button" wire:click="addToCart('{{ $product->id }}')" wire:loading.attr="disabled" wire:target="addToCart('{{ $product->id }}')" class="group mt-5 flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 px-6 py-3 text-sm font-semibold text-white shadow-[0_16px_36px_rgba(236,72,153,0.45)] ring-1 ring-white/20 transition-all duration-300 ease-out hover:scale-[1.02] hover:shadow-[0_28px_64px_rgba(236,72,153,0.6)] hover:ring-2 hover:ring-pink-300/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0f0218] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100">
                                        <span wire:loading.remove wire:target="addToCart('{{ $product->id }}')" class="flex items-center gap-2 transition-all duration-200">
                                            <flux:icon.shopping-cart class="h-4 w-4 transition-transform duration-200 group-hover:scale-110" />
                                            <span class="tracking-wide">Tambah</span>
                                        </span>
                                        <span wire:loading wire:target="addToCart('{{ $product->id }}')" class="flex items-center justify-center gap-2">
                                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        @endisland


        <div x-data="{ showFooterBtn: false }"
             x-init="
                const btn = $refs.checkoutBtn;
                const handler = () => {
                    if (!btn) return;
                    const rect = btn.getBoundingClientRect();
                    showFooterBtn = rect.bottom > window.innerHeight || rect.top < 0;
                };
                window.addEventListener('scroll', handler);
                window.addEventListener('resize', handler);
                handler();
             "
             class="relative">
                <div class="container">
                    <x-footer />
                </div>
                <div
                    x-data="{
                        showFooter: false,
                        isMobile: window.innerWidth < 640,
                        update() {
                            this.isMobile = window.innerWidth < 640;
                        }
                    }"
                    x-init="
                        const mainBtn = $refs.checkoutBtn;
                        const observer = new IntersectionObserver(([entry]) => {
                            showFooter = !entry.isIntersecting && isMobile;
                        }, { threshold: 0.1 });
                        observer.observe(mainBtn);
                        window.addEventListener('resize', () => {
                            update();
                            // Re-evaluate visibility if mainBtn is not in view
                            if (mainBtn) {
                                const rect = mainBtn.getBoundingClientRect();
                                const inView = rect.top >= 0 && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight);
                                showFooter = !inView && isMobile;
                            }
                        });
                    "
                    class="fixed bottom-0 left-0 right-0 z-50 bg-gradient-to-t from-[#0f0218] via-[#0f0218]/90 to-transparent px-4 py-3 flex justify-center shadow-2xl"
                    x-show="showFooter"
                    style="display: none;"
                >
                    <flux:button variant="primary" href="{{ route('checkout') }}" wire:navigate.hover class="cart-button-primary flex items-center justify-center gap-2 px-8 py-4 text-lg font-semibold w-full max-w-md">
                        <flux:icon.credit-card class="h-5 w-5" />
                        Terus ke bayaran
                    </flux:button>
                </div>
        </div>

        <div style="background: rgba(11,15,26,0.6); border-top: 1px solid rgba(207,214,245,0.18); padding: 0.6rem 1rem; font-size: 0.8rem; color: rgba(207,214,245,0.75); text-align: center; margin-top: 1rem;">
            <strong style="color: #ff8edc;">DEBUG:</strong>
            @auth
                <span style="color: #34d399;">✓ Authenticated User</span> -
                <span>{{ auth()->user()->name ?? 'No Name' }}</span>
                (<span>{{ auth()->user()->email ?? 'No Email' }}</span>)
            @else
                <span style="color: #fb7185;">✗ Guest User</span> - Not logged in
            @endauth
            | Role: {{ auth()->user()->role ?? 'guest' }}
        </div>
    </div>
</div>
