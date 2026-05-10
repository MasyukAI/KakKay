<x-layouts.pages>
    <x-slot:title>Buku, Jurnal &amp; Panduan — Kak Kay</x-slot>

    @php
        $labels = [
            'cara-bercinta' => 'Hubungan',
            'kasihi-puteri' => 'Healing',
            'diari-healing' => 'Healing',
            'cara-cakap' => 'Komunikasi',
            'kitab-kkdi' => 'Keibubapaan',
            'potensi-anak' => 'Keibubapaan',
            'sebab-terasa' => 'Refleksi',
            'tak-boleh-cakap' => 'Keluarga',
        ];
    @endphp

    <div class="storefront-shell storefront-shell-bottom relative overflow-hidden pb-16">
        <div class="relative z-10">
            <x-brand-header />

            <main class="space-y-12 pb-14 pt-6 sm:space-y-16 sm:pt-8">
                <section class="storefront-container">
                    <div class="grid gap-8 xl:grid-cols-[1.08fr_0.92fr] xl:items-center">
                        <div class="space-y-6">
                            <div class="storefront-eyebrow">Utama • Buku</div>
                            <h1 class="font-display text-4xl leading-tight text-[var(--store-text)] sm:text-5xl xl:text-[4.1rem]">
                                Buku, Jurnal &amp; Panduan
                                <span class="text-[var(--store-rose)]">untuk hati yang ingin pulih, kuat dan dicintai.</span>
                            </h1>
                            <p class="max-w-2xl text-lg text-[var(--store-text-soft)] sm:text-xl">
                                Setiap halaman adalah pelukan. Setiap perkataan ialah jalan pulang untuk diri sendiri, hubungan yang lebih jernih, dan hidup yang lebih matang.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <span class="storefront-badge">Bestseller pilihan pembaca</span>
                                <span class="storefront-badge">Buku fizikal &amp; panduan praktikal</span>
                                <span class="storefront-badge">Penghantaran seluruh Malaysia</span>
                            </div>
                        </div>

                        <div class="storefront-soft-card rounded-[2.4rem] p-4 sm:p-6">
                            <div class="grid gap-6 rounded-[2rem] bg-white/70 p-6 md:grid-cols-[0.9fr_1.1fr] md:items-center">
                                <img src="{{ asset('storage/images/kakkay.webp') }}" alt="Kak Kay" class="h-full min-h-[22rem] w-full rounded-[2rem] object-cover" />
                                <div class="space-y-5">
                                    <div class="storefront-badge">Buku yang betul, pada masa yang betul.</div>
                                    <p class="font-display text-3xl leading-tight text-[var(--store-text)]">
                                        “Setiap buku dibina untuk menemani fasa yang berbeza — pulih, faham, bercinta, dan bertumbuh.”
                                    </p>
                                    <p class="text-sm uppercase tracking-[0.24em] text-[var(--store-text-soft)]">— Kak Kay</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="storefront-container space-y-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap gap-3">
                            @foreach (['Semua', 'Hubungan', 'Healing', 'Keibubapaan', 'Jurnal', 'Digital'] as $index => $chip)
                                <button type="button" class="rounded-full border px-4 py-2 text-sm font-medium transition {{ $index === 0 ? 'border-[color:var(--store-border-strong)] bg-[#fff1ea] text-[var(--store-rose)]' : 'border-[color:var(--store-border)] bg-white/75 text-[var(--store-text-soft)] hover:text-[var(--store-rose)]' }}">
                                    {{ $chip }}
                                </button>
                            @endforeach
                        </div>

                        <div class="w-full lg:w-auto">
                            <select class="storefront-input w-full px-4 py-3 text-sm lg:w-56">
                                <option>Susun mengikut populariti</option>
                                <option>Harga rendah ke tinggi</option>
                                <option>Harga tinggi ke rendah</option>
                                <option>Paling baru</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ($products as $index => $product)
                            <article class="storefront-card storefront-product-card rounded-[2rem] p-4">
                                <div class="relative overflow-hidden rounded-[1.6rem] bg-[#fff6ef] p-4">
                                    @if ($product->is_featured)
                                        <span class="absolute left-4 top-4 rounded-full bg-[var(--store-rose)] px-3 py-1 text-xs font-semibold text-white">Bestseller</span>
                                    @elseif ($index === 1)
                                        <span class="absolute left-4 top-4 rounded-full bg-[#c4d8b1] px-3 py-1 text-xs font-semibold text-[#53603d]">New</span>
                                    @endif
                                    <a href="/{{ $product->slug }}" wire:navigate.hover class="block rounded-[1.3rem] bg-white px-4 pt-4 shadow-[0_18px_32px_rgba(132,99,82,0.12)]">
                                        <img src="{{ asset('storage/images/cover/' . $product->slug . '.webp') }}" alt="{{ $product->name }}" class="mx-auto h-72 w-auto object-contain" />
                                    </a>
                                </div>

                                <div class="space-y-3 px-1 pt-5">
                                    <div>
                                        <div class="text-sm text-[var(--store-text-soft)]">{{ $labels[$product->slug] ?? 'Buku' }}</div>
                                        <a href="/{{ $product->slug }}" wire:navigate.hover class="mt-1 block text-xl font-semibold leading-snug text-[var(--store-text)] transition hover:text-[var(--store-rose)]">
                                            {{ $product->name }}
                                        </a>
                                    </div>

                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-lg font-semibold text-[var(--store-text)]">{{ \Akaunting\Money\Money::MYR($product->price)->format() }}</span>
                                        <span class="text-sm text-[var(--store-text-soft)]">★ 4.9</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="storefront-container">
                    <div class="storefront-soft-card overflow-hidden rounded-[2.4rem] p-6 sm:p-8">
                        <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                            <div class="space-y-4">
                                <div class="storefront-eyebrow">Pilihan istimewa</div>
                                <h2 class="font-display text-3xl text-[var(--store-text)] sm:text-4xl">Pakej Pulih &amp; Dicintai ♡</h2>
                                <p class="text-base text-[var(--store-text-soft)] sm:text-lg">
                                    Empat judul pilihan Kak Kay untuk menemani perjalanan memulihkan hati, memahami hubungan, dan kembali menyayangi diri sendiri.
                                </p>
                                <div class="flex items-end gap-3">
                                    <span class="text-3xl font-semibold text-[var(--store-rose)]">{{ \Akaunting\Money\Money::MYR($bundleTotal)->format() }}</span>
                                    <span class="pb-1 text-lg text-[var(--store-text-soft)] line-through">{{ \Akaunting\Money\Money::MYR($bundleCompareTotal)->format() }}</span>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('cart') }}" wire:navigate.hover class="storefront-button-primary">Tambah ke Troli</a>
                                    <a href="#butiran-pakej" class="storefront-button-secondary">Lihat butiran pakej</a>
                                </div>
                            </div>

                            <div id="butiran-pakej" class="grid grid-cols-[repeat(4,minmax(0,1fr))_auto] items-center gap-3 overflow-hidden rounded-[2rem] bg-white/80 p-4">
                                @foreach ($bundleProducts as $product)
                                    <div class="rounded-[1.3rem] bg-[#fff7f0] p-3 text-center shadow-[0_10px_24px_rgba(120,87,72,0.08)]">
                                        <img src="{{ asset('storage/images/cover/' . $product->slug . '.webp') }}" alt="{{ $product->name }}" class="mx-auto h-36 w-auto object-contain" />
                                    </div>
                                @endforeach
                                <div class="flex h-full items-center justify-center rounded-full bg-[#f2ddd2] px-4 text-center text-sm font-semibold text-[var(--store-rose)]">
                                    Jimat
                                    <br>
                                    {{ \Akaunting\Money\Money::MYR(max($bundleCompareTotal - $bundleTotal, 0))->format() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="storefront-container space-y-6">
                    <div class="text-center">
                        <span class="storefront-divider">Kata mereka yang telah membaca</span>
                    </div>
                    <div class="grid gap-4 lg:grid-cols-3">
                        @foreach ([
                            'Buku Kak Kay hadir tepat pada waktunya. Setiap muka surat terasa seperti bercakap terus dengan hati saya.' => 'Aina, 33',
                            'Diari Healing bantu saya lepaskan banyak yang dipendam. Menangis, tapi juga sangat lega.' => 'Nurul, 29',
                            'Kasihi Puteri mengingatkan saya untuk kembali pada diri sendiri. Lembut, jujur, dan sangat menenangkan.' => 'Siti, 35',
                        ] as $quote => $author)
                            <article class="storefront-card rounded-[1.75rem] p-6">
                                <div class="storefront-rating text-lg">★★★★★</div>
                                <p class="mt-4 text-[var(--store-text)]">{{ $quote }}</p>
                                <p class="mt-4 text-sm font-medium text-[var(--store-text-soft)]">— {{ $author }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
            </main>

            <div class="storefront-container">
                <x-footer />
            </div>
        </div>
    </div>
</x-layouts.pages>