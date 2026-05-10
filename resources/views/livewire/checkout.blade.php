<div class="checkout-container storefront-shell storefront-shell-bottom relative overflow-hidden pb-16">
    <div class="relative z-10">
        <x-brand-header :cart-quantity="$cartQuantity ?? null" action-label="Akaun Saya" :action-href="auth()->check() ? route('dashboard') : route('login')" />

        <main class="space-y-12 pb-14 pt-6 sm:space-y-16 sm:pt-8">
            <section class="storefront-container">
                <ol class="mx-auto flex max-w-3xl items-start justify-between gap-4 text-center text-sm text-store-soft">
                    @foreach ([['label' => 'Troli', 'active' => false], ['label' => 'Penghantaran & Bayaran', 'active' => true], ['label' => 'Selesai', 'active' => false]] as $step)
                        <li class="storefront-step flex-1" data-active="{{ $step['active'] ? 'true' : 'false' }}">
                            <div class="storefront-step-dot mx-auto">{{ $loop->iteration }}</div>
                            <div class="mt-3 font-medium {{ $step['active'] ? 'text-store-rose' : 'text-store-soft' }}">{{ $step['label'] }}</div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section class="storefront-container">
                <div class="grid gap-8 xl:grid-cols-[1.08fr_0.92fr]">
                    <div class="min-w-0 space-y-5">
                        <div class="storefront-card rounded-[2.2rem] p-6 sm:p-8">
                            <span class="storefront-eyebrow">Maklumat penghantaran</span>
                            <h1 class="mt-2 font-display text-3xl text-store sm:text-4xl">Lengkapkan butiran anda</h1>
                            <p class="mt-3 text-store-soft">Semua maklumat digunakan untuk penghantaran dan kemas kini pesanan sahaja. Ia disimpan dengan selamat.</p>
                        </div>

                        <div class="min-w-0">
                            {{ $this->form }}
                        </div>
                    </div>

                    <aside class="space-y-5">
                        <div class="storefront-soft-card storefront-summary-card rounded-[2.2rem] p-6 sm:p-8">
                            <div>
                                <span class="storefront-eyebrow">Ringkasan pesanan</span>
                                <h2 class="mt-2 font-display text-3xl text-store">Pesanan anda</h2>
                            </div>

                            @if (! empty($cartItems))
                                <ul class="mt-6 space-y-3 text-sm text-store-soft">
                                    @foreach ($cartItems as $item)
                                        <li class="rounded-[1.4rem] border border-store bg-white px-4 py-4">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="font-semibold text-store">{{ $item['name'] }}</div>
                                                    <div class="mt-1 text-xs uppercase tracking-[0.2em] text-store-soft">Kuantiti {{ $item['quantity'] }}</div>
                                                </div>
                                                <span class="font-semibold text-store">{{ \Akaunting\Money\Money::MYR($item['price'])->format() }}</span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="mt-6 rounded-[1.4rem] border border-store bg-white px-4 py-4 text-sm text-store-soft">
                                    Troli kosong. Sila kembali ke halaman buku untuk menambah item.
                                </div>
                            @endif

                            <div class="mt-6 space-y-4 rounded-[1.6rem] border border-store bg-white px-5 py-5 text-sm text-store-soft">
                                <div class="flex items-center justify-between">
                                    <span>Jumlah harga</span>
                                    <span class="font-semibold text-store">{{ $this->getSubtotal()->format() }}</span>
                                </div>
                                @if ($this->getSavings()->getAmount() > 0)
                                    <div class="flex items-center justify-between text-[#6f9f55]">
                                        <span>Anda jimat</span>
                                        <span class="font-semibold">-{{ $this->getSavings()->format() }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <span>Penghantaran</span>
                                    <span class="font-semibold text-store">{{ $this->getShipping()->format() }}</span>
                                </div>
                                <div class="border-t border-store pt-4">
                                    <div class="flex items-center justify-between text-xl font-semibold text-store">
                                        <span>Jumlah</span>
                                        <span class="text-store-rose">{{ $this->getTotal()->format() }}</span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" wire:click="submitCheckout" wire:loading.attr="disabled" class="storefront-button-primary mt-6 w-full">
                                <span wire:loading.remove wire:target="submitCheckout">Bayar Sekarang</span>
                                <span wire:loading wire:target="submitCheckout">Memproses pembayaran...</span>
                            </button>

                            <div class="mt-6 rounded-[1.6rem] border border-store bg-white px-5 py-5 text-sm text-store-soft">
                                <div class="font-semibold text-store">Pembayaran selamat &amp; dipercayai</div>
                                <div class="mt-4 flex items-center justify-center rounded-[1.2rem] bg-[#fff8f2] p-4">
                                    <img src="{{ asset('storage/images/fpx.webp') }}" alt="Payment methods" class="h-10 object-contain" />
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>
        </main>

        <div class="storefront-container">
            <x-footer />
        </div>
    </div>
</div>
