@php
    $cartCount = max(0, (int) ($cartQuantity ?? 0));
    $kkdiHref = \Illuminate\Support\Facades\Route::has('kkdi') ? route('kkdi') : '#';
    $booksHref = \Illuminate\Support\Facades\Route::has('books') ? route('books') : '#';
    $consultationHref = \Illuminate\Support\Facades\Route::has('consultation') ? route('consultation') : '#';
    $heroImageUrl = asset('storage/images/kakkay.png');
    $floralImageUrl = asset('storage/images/floral.png');
    $heroBannerUrl = asset('storage/images/hero.png');
    $loungeImageUrl = asset('storage/images/launge.png');
    $featuredProductUrl = $featuredProduct ? url('/'.$featuredProduct->slug) : '#';
    $featuredImageUrl = $featuredCoverPath ? asset('storage/'.$featuredCoverPath) : null;
    $showcaseProducts = $products->take(8);

    $homeNavItems = [
        ['label' => 'Utama', 'href' => route('home'), 'spa' => true, 'active' => true],
        ['label' => 'Ujian KKDI', 'href' => $kkdiHref, 'spa' => true],
        ['label' => 'Buku', 'href' => $booksHref, 'spa' => true],
        ['label' => 'Konsultasi', 'href' => $consultationHref, 'spa' => true],
        ['label' => 'Tentang', 'href' => route('home').'#tentang', 'spa' => false],
        ['label' => 'Sumber', 'href' => route('home').'#sumber', 'spa' => false],
    ];

    $heroStats = [
        ['icon' => 'heart', 'value' => '10K+', 'label' => 'Insan dibantu'],
        ['icon' => 'user-group', 'value' => '300K+', 'label' => 'Komuniti & pengikut'],
        ['icon' => 'book-open', 'value' => '34', 'label' => 'Teknik & panduan'],
        ['icon' => 'sparkles', 'value' => '8+ Tahun', 'label' => 'Pengalaman & amalan'],
    ];

    $journeyCards = [
        ['title' => 'Ambil Ujian KKDI', 'description' => 'Kenali potensi anak, pasangan atau diri anda dengan ujian yang saintifik dan mudah.', 'cta' => 'Mula Sekarang', 'href' => $kkdiHref, 'icon' => 'heart'],
        ['title' => 'Beli Buku & Panduan', 'description' => 'Buku & jurnal yang ditulis dengan kasih untuk panduan anda, setiap hari.', 'cta' => 'Terokai Koleksi', 'href' => $booksHref, 'icon' => 'book-open'],
        ['title' => 'Tempah Konsultasi', 'description' => 'Sesi peribadi bersama Kak Kay untuk bimbingan yang lebih mendalam & tersendiri.', 'cta' => 'Lihat Pilihan Sesi', 'href' => $consultationHref, 'icon' => 'chat-bubble-left-right'],
    ];

    $featuredPoints = [
        'Bahasa santai & mudah',
        'Sesuai untuk semua fasa cinta',
        'Tip praktikal, terus boleh guna',
        'Ditulis dengan pengalaman sebenar',
    ];

    $testimonials = [
        ['quote' => 'Ujian KKDI bantu saya kenal potensi anak lebih dalam. Cara Kak Kay menerangkan sangat jelas & membuka mata.', 'author' => 'Aisyah, Ibu'],
        ['quote' => 'Buku Kak Kay memang cakap terus ke hati. Setiap muka surat ada pengajaran yang betul-betul saya perlukan.', 'author' => 'Nadia, Pembaca'],
        ['quote' => 'Sesi konsultasi dengan Kak Kay mengubah cara saya melihat diri & membuat keputusan dengan lebih tenang.', 'author' => 'Farah, Klien'],
    ];

    $footerPopularBooks = [
        ['label' => 'Macam Ni Rupanya Cara Nak Bercinta', 'href' => '/cara-bercinta'],
        ['label' => 'Rahsia Mengenali Potensi Anak KKDI', 'href' => '/potensi-anak'],
        ['label' => 'Diari Healing Yang Mendewasakan', 'href' => '/diari-healing'],
        ['label' => 'Lihat semua buku', 'href' => $booksHref],
    ];

    $socialLinks = [
        ['label' => 'IG', 'href' => 'https://instagram.com/kamaliakamal'],
        ['label' => 'FB', 'href' => 'https://facebook.com/kamaliakamal'],
        ['label' => 'YT', 'href' => 'https://youtube.com'],
        ['label' => 'TT', 'href' => 'https://tiktok.com/@kakkayloveme'],
    ];

    $contactItems = [
        ['label' => 'hello@kakkay.my', 'href' => 'mailto:hello@kakkay.my', 'icon' => 'envelope'],
        ['label' => '+60 13-884 6594', 'href' => 'https://wa.me/60138846594', 'icon' => 'phone'],
        ['label' => 'Cheras, Selangor', 'href' => null, 'icon' => 'map-pin'],
        ['label' => 'Isnin — Jumaat (9am — 6pm)', 'href' => null, 'icon' => 'clock'],
    ];
