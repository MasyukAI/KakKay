@php
  $price = \Akaunting\Money\Money::MYR((int) $product->price)->format();
  $comparePrice = $product->compare_price ? \Akaunting\Money\Money::MYR((int) $product->compare_price)->format() : null;
  $highlights = [
    'Bahasa mudah dan dekat di hati',
    'Teknik praktikal, terus boleh guna',
    'Sesuai untuk semua fasa cinta',
    'Bonus refleksi & doa untuk pasangan',
  ];
  $chapters = [
    ['title' => 'Kenali Diri, Kenali Cinta', 'desc' => 'Membaca semula pola hati supaya anda lebih jujur tentang apa yang diperlukan.'],
    ['title' => 'Komunikasi dari Hati', 'desc' => 'Cara menyatakan keperluan dengan lembut tanpa memadamkan diri sendiri.'],
    ['title' => 'Seni Menangani Konflik', 'desc' => 'Belajar bertengkar dengan matang, bukan saling melukakan.'],
    ['title' => 'Cinta yang Diredhai', 'desc' => 'Menjaga hubungan dengan ilmu, adab, dan kesedaran yang lebih dalam.'],
  ];
@endphp

<div class="storefront-shell storefront-shell-bottom relative overflow-hidden pb-16">
  <div class="relative z-10">
    <x-brand-header :cart-quantity="\AIArmada\Cart\Facades\Cart::getTotalQuantity()" />

    <main class="space-y-12 pb-14 pt-6 sm:space-y-16 sm:pt-8">
      <section class="storefront-container">
        <div class="grid gap-8 xl:grid-cols-[0.96fr_1.04fr] xl:items-start">
          <div class="space-y-4">
            <div class="text-sm text-store-soft">Utama • Buku • {{ $product->name }}</div>
            <div class="storefront-soft-card rounded-[2.4rem] p-5 sm:p-6">
              <div class="rounded-[2rem] bg-white p-5 shadow-[0_24px_56px_rgba(120,87,72,0.1)]">
                @if ($product->getFirstMediaUrl('cover'))
                  <img src="{{ $product->getFirstMediaUrl('cover') }}" alt="{{ $product->name }}" class="mx-auto h-[30rem] w-auto max-w-full object-contain" />
                @else
                  <img src="{{ asset('storage/images/cover/' . $product->slug . '.webp') }}" alt="{{ $product->name }}" class="mx-auto h-[30rem] w-auto max-w-full object-contain" />
                @endif
              </div>
              <div class="mt-4 grid grid-cols-4 gap-3">
                @foreach (range(1, 4) as $index)
                  <div class="rounded-[1.2rem] border border-store bg-white/80 p-3 shadow-[0_10px_20px_rgba(120,87,72,0.06)]">
                    <img src="{{ asset('storage/images/cover/' . $product->slug . '.webp') }}" alt="{{ $product->name }}" class="mx-auto h-20 w-auto object-contain" />
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          <div class="space-y-6">
            <div class="storefront-badge">Buku pilihan Kak Kay ♥</div>
            <div>
              <h1 class="font-display text-4xl leading-tight text-store sm:text-5xl">{{ $product->name }}</h1>
              <p class="mt-4 text-lg text-store-soft sm:text-xl">{{ $product->description ?? 'Panduan praktikal yang membimbing anda memahami diri, hubungan, dan cinta dengan lebih matang.' }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-sm text-store-soft">
              <div class="storefront-rating text-lg">★★★★★</div>
              <span>4.9 (124 ulasan)</span>
              <span>Telah terjual 10K+ naskhah</span>
            </div>

            <div class="flex flex-wrap items-end gap-4">
              <div class="text-5xl font-semibold text-store-rose">{{ $price }}</div>
              @if ($comparePrice)
                <div class="pb-2 text-lg text-store-soft line-through">{{ $comparePrice }}</div>
              @endif
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
              @foreach (['Bahasa mudah dan dekat di hati', 'Teknik praktikal boleh terus guna', 'Sesuai untuk semua fasa cinta'] as $point)
                <div class="storefront-card rounded-[1.4rem] px-4 py-3 text-sm text-store-soft">{{ $point }}</div>
              @endforeach
            </div>

            <div class="flex items-center gap-4">
              <span class="text-sm font-medium text-store-soft">Kuantiti</span>
              <div class="flex items-center gap-3 rounded-full border border-store bg-white px-4 py-2 shadow-[0_12px_24px_rgba(120,87,72,0.08)]">
                <button type="button" wire:click="decreaseQuantity" class="text-store-soft transition hover:text-store-rose">
                  <flux:icon.minus class="h-4 w-4" />
                </button>
                <span class="min-w-6 text-center font-semibold text-store">{{ $quantity }}</span>
                <button type="button" wire:click="increaseQuantity" class="text-store-soft transition hover:text-store-rose">
                  <flux:icon.plus class="h-4 w-4" />
                </button>
              </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
              <button type="button" wire:click="addToCart" class="storefront-button-secondary w-full border-store-strong">
                <span wire:loading.remove wire:target="addToCart">Tambah ke Troli</span>
                <span wire:loading wire:target="addToCart">Menambah...</span>
              </button>
              <button type="button" wire:click="buyNow" class="storefront-button-primary w-full">
                <span wire:loading.remove wire:target="buyNow">Beli Sekarang</span>
                <span wire:loading wire:target="buyNow">Membawa ke bayaran...</span>
              </button>
            </div>

            <div class="grid gap-4 sm:grid-cols-3 text-sm text-store-soft">
              <div class="storefront-card rounded-[1.4rem] p-4">Penghantaran pantas 1–3 hari bekerja</div>
              <div class="storefront-card rounded-[1.4rem] p-4">Pembayaran selamat SSL encrypted</div>
              <div class="storefront-card rounded-[1.4rem] p-4">Jaminan pulangan 7 hari</div>
            </div>
          </div>
        </div>
      </section>

      <section class="storefront-container space-y-6">
        <div class="text-center">
          <span class="storefront-divider">Apa yang anda akan dapat</span>
        </div>
        <div class="grid gap-4 lg:grid-cols-4">
          @foreach ($highlights as $highlight)
            <article class="storefront-card rounded-[1.8rem] p-5 text-center text-store-soft">
              <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#f5dfd6] text-store-rose">
                <flux:icon.heart class="h-6 w-6" />
              </div>
              <p class="mt-4 text-base font-medium text-store">{{ $highlight }}</p>
            </article>
          @endforeach
        </div>
      </section>

      <section class="storefront-container">
        <div class="storefront-soft-card rounded-[2.4rem] p-6 sm:p-8">
          <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
            <div>
              <span class="storefront-eyebrow">Intipati di dalam buku</span>
              <h2 class="mt-2 font-display text-4xl text-store">Bab demi bab untuk refleksi, komunikasi, dan tindakan praktikal.</h2>
              <p class="mt-4 text-lg text-store-soft">Setiap bahagian ditulis untuk mengajak anda melihat hubungan dengan lebih tenang dan berani — bukan sekadar rasa, tetapi juga ilmu.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              @foreach ($chapters as $chapter)
                <article class="storefront-card rounded-[1.6rem] p-5">
                  <h3 class="font-display text-2xl text-store">{{ $chapter['title'] }}</h3>
                  <p class="mt-3 text-sm text-store-soft">{{ $chapter['desc'] }}</p>
                </article>
              @endforeach
            </div>
          </div>
        </div>
      </section>

      <section class="storefront-container">
        <div class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
          <div class="storefront-card rounded-[2rem] p-6 sm:p-8">
            <div class="grid gap-6 sm:grid-cols-[0.84fr_1.16fr] sm:items-center">
              <img src="{{ asset('storage/images/kakkay.webp') }}" alt="Kak Kay" class="h-full min-h-[16rem] w-full rounded-[1.8rem] object-cover" />
              <div>
                <span class="storefront-eyebrow">Tentang penulis</span>
                <h2 class="mt-2 font-display text-3xl text-store">Kak Kay</h2>
                <p class="mt-4 text-store-soft">Penulis, kaunselor, dan pencipta KKDI. Beliau menulis untuk membantu wanita memahami diri, membina hubungan yang sihat, dan kembali berani mencintai dengan ilmu.</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2 text-sm text-store-soft">
                  <div class="storefront-card rounded-[1.2rem] px-4 py-3">10+ tahun pengalaman</div>
                  <div class="storefront-card rounded-[1.2rem] px-4 py-3">100K+ klien &amp; pembaca dibantu</div>
                </div>
              </div>
            </div>
          </div>

          <div class="storefront-soft-card rounded-[2rem] p-6 sm:p-8">
            <div class="text-center">
              <span class="storefront-divider">Apa kata pembaca</span>
            </div>
            <div class="mt-6 grid gap-4 lg:grid-cols-3">
              @foreach ([
                'Buku ini buat saya lebih berani bercakap jujur tentang keperluan hati sendiri.' => 'Nur A.',
                'Setiap bab seperti ada refleksi yang betul-betul kena pada masanya.' => 'Syafiqah M.',
                'Ilmu praktikal yang boleh terus guna dengan pasangan. Sangat membantu.' => 'Hafizah R.',
              ] as $quote => $author)
                <article class="storefront-card rounded-[1.6rem] p-5">
                  <div class="storefront-rating text-lg">★★★★★</div>
                  <p class="mt-4 text-store">{{ $quote }}</p>
                  <p class="mt-4 text-sm font-medium text-store-soft">— {{ $author }}</p>
                </article>
              @endforeach
            </div>
          </div>
        </div>
      </section>

      @if ($relatedProducts->isNotEmpty())
        <section class="storefront-container space-y-6">
          <div class="grid gap-6 xl:grid-cols-[1fr_1.05fr]">
            <div class="storefront-card rounded-[2rem] p-6 sm:p-8">
              <h2 class="font-display text-3xl text-store">Soalan Lazim</h2>
              <div class="mt-6 space-y-3">
                @foreach ([
                  'Buku ini sesuai untuk siapa?' => 'Sesuai untuk sesiapa yang mahu memahami diri, komunikasi, dan hubungan dengan lebih matang.',
                  'Berapa lama tempoh penghantaran?' => 'Kebiasaannya 1 hingga 3 hari bekerja ke seluruh Malaysia.',
                  'Bolehkah ia dibaca jika saya masih bujang?' => 'Ya. Buku ini banyak membantu dari sudut refleksi diri dan cara membina hubungan yang sihat sejak awal.',
                ] as $question => $answer)
                  <details class="rounded-[1.4rem] border border-store bg-[#fffaf6] p-4">
                    <summary class="cursor-pointer list-none font-medium text-store">{{ $question }}</summary>
                    <p class="mt-3 text-sm text-store-soft">{{ $answer }}</p>
                  </details>
                @endforeach
              </div>
            </div>

            <div class="storefront-soft-card rounded-[2rem] p-6 sm:p-8">
              <div class="flex items-end justify-between gap-4">
                <h2 class="font-display text-3xl text-store">Buku berkaitan</h2>
                <a href="{{ route('books') }}" wire:navigate.hover class="text-sm font-semibold text-store-rose">Lihat semua →</a>
              </div>
              <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($relatedProducts as $relatedProduct)
                  <article class="storefront-card storefront-product-card rounded-[1.5rem] p-4">
                    <a href="/{{ $relatedProduct->slug }}" wire:navigate.hover class="block rounded-[1.2rem] bg-[#fff7f0] p-4">
                      <img src="{{ asset('storage/images/cover/' . $relatedProduct->slug . '.webp') }}" alt="{{ $relatedProduct->name }}" class="mx-auto h-40 w-auto object-contain" />
                    </a>
                    <div class="mt-4 text-sm font-semibold leading-snug text-store">{{ $relatedProduct->name }}</div>
                    <div class="mt-2 text-sm text-store-soft">{{ \Akaunting\Money\Money::MYR((int) $relatedProduct->price)->format() }}</div>
                  </article>
                @endforeach
              </div>
            </div>
          </div>
        </section>
      @endif

      <section class="storefront-container">
        <div class="storefront-soft-card flex flex-col gap-5 rounded-[2.2rem] px-6 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-8">
          <div>
            <div class="font-display text-3xl text-store sm:text-4xl">Bersedia untuk mencintai dengan ilmu?</div>
            <p class="mt-3 text-store-soft">Dapatkan buku ini hari ini dan mulakan perjalanan cinta anda dengan lebih jelas, lembut, dan matang.</p>
          </div>
          <div class="flex flex-wrap gap-3">
            <button type="button" wire:click="buyNow" class="storefront-button-primary">Beli Sekarang</button>
            <button type="button" wire:click="addToCart" class="storefront-button-secondary border-store-strong">Tambah ke Troli</button>
          </div>
        </div>
      </section>
    </main>

    <div class="storefront-container">
      <x-footer />
    </div>
  </div>
</div>
