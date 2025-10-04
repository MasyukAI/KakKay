<div class="checkout-container relative overflow-hidden min-h-screen bg-[#0f0218] text-white">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-48 -left-40 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-pink-500/35 via-purple-500/25 to-rose-500/40 blur-3xl"></div>
        <div class="absolute top-1/2 -right-32 h-[520px] w-[520px] -translate-y-1/2 rounded-full bg-gradient-to-br from-fuchsia-500/25 via-rose-500/25 to-orange-400/35 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/2 h-[320px] w-[520px] -translate-x-1/2 rounded-full bg-gradient-to-br from-purple-600/30 via-indigo-500/20 to-pink-400/30 blur-3xl"></div>
    </div>

    <div class="relative z-10">
        <!-- Header Navigation -->
        <x-brand-header :cart-quantity="$cartQuantity ?? null" />

        <!-- Checkout Content -->
        <section class="pt-20">
            <div class="mx-auto max-w-7xl px-6 sm:px-8">
                <div class="relative mx-auto max-w-3xl">
                    <div class="absolute left-6 right-6 top-6 block h-px bg-white/15"></div>
                    <ol class="relative flex items-center justify-between gap-6 text-xs font-semibold uppercase tracking-[0.28em] text-white/60">
                        <li class="flex flex-col items-center gap-3">
                            <a href="{{ route('cart') }}" class="group flex h-12 w-12 items-center justify-center rounded-full border border-white/25 bg-white/10 text-pink-200 shadow-[0_10px_35px_rgba(236,72,153,0.28)] transition hover:border-white/50 hover:text-white">
                                <flux:icon.check-circle class="h-5 w-5" />
                            </a>
                            <span>Troli</span>
                        </li>
                        <li class="flex flex-col items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-white/40 bg-gradient-to-br from-pink-500 via-rose-500 to-purple-500 text-white shadow-[0_16px_45px_rgba(236,72,153,0.45)]">
                                <flux:icon.credit-card class="h-5 w-5" />
                            </div>
                            <span class="text-white">Bayaran</span>
                        </li>
                        <li class="flex flex-col items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-white/20 bg-white/5 text-white/60">
                                <flux:icon.clock class="h-5 w-5" />
                            </div>
                            <span>Pesanan</span>
                        </li>
                    </ol>
                </div>
            </div>
        </section>

        <section id="form" class="mt-14 pb-24">
            <div class="mx-auto max-w-7xl px-6 sm:px-8">
                <form wire:submit="submitCheckout" class="relative grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px] xl:grid-cols-[minmax(0,1fr)_400px]">
                        <div class="min-w-0">
                            {{ $this->form }}
                        </div>

                        <!-- Order Summary Sidebar -->
                        <aside class="mt-10 space-y-6 lg:mt-0">
                            <div class="cart-summary-card sticky p-6 sm:p-6.5">
                                <div class="absolute -top-20 right-0 h-48 w-48 rounded-full bg-gradient-to-br from-pink-400/30 via-purple-400/20 to-orange-300/30 blur-3xl"></div>
                                <div class="relative space-y-6">
                                    <div class="space-y-3">
                                        {{-- <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1 text-[0.7rem] font-semibold uppercase tracking-[0.32em] text-white/70">
                                            Langkah Bayaran
                                        </span> --}}
                                        <h3 class="font-display text-3xl text-white">Ringkasan Pesanan</h3>
                                        {{-- <p class="text-sm leading-relaxed text-white/70">Semua harga dalam Ringgit Malaysia (RM). Semak jumlah sebelum lengkapkan bayaran.</p> --}}
                                    </div>

                                    @if (! empty($cartItems))
                                        <ul class="space-y-3 text-sm text-white/80">
                                            @foreach ($cartItems as $item)
                                                <li class="flex flex-col gap-1 rounded-2xl border border-white/15 bg-white/10 px-4 py-3">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="font-semibold text-white">{{ $item['name'] }}</div>
                                                        <span class="text-white">{{ \Akaunting\Money\Money::MYR($item['price'])->format() }}</span>
                                                    </div>
                                                    <div class="w-full text-xs uppercase tracking-[0.28em] text-white/50 text-right">Qty {{ $item['quantity'] }}</div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-sm text-white/70">
                                            Troli kosong. Sila kembali ke halaman produk untuk menambah item.
                                        </div>
                                    @endif

                                    <div class="space-y-3 text-sm text-white/80">
                                        <div class="flex items-center justify-between">
                                            <span>Jumlah Harga</span>
                                            <span class="font-medium text-white">{{ $this->getSubtotal()->format() }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span>Penghantaran</span>
                                            <span class="font-medium text-white">{{ $this->getShipping()->format() }}</span>
                                        </div>
                                        <hr class="border-white/15">
                                        <div class="flex items-center justify-between text-lg font-bold">
                                            <span>Jumlah</span>
                                            <span class="bg-gradient-to-r from-pink-400 via-rose-500 to-purple-500 bg-clip-text text-transparent">{{ $this->getTotal()->format() }}</span>
                                        </div>
                                    </div>

                                    <flux:button type="submit" variant="primary" class="cart-button-primary flex w-full items-center justify-center gap-2 px-6 py-4 text-lg font-semibold" wire:loading.attr="disabled">
                                        <div wire:loading.remove class="flex flex-row items-center justify-center gap-2 min-w-0">
                                            <span class="flex flex-row items-center gap-2 min-w-0">
                                                <flux:icon.credit-card class="h-5 w-5 flex-shrink-0" />
                                                <span class="truncate">Bayar Sekarang</span>
                                            </span>
                                        </div>
                                        <div wire:loading class="flex items-center justify-center gap-3">
                                            <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </flux:button>

                                    {{-- <div class="mt-4 rounded-2xl border border-white/15 bg-white/5 p-4 text-xs text-white/70">
                                        Pastikan maklumat alamat tepat. Jika perlu ubah selepas pembayaran, hubungi kami segera melalui WhatsApp: <span class="text-pink-200 font-medium">+60 11-1234 5678</span>.
                                    </div> --}}
                                </div>
                            </div>

                            <div class="rounded-[32px] border border-white/10 bg-white/5 p-6 backdrop-blur-xl text-sm text-white/75">
                                {{-- <div class="flex items-center gap-3">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-lg">✅</span>
                                    <div>Sokongan selepas jualan tersedia 7 hari seminggu. Kami bantu sehingga buku selamat di tangan anda.</div>
                                </div> --}}
                                <div class="mt-6 flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <img src="{{ asset('storage/images/fpx.png') }}" alt="Payment methods" class="h-10 object-contain " />
                                </div>
                            </div>
                        </aside>
                    </form>
                </div>
            </div>
        </section>

        <div class="container">
            <x-footer />
        </div>
    </div>
</div>
