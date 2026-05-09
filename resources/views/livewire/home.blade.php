<div class="relative min-h-screen bg-[#0f0218] text-white ambient-glow">

    <div class="relative z-10">
        <x-brand-header :cart-quantity="$cartQuantity ?? null" />

        <main class="pb-24">
            <!-- HERO -->
            <section class="relative pt-16 pb-20">
                <div class="mx-auto max-w-7xl px-6 sm:px-8">
                    <div class="grid items-center gap-12 lg:grid-cols-2">
                        <div class="order-2 space-y-8 lg:order-1">
                            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-5 py-2 text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Counsellor · Therapist · Pencipta KKDI</span>
                            <h1 class="font-caveat-brush font-display text-5xl leading-[0.92] tracking-tight sm:text-6xl lg:text-7xl">
                                Berani kenal diri.<br>
                                <span class="bg-gradient-to-r from-pink-300 via-rose-400 to-orange-200 bg-clip-text text-transparent">Berani pulih.</span>
                            </h1>
                            <p class="max-w-xl text-lg leading-relaxed text-white/85">
                                Kak Kay bantu wanita yang dah penat tahan sorang-sorang untuk mula faham diri, lepaskan luka lama, dan hidup dengan lebih jujur. Melalui buku, sesi peribadi, dan komuniti KKDI.
                            </p>
                            <div class="flex flex-wrap items-center gap-4">
                                <a href="#featured" class="btn primary cart-button-primary flex items-center gap-3 rounded-full px-7 py-3 text-base font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-105 hover:brightness-110 hover:shadow-[0_15px_35px_rgba(255,105,180,0.4)]">
                                    <span>Tengok Buku Terbaru</span>
                                    <flux:icon.arrow-down class="h-4 w-4" />
                                </a>
                                <a href="#library" class="rounded-full border border-white/30 px-7 py-3 text-base font-semibold text-white/80 backdrop-blur-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-pink-400/80 hover:bg-white/10 hover:text-white">
                                    Semua Karya
                                </a>
                            </div>
                        </div>
                        <div class="order-1 relative flex justify-center lg:order-2 lg:justify-end">
                            <div class="group relative max-w-[260px] lg:max-w-[300px]">
                                <div class="absolute -inset-4 rounded-[28px] bg-gradient-to-br from-pink-400/40 via-fuchsia-400/25 to-purple-500/35 opacity-80 blur-2xl transition duration-300 group-hover:opacity-100"></div>
                                <div class="relative overflow-hidden rounded-[24px] border border-white/15 bg-white/10 p-3 backdrop-blur-sm shadow-[0_14px_32px_rgba(15,3,37,0.55)] transition duration-300 group-hover:-translate-y-1">
                                    <div class="relative overflow-hidden rounded-[20px]">
                                        <img src="{{ asset('storage/images/kakkay.webp') }}" alt="Kak Kay" loading="lazy" class="w-full rounded-[20px] object-cover shadow-[0_16px_36px_rgba(17,0,34,0.55)]">
                                    </div>
                                    <div class="mt-2.5 flex items-center gap-2 px-1">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/10 text-xs">💬</span>
                                        <p class="text-xs italic text-white/70">&ldquo;Healing bukan tanda lemah. Itu tanda kau dah cukup berani.&rdquo;</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SOCIAL PROOF STRIP -->
            <section class="pb-20">
                <div class="mx-auto max-w-4xl px-6">
                    <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-4 rounded-full border border-white/10 bg-white/5 px-8 py-5 backdrop-blur-sm">
                        @php($heroStats = [
                            ['value' => '1K+', 'label' => 'Sesi Peribadi'],
                            ['value' => '300+', 'label' => 'Program & Bengkel'],
                            ['value' => '250K+', 'label' => 'Peserta KKDI'],
                        ])
                        @foreach ($heroStats as $index => $stat)
                            <div wire:key="hero-stat-{{ $index }}" class="flex items-center gap-3">
                                <span class="text-2xl font-bold text-white">{{ $stat['value'] }}</span>
                                <span class="text-xs uppercase tracking-[0.2em] text-white/50">{{ $stat['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- HOW KAK KAY HELPS -->
            <section id="help" class="pb-24">
                <div class="mx-auto max-w-6xl px-6 sm:px-8">
                    <div class="text-center">
                        <h2 class="font-display text-3xl text-white sm:text-4xl">Tiga cara Kak Kay boleh bantu</h2>
                        <p class="mx-auto mt-3 max-w-xl text-lg text-white/70">Pilih mana yang sesuai dengan rentak kau. Boleh mula dari mana-mana.</p>
                    </div>
                    <div class="mt-10 grid gap-6 md:grid-cols-3">
                        @foreach ([
                            ['icon' => '📖', 'title' => 'Buku & Panduan', 'desc' => 'Baca bila-bila, refleksi sorang-sorang atau dengan pasangan. Ditulis dari pengalaman sebenar — bukan teori.', 'link' => '#library', 'cta' => 'Lihat buku'],
                            ['icon' => '💬', 'title' => 'Sesi 1-to-1', 'desc' => 'Ruang selamat untuk cerita apa yang selama ni kau pendam. Kak Kay dengar, bimbing, dan bantu kau nampak jalan.', 'link' => '#', 'cta' => 'Tempah sesi'],
                            ['icon' => '👥', 'title' => 'KKDI Circle', 'desc' => 'Komuniti wanita yang faham tanpa judge. Doa, diskusi, accountability — jalan sama-sama.', 'link' => '#', 'cta' => 'Sertai komuniti'],
                        ] as $pillar)
                            <div class="group rounded-[28px] border border-white/10 bg-white/5 p-8 transition duration-300 hover:-translate-y-1 hover:bg-white/10 hover:shadow-[0_14px_32px_rgba(236,72,153,0.15)]">
                                <span class="text-3xl">{{ $pillar['icon'] }}</span>
                                <h3 class="mt-4 text-xl font-semibold text-white">{{ $pillar['title'] }}</h3>
                                <p class="mt-3 text-sm leading-relaxed text-white/70">{{ $pillar['desc'] }}</p>
                                <a href="{{ $pillar['link'] }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-pink-200 transition hover:text-pink-100">
                                    {{ $pillar['cta'] }}
                                    <flux:icon.arrow-right class="h-3.5 w-3.5" />
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- FEATURED BOOK -->
            <section id="featured" class="scroll-mt-10 pb-24">
                <div class="mx-auto flex max-w-6xl flex-col gap-12 px-6 sm:px-8 lg:flex-row lg:items-center">
                    <div class="relative flex-1">
                        <div class="absolute -inset-8 rounded-[40px] bg-gradient-to-br from-pink-400/35 via-rose-500/25 to-purple-500/35 blur-2xl"></div>
                        <div class="relative overflow-hidden rounded-[36px] border border-white/15 bg-white/10 p-8 backdrop-blur-sm shadow-[0_14px_32px_rgba(15,3,37,0.55)]">
                            <div class="relative mx-auto w-full max-w-sm">
                                <img src="{{ asset('storage/images/cover/' . $featuredProduct->slug . '.webp') }}" alt="{{ $featuredProduct->name }}" fetchpriority="high" class="relative w-full rounded-[30px] border border-white/20 object-cover shadow-[0_18px_48px_rgba(17,0,34,0.55)]">
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 space-y-6">
                        <div class="flex flex-wrap items-center gap-3 text-sm uppercase tracking-[0.28em] text-white/60">
                            <span class="rounded-full border border-white/20 bg-white/10 px-4 py-1">Bestseller</span>
                            <span class="rounded-full border border-white/20 bg-white/10 px-4 py-1">Terbaru</span>
                        </div>
                        <h2 class="font-display text-3xl text-white sm:text-4xl">{{ $featuredProduct->name }}</h2>
                        <p class="text-lg leading-relaxed text-white/80">{{ $featuredProduct->description }}</p>
                        <div class="flex items-center gap-6">
                            <div>
                                <div class="text-xs uppercase tracking-[0.3em] text-white/60">Harga</div>
                                <div class="text-3xl font-semibold text-white">{{ \Akaunting\Money\Money::MYR($featuredProduct->price)->format() }}</div>
                            </div>
                            <a href="/{{ $featuredProduct->slug }}" wire:navigate.hover class="btn primary cart-button-primary flex items-center gap-3 rounded-full px-8 py-3 text-base font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-105 hover:brightness-110 hover:shadow-[0_15px_35px_rgba(255,105,180,0.4)]">
                                <flux:icon.arrow-right class="h-5 w-5" />
                                Lihat selanjutnya
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TESTIMONIALS -->
            <section id="community" class="pb-24">
                <div class="mx-auto max-w-5xl px-6 sm:px-8">
                    <div class="text-center">
                        <h2 class="font-display text-3xl text-white sm:text-4xl">Suara mereka yang dah mula berubah</h2>
                        <p class="mt-3 text-lg text-white/70">Bukan motivasi kosong. Ini cerita betul.</p>
                    </div>
                    @php($testimonials = [
                        ['quote' => 'Saya ingat saya dah okay. Lepas sesi dengan Kak Kay, baru sedar betapa lama saya pendam semuanya. Sekarang saya tahu cara nak lepaskan.', 'name' => 'Aina, Kuala Lumpur'],
                        ['quote' => 'Buku dia buat saya nangis bukan sebab sedih, tapi sebab akhirnya ada orang cakap apa yang saya rasa selama ni tapi tak tahu nak explain.', 'name' => 'Hannah, Melaka'],
                        ['quote' => 'KKDI bagi saya jawapan yang saya cari bertahun. Sekarang saya faham kenapa saya react macam tu, dan saya dah boleh pilih untuk berbeza.', 'name' => 'Siti, Brunei'],
                    ])
                    <div class="mt-10 grid gap-6 md:grid-cols-3">
                        @foreach ($testimonials as $index => $testimonial)
                            <figure wire:key="testimonial-{{ $index }}" class="rounded-[26px] border border-white/10 bg-white/5 p-6 transition duration-300 hover:bg-white/10">
                                <blockquote class="text-base leading-relaxed text-white/85">&ldquo;{{ $testimonial['quote'] }}&rdquo;</blockquote>
                                <figcaption class="mt-5 text-sm text-white/50">&mdash; {{ $testimonial['name'] }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- LIBRARY -->
            <section id="library" class="scroll-mt-10 pb-24">
                <div class="mx-auto max-w-7xl px-6 sm:px-8">
                    <div class="text-center">
                        <h2 class="font-display text-3xl text-white sm:text-4xl">Karya Kak Kay</h2>
                        <p class="mt-3 text-lg text-white/70">Setiap buku ditulis dari pengalaman sebenar &mdash; bukan teori. Ini panduan hidup.</p>
                    </div>
                    <div class="mt-10 flex flex-wrap justify-center gap-6 max-w-6xl mx-auto">
                        @foreach ($products as $product)
                            <a wire:key="product-{{ $product->id }}" href="/{{ $product->slug }}" wire:navigate.hover class="group relative flex h-full flex-col overflow-hidden rounded-[30px] border border-white/15 bg-white/5 p-5 text-left text-white/80 shadow-[0_16px_40px_rgba(12,5,24,0.4)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_14px_32px_rgba(236,72,153,0.3)] w-full max-w-sm sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]">
                                <div class="relative overflow-hidden rounded-[22px]">
                                    <img src="{{ asset('storage/images/cover/' . $product->slug . '.webp') }}" alt="{{ $product->name }}" loading="lazy" class="w-full rounded-[22px] border border-white/20 object-cover shadow-[0_14px_32px_rgba(17,0,34,0.45)]">
                                </div>
                                <div class="mt-5 space-y-3">
                                    <h3 class="text-xl font-semibold text-white">{{ $product->name }}</h3>
                                    <p class="line-clamp-3 text-sm text-white/70">{{ $product->description }}</p>
                                    <div class="inline-flex items-center gap-2 text-sm font-semibold text-pink-200">
                                        Baca lagi
                                        <flux:icon.arrow-right class="h-4 w-4" />
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section>
                <div class="mx-auto max-w-3xl px-6 text-center">
                    <h2 class="font-display text-3xl text-white sm:text-4xl">Kau tak perlu tunggu ready.<br>Mula je dulu.</h2>
                    <p class="mx-auto mt-4 max-w-lg text-lg text-white/70">Ambil satu buku, baca satu bab malam ni. Perjalanan seribu langkah bermula dari keberanian untuk mengaku: &ldquo;Aku perlukan perubahan.&rdquo;</p>
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                        <a href="/{{ $featuredProduct->slug }}" wire:navigate.hover class="cart-button-primary flex items-center gap-3 rounded-full px-8 py-3 text-base font-semibold">
                            <flux:icon.book-open class="h-5 w-5" />
                            Dapatkan buku
                        </a>
                        <a href="{{ route('checkout') }}" wire:navigate.hover class="rounded-full border border-white/30 px-8 py-3 text-base font-semibold text-white/80 backdrop-blur-sm transition hover:border-white/60 hover:text-white">
                            Sertai KKDI Circle
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <div class="container">
            <x-footer />
        </div>
    </div>
</div>
