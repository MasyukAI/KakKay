<div class="storefront-shell storefront-shell-bottom relative overflow-hidden pb-16">
    <div class="relative z-10">
        <x-brand-header action-label="Akaun Saya" :action-href="auth()->check() ? route('dashboard') : route('login')" />

        <main class="space-y-12 pb-14 pt-6 sm:space-y-16 sm:pt-8">
            <section class="storefront-container">
                <ol class="mx-auto flex max-w-3xl items-start justify-between gap-4 text-center text-sm text-store-soft">
                    @foreach ([['label' => 'Troli', 'active' => true], ['label' => 'Penghantaran & Bayaran', 'active' => false], ['label' => 'Selesai', 'active' => false]] as $step)
                        <li class="storefront-step flex-1" data-active="{{ $step['active'] ? 'true' : 'false' }}">
                            <div class="storefront-step-dot mx-auto">{{ $loop->iteration }}</div>
                            <div class="mt-3 font-medium {{ $step['active'] ? 'text-store-rose' : 'text-store-soft' }}">{{ $step['label'] }}</div>
                        </li>
                    @endforeach
                </ol>
            </section>

            @if (empty($cartItems))
                <section class="storefront-container">
                    <div class="storefront-soft-card rounded-[2.6rem] px-6 py-12 text-center sm:px-10">
                        <span class="storefront-badge">Troli masih tenang ♡</span>
                        <h1 class="mt-6 font-display text-4xl text-store sm:text-5xl">Troli anda masih kosong.</h1>
                        <p class="mx-auto mt-4 max-w-2xl text-lg text-store-soft">Belum terlambat untuk mula. Pilih buku atau panduan yang paling dekat dengan fasa anda sekarang, kemudian sambung ke pembayaran bila sudah bersedia.</p>
                        <div class="mt-8 flex flex-wrap justify-center gap-3">
                            <a href="{{ route('books') }}" wire:navigate.hover class="storefront-button-primary">Terokai Buku</a>
                            <a href="{{ route('home') }}" wire:navigate.hover class="storefront-button-secondary">Kembali ke Utama</a>
                        </div>
                    </div>
                </section>
            @else
                <section class="storefront-container">
                    <div class="grid gap-8 xl:grid-cols-[1.08fr_0.92fr]">
                        <div class="space-y-5">
                            <div class="storefront-card rounded-[2.2rem] p-6 sm:p-8">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <span class="storefront-eyebrow">Semak troli anda</span>
                                        <h1 class="mt-2 font-display text-3xl text-store sm:text-4xl">Pilihan anda untuk langkah seterusnya</h1>
                                    </div>
                                    <a href="{{ route('books') }}" wire:navigate.hover class="storefront-button-secondary text-sm">Tambah lagi buku</a>
                                </div>

                                <div class="mt-6 space-y-4">
                                    @foreach ($cartItems as $item)
                                        <article wire:key="cart-item-{{ $item['id'] }}" class="rounded-[1.8rem] border border-store bg-[#fffaf6] p-4 shadow-[0_14px_28px_rgba(120,87,72,0.06)] sm:p-5">
                                            <div class="grid gap-4 sm:grid-cols-[8rem_1fr_auto] sm:items-start">
                                                <a href="/{{ $item['slug'] }}" wire:navigate.hover class="rounded-[1.3rem] bg-white p-3 shadow-[0_12px_24px_rgba(120,87,72,0.06)]">
                                                    <img src="{{ asset('storage/images/cover/' . $item['slug'] . '.webp') }}" alt="{{ $item['name'] }}" class="mx-auto h-36 w-auto object-contain" />
                                                </a>

                                                <div>
                                                    <a href="/{{ $item['slug'] }}" wire:navigate.hover class="text-2xl font-semibold leading-tight text-store transition hover-store-rose">{{ $item['name'] }}</a>
                                                    <p class="mt-2 text-sm text-store-soft">Buku fizikal oleh Kak Kay • stok tersedia</p>
                                                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-store-soft">
                                                        <span>Harga seunit: <span class="font-semibold text-store">{{ $item['price'] }}</span></span>
                                                        <span>Jumlah item: <span class="font-semibold text-store">{{ $item['subtotal'] }}</span></span>
                                                    </div>
                                                </div>

                                                <div class="flex flex-col items-end gap-3">
                                                    <div class="flex items-center gap-3 rounded-full border border-store bg-white px-4 py-2 shadow-[0_10px_24px_rgba(120,87,72,0.08)]">
                                                        <button type="button" wire:click="decrementQuantity('{{ $item['id'] }}')" class="text-store-soft transition hover-store-rose">
                                                            <flux:icon.minus class="h-4 w-4" />
                                                        </button>
                                                        <span class="min-w-6 text-center font-semibold text-store">{{ $item['quantity'] }}</span>
                                                        <button type="button" wire:click="incrementQuantity('{{ $item['id'] }}')" class="text-store-soft transition hover-store-rose">
                                                            <flux:icon.plus class="h-4 w-4" />
                                                        </button>
                                                    </div>
                                                    <button type="button" wire:click="removeItem('{{ $item['id'] }}')" class="inline-flex items-center gap-2 rounded-full border border-[#edc3c0] px-4 py-2 text-sm font-medium text-[#c06269] transition hover:bg-[#fff0ef]">
                                                        <flux:icon.trash class="h-4 w-4" />
                                                        Buang
                                                    </button>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <aside class="space-y-5">
                            <div class="storefront-soft-card storefront-summary-card rounded-[2.2rem] p-6 sm:p-8">
                                <div>
                                    <span class="storefront-eyebrow">Ringkasan pesanan</span>
                                    <h2 class="mt-2 font-display text-3xl text-store">Jumlah keseluruhan</h2>
                                </div>

                                <div class="mt-6 space-y-3">
                                    <div class="text-sm font-semibold text-store">Kod kupon / diskaun</div>
                                    @if ($appliedVoucher)
                                        <div class="rounded-[1.3rem] border border-[#d9e8cc] bg-[#f3faee] px-4 py-3 text-sm text-store-soft">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <div class="font-semibold text-store">{{ $appliedVoucher['code'] }}</div>
                                                    <div>{{ $appliedVoucher['description'] ?? 'Diskaun digunakan' }}</div>
                                                </div>
                                                <button type="button" wire:click="removeVoucher" class="text-[#7f655d] transition hover:text-[#c06269]">
                                                    <flux:icon.x-mark class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex gap-2">
                                            <input type="text" wire:model="voucherCode" wire:keydown.enter="applyVoucher" placeholder="Masukkan kod kupon" class="storefront-input min-w-0 flex-1 px-4 py-3 text-sm" />
                                            <button type="button" wire:click="applyVoucher" class="storefront-button-primary px-5 py-3 text-sm">Guna</button>
                                        </div>
                                        @if ($voucherError)
                                            <p class="text-sm text-[#c06269]">{{ $voucherError }}</p>
                                        @endif
                                    @endif
                                </div>

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

                                <a href="{{ route('checkout') }}" wire:navigate.hover class="storefront-button-primary mt-6 w-full">Buat pembayaran sekarang</a>

                                <div class="mt-6 rounded-[1.6rem] border border-store bg-white px-5 py-5 text-sm text-store-soft">
                                    <h3 class="font-semibold text-store">Kenapa beli dengan Kak Kay?</h3>
                                    <ul class="mt-4 space-y-3">
                                        <li>100% produk original dan berkualiti</li>
                                        <li>Penghantaran pantas diproses dalam 1–2 hari bekerja</li>
                                        <li>Pembayaran selamat dan dipercayai</li>
                                        <li>Sokongan mesra jika anda perlukan bantuan</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="storefront-card rounded-[2rem] p-6">
                                <div class="text-sm font-semibold text-store">Dipercayai oleh pembaca</div>
                                <div class="mt-4 flex -space-x-3">
                                    @foreach (['A', 'F', 'N', 'S', 'R'] as $initial)
                                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full border-2 border-white bg-[#f2ddd3] font-semibold text-store-rose">{{ $initial }}</span>
                                    @endforeach
                                </div>
                                <div class="mt-4 text-sm text-store-soft">★★★★★ 4.9/5 (10K+ ulasan)</div>
                            </div>
                        </aside>
                    </div>
                </section>
            @endif

            @if ($this->suggestedProducts && $this->suggestedProducts->count() > 0)
                <section class="storefront-container space-y-6" id="recommended">
                    <div class="text-center">
                        <span class="storefront-divider">Anda mungkin juga suka</span>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($this->suggestedProducts as $product)
                            <article wire:key="suggested-product-{{ $product->id }}" class="storefront-card storefront-product-card rounded-[1.8rem] p-4">
                                <a href="/{{ $product->slug }}" wire:navigate.hover class="block rounded-[1.4rem] bg-[#fff7f0] p-4 shadow-[0_16px_28px_rgba(120,87,72,0.08)]">
                                    <img src="{{ asset('storage/images/cover/' . $product->slug . '.webp') }}" alt="{{ $product->name }}" class="mx-auto h-56 w-auto object-contain" />
                                </a>
                                <div class="space-y-2 px-1 pt-4">
                                    <a href="/{{ $product->slug }}" wire:navigate.hover class="text-xl font-semibold leading-snug text-store transition hover-store-rose">{{ $product->name }}</a>
                                    <p class="text-sm text-store-soft">{{ $product->description }}</p>
                                    <div class="text-sm font-semibold text-store">{{ \Akaunting\Money\Money::MYR($product->price)->format() }}</div>
                                </div>
                                <button type="button" wire:click="addToCart('{{ $product->id }}')" wire:loading.attr="disabled" wire:target="addToCart('{{ $product->id }}')" class="storefront-button-primary mt-5 w-full text-sm">
                                    <span wire:loading.remove wire:target="addToCart('{{ $product->id }}')">Tambah ke Troli</span>
                                    <span wire:loading wire:target="addToCart('{{ $product->id }}')">Menambah...</span>
                                </button>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </main>

        <div class="storefront-container">
            <x-footer />
        </div>
    </div>
</div>