@endphp

<div class="homepage-redesign relative overflow-hidden pb-18 sm:pb-24">
    <img src="{{ $floralImageUrl }}" alt="" aria-hidden="true" class="homepage-floral homepage-floral-top-left" />
    <img src="{{ $floralImageUrl }}" alt="" aria-hidden="true" class="homepage-floral homepage-floral-top-right" />
    <img src="{{ $floralImageUrl }}" alt="" aria-hidden="true" class="homepage-floral homepage-floral-mid-left" />
    <img src="{{ $floralImageUrl }}" alt="" aria-hidden="true" class="homepage-floral homepage-floral-mid-right" />
    <img src="{{ $floralImageUrl }}" alt="" aria-hidden="true" class="homepage-floral homepage-floral-bottom-left" />
    <img src="{{ $floralImageUrl }}" alt="" aria-hidden="true" class="homepage-floral homepage-floral-bottom-right" />

    <div class="relative z-10">
        <header class="homepage-header px-5 py-4 sm:px-8 sm:py-5 xl:px-10">
            <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('home') }}" wire:navigate.hover class="flex items-center gap-3 text-store no-underline">
                        <div>
                            <div class="font-brand text-3xl font-light tracking-tight text-store-rose-dark sm:text-[2.2rem]">Kak Kay ♡</div>
                            <div class="mt-1 text-[0.66rem] uppercase tracking-[0.28em] text-store-soft sm:text-[0.72rem]">Counsellor • Therapist • KKDI Creator • Author</div>
                        </div>
                    </a>

                    <nav class="hidden items-center gap-8 lg:flex">
                        @foreach ($homeNavItems as $item)
                            @if ($item['spa'])
                                <a href="{{ $item['href'] }}" wire:navigate.hover class="homepage-nav-link text-sm font-medium {{ ($item['active'] ?? false) ? 'is-active' : '' }}">{{ $item['label'] }}</a>
                            @else
                                <a href="{{ $item['href'] }}" class="homepage-nav-link text-sm font-medium">{{ $item['label'] }}</a>
                            @endif
                        @endforeach
                    </nav>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <button type="button" class="homepage-icon-button hidden sm:inline-flex" aria-label="Cari">
                            <flux:icon.magnifying-glass class="h-5 w-5" />
                        </button>

                        <a href="{{ route('cart') }}" wire:navigate.hover class="homepage-icon-button relative" aria-label="Troli">
                            <flux:icon.shopping-bag class="h-5 w-5" />
                            @if ($cartCount > 0)
                                <span class="absolute -right-1.5 -top-1.5 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-store-rose px-1 text-[0.64rem] font-semibold text-white">{{ $cartCount }}</span>
                            @endif
                        </a>

                        <a href="{{ $kkdiHref }}" wire:navigate.hover class="storefront-button-primary hidden sm:inline-flex">Ambil Ujian KKDI</a>
                    </div>
                </div>

                <nav class="mt-4 flex gap-2 overflow-x-auto pb-1 lg:hidden">
                    @foreach ($homeNavItems as $item)
                        @if ($item['spa'])
                            <a href="{{ $item['href'] }}" wire:navigate.hover class="homepage-mobile-nav-link whitespace-nowrap {{ ($item['active'] ?? false) ? 'is-active' : '' }}">{{ $item['label'] }}</a>
                        @else
                            <a href="{{ $item['href'] }}" class="homepage-mobile-nav-link whitespace-nowrap">{{ $item['label'] }}</a>
                        @endif
                    @endforeach
                    <a href="{{ $kkdiHref }}" wire:navigate.hover class="storefront-button-primary whitespace-nowrap sm:hidden">Ambil Ujian KKDI</a>
                </nav>
        </header>

        {{-- Hero: full-bleed wide image with text overlay --}}
        <section class="homepage-hero-banner relative overflow-hidden">
            {{-- Background image --}}
            <img src="{{ $heroBannerUrl }}" alt="" class="absolute inset-0 h-full w-full object-cover object-right">

            {{-- Left gradient veil --}}
            <div class="homepage-hero-veil absolute inset-0"></div>

            {{-- Floral accent --}}
            <img src="{{ $floralImageUrl }}" alt="" aria-hidden="true" class="homepage-floral homepage-floral-hero-right hidden xl:block" />

            {{-- Signature note: desktop top-right --}}
            <div class="homepage-signature-note absolute right-8 top-8 z-10 hidden max-w-24 text-left xl:block">
                <p class="font-script text-xl leading-[1.15] text-store">Saya di sini untuk menemani perjalanan anda.</p>
                <p class="mt-1.5 text-[0.62rem] uppercase tracking-[0.22em] text-store-soft">— Kak Kay ♡</p>
            </div>

            {{-- Text content --}}
            <div class="homepage-container relative z-10 py-8 sm:py-10 xl:py-12">
                <div class="max-w-xl space-y-3 sm:space-y-4">
                    <div class="storefront-eyebrow">Pulih diri, faham hati, bina cinta yang sihat. ♡</div>

                    <h1 class="font-display text-[2.8rem] leading-[0.98] tracking-[-0.035em] text-store sm:text-[3.6rem]">
                        <span class="block">Diri yang tenang,</span>
                        <span class="block text-store-rose">cinta yang matang,</span>
                        <span class="block">hidup yang bermakna.</span>
                    </h1>

                    <p class="max-w-md text-base leading-7 text-store-soft">
                        Bersama Kak Kay, temui diri sebenar, sembuh luka lama, dan bina hubungan yang lebih sihat dan bahagia.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ $kkdiHref }}" wire:navigate.hover class="storefront-button-primary">Ambil Ujian KKDI</a>
                        <a href="{{ $booksHref }}" wire:navigate.hover class="storefront-button-secondary">
                            Terokai Buku &amp; Panduan
                            <flux:icon.arrow-right class="h-4 w-4" />
                        </a>
                    </div>

                    <div class="homepage-community-pill inline-flex flex-wrap items-center gap-4 rounded-full px-4 py-3 sm:px-5">
                        <div class="flex -space-x-3">
                            @foreach ([20, 35, 50] as $position)
                                <span class="inline-flex h-10 w-10 overflow-hidden rounded-full border-2 border-white bg-[#ecd9d0]">
                                    <img src="{{ $heroImageUrl }}" alt="" class="h-full w-full object-cover" style="object-position: center {{ $position }}%;">
                                </span>
                            @endforeach
                        </div>

                        <p class="max-w-xs text-sm text-store-soft">
                            Membantu ribuan insan menemui diri &amp; membina hubungan yang lebih baik.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Stats --}}
        <section class="homepage-container pt-2 pb-4 sm:pt-3 sm:pb-6">
            <div class="homepage-stats-card grid gap-2 rounded-[2rem] px-5 py-4 sm:grid-cols-2 sm:px-6 sm:py-5 xl:grid-cols-4 xl:gap-0 xl:px-8">
                @foreach ($heroStats as $stat)
                    <div class="homepage-stat-item flex items-center gap-4 px-3 py-2 xl:justify-center xl:px-6" wire:key="home-stat-{{ $stat['label'] }}">
                        <span class="homepage-stat-icon inline-flex h-10 w-10 items-center justify-center rounded-full text-[var(--store-rose)]">
                            <flux:icon :name="$stat['icon']" class="h-5 w-5" />
                        </span>

                        <div>
                            <div class="text-xl font-semibold text-[var(--store-text)] sm:text-2xl">{{ $stat['value'] }}</div>
                            <div class="text-sm text-[var(--store-text-soft)]">{{ $stat['label'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <main class="space-y-16 pt-8 sm:space-y-20 sm:pt-10 xl:pt-14">
            <section class="homepage-container space-y-6">
                <div class="text-center">
                    <span class="storefront-divider">Tiga cara untuk mula perjalanan baharu</span>
                    <h2 class="mt-4 font-display text-[2.2rem] leading-tight text-[var(--store-text)] sm:text-[2.7rem]">Tiga cara untuk mula perjalanan baharu</h2>
                </div>

                <div class="grid gap-5 xl:grid-cols-3">
                    @foreach ($journeyCards as $item)
                        <article class="homepage-section-card flex h-full flex-col items-center rounded-[2rem] p-6 text-center sm:p-8" wire:key="journey-{{ $item['title'] }}">
                            <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-[#f8dfd7] text-[var(--store-rose)]">
                                <flux:icon :name="$item['icon']" class="h-7 w-7" />
                            </span>

                            <h3 class="mt-6 font-display text-[2rem] leading-tight text-[var(--store-text)]">{{ $item['title'] }}</h3>
                            <p class="mt-3 max-w-[18rem] text-[var(--store-text-soft)]">{{ $item['description'] }}</p>

                            <a href="{{ $item['href'] }}" wire:navigate.hover class="mt-auto inline-flex items-center gap-2 pt-6 text-sm font-semibold text-[var(--store-rose)]">
                                {{ $item['cta'] }}
                                <flux:icon.arrow-right class="h-4 w-4" />
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>

            @if ($featuredProduct)
                <section class="homepage-container" id="featured">
                    <div class="grid gap-10 xl:grid-cols-[0.46fr_0.54fr] xl:items-center">
                        <div class="relative">
                            <div class="homepage-featured-book-shell rounded-[2.4rem] p-5 sm:p-8">
                                <div class="homepage-featured-book-stage relative rounded-[2rem] bg-white px-4 py-6 shadow-[0_22px_54px_rgba(120,87,72,0.1)] sm:px-6 sm:py-8">
                                    @if ($hasFeaturedCover && $featuredImageUrl)
                                        <img src="{{ $featuredImageUrl }}" alt="{{ $featuredProduct->name }}" class="mx-auto h-[26rem] w-auto max-w-full object-contain sm:h-[30rem] xl:h-[34rem]">
                                    @else
                                        <div class="flex h-[26rem] items-end rounded-[1.8rem] bg-[#f6e6da] p-6 font-display text-3xl leading-tight text-[var(--store-text)] sm:h-[30rem] xl:h-[34rem]">{{ $featuredProduct->name }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="homepage-still-life hidden xl:flex" aria-hidden="true">
                                <span class="homepage-still-life-vase"></span>
                                <span class="homepage-still-life-cup"></span>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="storefront-eyebrow">Buku terlaris ♡</div>
                            <h2 class="font-display text-[2.4rem] leading-tight text-[var(--store-text)] sm:text-[3rem] xl:text-[3.4rem]">{{ $featuredProduct->name }}</h2>
                            <p class="max-w-2xl text-base leading-7 text-[var(--store-text-soft)] sm:text-lg sm:leading-8">{{ $featuredProduct->description }}</p>

                            <ul class="grid gap-3 sm:grid-cols-2">
                                @foreach ($featuredPoints as $point)
                                    <li class="homepage-feature-pill rounded-full px-4 py-3">
                                        <span class="flex items-center gap-3 text-sm text-[var(--store-text-soft)]">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#f5dfd5] text-[var(--store-rose)]">
                                                <flux:icon.check class="h-4 w-4" />
                                            </span>
                                            <span>{{ $point }}</span>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="flex flex-wrap gap-3">
                                <a href="{{ $featuredProductUrl }}" wire:navigate.hover class="storefront-button-primary">Dapatkan Sekarang</a>
                                <a href="{{ $booksHref }}" wire:navigate.hover class="storefront-button-secondary">
                                    Lihat sinopsis &amp; preview
                                    <flux:icon.arrow-right class="h-4 w-4" />
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            <section class="homepage-container space-y-6" id="sumber">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <div class="storefront-eyebrow">Koleksi Buku &amp; Jurnal</div>
                        <h2 class="mt-3 font-display text-[2.2rem] leading-tight text-[var(--store-text)] sm:text-[2.7rem]">Koleksi Buku &amp; Jurnal</h2>
                    </div>

                    <a href="{{ $booksHref }}" wire:navigate.hover class="hidden items-center gap-2 text-sm font-semibold text-[var(--store-rose)] sm:inline-flex">
                        Lihat semua
                        <flux:icon.arrow-right class="h-4 w-4" />
                    </a>
                </div>

                <div class="homepage-carousel-shell relative" x-data="bookShelfCarousel()" x-init="setup()" x-on:resize.window.debounce.150ms="updateControls()" data-book-carousel>
                    <button
                        type="button"
                        class="homepage-carousel-arrow left-0 hidden xl:inline-flex"
                        x-on:click="prev()"
                        x-bind:disabled="!canScrollPrev"
                        x-bind:class="{ 'is-disabled': !canScrollPrev, 'is-hidden': !hasOverflow }"
                        aria-controls="book-shelf-carousel-track"
                        aria-label="Lihat buku sebelumnya"
                        data-book-carousel-prev
                    >
                        <flux:icon.chevron-left class="h-5 w-5" />
                    </button>

                    <button
                        type="button"
                        class="homepage-carousel-arrow right-0 hidden xl:inline-flex"
                        x-on:click="next()"
                        x-bind:disabled="!canScrollNext"
                        x-bind:class="{ 'is-disabled': !canScrollNext, 'is-hidden': !hasOverflow }"
                        aria-controls="book-shelf-carousel-track"
                        aria-label="Lihat buku seterusnya"
                        data-book-carousel-next
                    >
                        <flux:icon.chevron-right class="h-5 w-5" />
                    </button>

                    <div class="homepage-carousel-viewport">
                        <div
                            id="book-shelf-carousel-track"
                            x-ref="track"
                            x-on:scroll.passive="updateControls()"
                            class="homepage-carousel-track"
                            tabindex="0"
                            aria-label="Koleksi buku dan jurnal Kak Kay"
                            data-book-carousel-track
                        >
                            @foreach ($showcaseProducts as $product)
                                <article class="homepage-shelf-card homepage-carousel-slide p-1" wire:key="shelf-product-{{ $product->slug }}" data-book-carousel-slide>
                                <a href="/{{ $product->slug }}" wire:navigate.hover class="homepage-shelf-cover block rounded-[1.2rem] bg-white px-2 py-3 shadow-[0_14px_28px_rgba(120,87,72,0.08)]">
                                    <img src="{{ asset('storage/images/cover/' . $product->slug . '.webp') }}" alt="{{ $product->name }}" class="mx-auto h-44 w-auto object-contain xl:h-48">
                                </a>

                                <div class="space-y-1.5 px-1 pt-3">
                                    <a href="/{{ $product->slug }}" wire:navigate.hover class="block min-h-[3.25rem] text-sm font-semibold leading-5 text-[var(--store-text)] transition hover:text-[var(--store-rose)]">{{ $product->name }}</a>
                                    <div class="text-sm font-medium text-[var(--store-text-soft)]">{{ \Akaunting\Money\Money::MYR($product->price)->format() }}</div>
                                </div>
                            </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-5 sm:hidden">
                        <a href="{{ $booksHref }}" wire:navigate.hover class="inline-flex items-center gap-2 text-sm font-semibold text-[var(--store-rose)]">
                            Lihat semua
                            <flux:icon.arrow-right class="h-4 w-4" />
                        </a>
                    </div>
                </div>
            </section>

            <section class="homepage-container" id="tentang">
                <div class="homepage-consult-shell overflow-hidden rounded-[2.4rem] px-6 py-6 sm:px-8 sm:py-8">
                    <div class="grid gap-6 xl:grid-cols-[0.84fr_1.16fr] xl:items-center">
                        <div class="space-y-5">
                            <div class="storefront-eyebrow">Ruang selamat untuk anda ♡</div>
                            <h2 class="font-display text-[2.25rem] leading-tight text-[var(--store-text)] sm:text-[2.8rem]">Kadang-kadang, kita cuma perlukan seseorang untuk mendengar &amp; membimbing.</h2>
                            <p class="max-w-xl text-base leading-7 text-[var(--store-text-soft)] sm:text-lg sm:leading-8">Sesi konsultasi peribadi bersama Kak Kay membantu anda memahami diri, menyusun semula hidup, dan melangkah ke arah perubahan yang sebenar.</p>

                            <div class="flex flex-wrap gap-3">
                                <a href="{{ $consultationHref }}" wire:navigate.hover class="storefront-button-primary">Tempah Sesi Konsultasi</a>
                                <a href="{{ $consultationHref }}" wire:navigate.hover class="storefront-button-secondary">Lihat jenis sesi</a>
                            </div>
                        </div>

                        <div class="homepage-lounge-scene relative min-h-[22rem] overflow-hidden rounded-[2rem] sm:min-h-[26rem]">
                            <img src="{{ $loungeImageUrl }}" alt="Ruang konsultasi Kak Kay" class="h-full w-full object-cover">

                            <div class="homepage-lounge-feature-card absolute right-4 top-4 w-[13.5rem] rounded-[1.7rem] bg-white/94 p-5 shadow-[0_18px_36px_rgba(120,87,72,0.12)] sm:right-6 sm:top-6 sm:w-[15rem]">
                                <div class="space-y-4 text-sm text-[var(--store-text-soft)]">
                                    <div class="flex items-start gap-3">
                                        <flux:icon.user class="mt-0.5 h-4 w-4 text-[var(--store-rose)]" />
                                        <span>Sesi 1:1 bersama Kak Kay</span>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <flux:icon.envelope class="mt-0.5 h-4 w-4 text-[var(--store-rose)]" />
                                        <span>Selamat, rahsia &amp; tanpa penghakiman</span>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <flux:icon.sparkles class="mt-0.5 h-4 w-4 text-[var(--store-rose)]" />
                                        <span>Bimbingan praktikal &amp; penuh empati</span>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <flux:icon.chat-bubble-left-right class="mt-0.5 h-4 w-4 text-[var(--store-rose)]" />
                                        <span>Sesi dalam talian &amp; fleksibel</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="homepage-container space-y-6">
                <div class="text-center">
                    <span class="storefront-divider">Kata mereka yang telah berubah</span>
                    <h2 class="mt-4 font-display text-[2.2rem] leading-tight text-[var(--store-text)] sm:text-[2.7rem]">Kata mereka yang telah berubah</h2>
                </div>

                <div class="grid gap-5 xl:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <article class="homepage-quote-card rounded-[1.8rem] px-6 py-5" wire:key="testimonial-{{ $testimonial['author'] }}">
                            <div class="flex items-start justify-between gap-4">
                                <span class="text-4xl leading-none text-[#d6a995]">“</span>
                                <span class="text-[0.82rem] tracking-[0.3em] text-[var(--store-gold)]">★★★★★</span>
                            </div>

                            <p class="mt-3 text-[var(--store-text)]">{{ $testimonial['quote'] }}</p>
                            <p class="mt-4 text-sm font-medium text-[var(--store-text-soft)]">— {{ $testimonial['author'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="homepage-container">
                <div class="homepage-cta-shell flex flex-col gap-5 rounded-[2.2rem] px-6 py-7 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <div>
                        <h2 class="font-display text-[2rem] leading-tight text-[var(--store-text)] sm:text-[2.4rem]">Sedia untuk versi terbaik diri anda?</h2>
                        <p class="mt-2 text-[var(--store-text-soft)]">Ambil langkah kecil hari ini, untuk perubahan besar esok.</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ $kkdiHref }}" wire:navigate.hover class="storefront-button-primary">Ambil Ujian KKDI Sekarang</a>
                        <a href="{{ $booksHref }}" wire:navigate.hover class="storefront-button-secondary">Terokai Semua Buku</a>
                    </div>
                </div>
            </section>
        </main>

        <section class="homepage-container mt-16 sm:mt-20">
            <footer id="contact" class="homepage-footer-shell relative overflow-hidden rounded-[2.6rem] px-6 py-10 sm:px-8 lg:px-10">
                <img src="{{ $floralImageUrl }}" alt="" aria-hidden="true" class="homepage-floral homepage-floral-footer-left" />
                <img src="{{ $floralImageUrl }}" alt="" aria-hidden="true" class="homepage-floral homepage-floral-footer-right" />

                <div class="relative z-10 grid gap-10 lg:grid-cols-[1.2fr_repeat(4,minmax(0,1fr))]">
                    <div class="space-y-5">
                        <a href="{{ route('home') }}" wire:navigate.hover class="inline-flex items-center text-[var(--store-text)] no-underline">
                            <div>
                                <div class="font-brand text-4xl font-black tracking-[-0.05em] text-[var(--store-rose-dark)]">Kak Kay ♡</div>
                                <div class="mt-1 text-xs uppercase tracking-[0.24em] text-[var(--store-text-soft)]">Counsellor • Therapist • KKDI Creator • Author</div>
                            </div>
                        </a>

                        <p class="max-w-sm text-sm leading-6 text-[var(--store-text-soft)]">Memberi bimbingan dengan kasih, menyembuh luka hati, dan membina hubungan yang lebih sihat.</p>

                        <div class="flex items-center gap-2">
                            @foreach ($socialLinks as $social)
                                <a href="{{ $social['href'] }}" target="_blank" rel="noopener" class="homepage-social-link">{{ $social['label'] }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-[var(--store-text)]">Pautan</h3>
                        <ul class="mt-4 space-y-3 text-sm text-[var(--store-text-soft)]">
                            <li><a href="{{ route('home') }}" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Utama</a></li>
                            <li><a href="{{ $kkdiHref }}" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Ujian KKDI</a></li>
                            <li><a href="{{ $booksHref }}" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Buku</a></li>
                            <li><a href="{{ $consultationHref }}" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Konsultasi</a></li>
                            <li><a href="{{ route('home') }}#tentang" class="transition hover:text-[var(--store-rose)]">Tentang</a></li>
                            <li><a href="{{ route('home') }}#sumber" class="transition hover:text-[var(--store-rose)]">Sumber</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-[var(--store-text)]">Buku Popular</h3>
                        <ul class="mt-4 space-y-3 text-sm text-[var(--store-text-soft)]">
                            @foreach ($footerPopularBooks as $book)
                                <li><a href="{{ $book['href'] }}" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">{{ $book['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-[var(--store-text)]">Bantuan</h3>
                        <ul class="mt-4 space-y-3 text-sm text-[var(--store-text-soft)]">
                            <li><a href="{{ $consultationHref }}#soalan-lazim" class="transition hover:text-[var(--store-rose)]">Soalan Lazim</a></li>
                            <li><a href="/shipping-policy" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Penghantaran &amp; Pemulangan</a></li>
                            <li><a href="/terms-of-service" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Terma &amp; Syarat</a></li>
                            <li><a href="/privacy-policy" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Dasar Privasi</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-[var(--store-text)]">Hubungi</h3>
                        <ul class="mt-4 space-y-3 text-sm text-[var(--store-text-soft)]">
                            @foreach ($contactItems as $contact)
                                <li>
                                    @if ($contact['href'])
                                        <a href="{{ $contact['href'] }}" class="homepage-footer-contact-link">
                                            <span class="homepage-footer-contact-icon">
                                                <flux:icon :name="$contact['icon']" class="h-4 w-4" />
                                            </span>
                                            <span>{{ $contact['label'] }}</span>
                                        </a>
                                    @else
                                        <span class="homepage-footer-contact-item">
                                            <span class="homepage-footer-contact-icon">
                                                <flux:icon :name="$contact['icon']" class="h-4 w-4" />
                                            </span>
                                            <span>{{ $contact['label'] }}</span>
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="relative z-10 mt-10 border-t border-[color:var(--store-border)] pt-4 text-xs text-[var(--store-text-soft)]">
                    © 2025 Kamelia Kamal Research International (Kak Kay). Hak cipta terpelihara.
                </div>
            </footer>
        </section>
    </div>
</div>
