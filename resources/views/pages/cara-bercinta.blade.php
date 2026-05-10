<?php

declare(strict_types=1);

use AIArmada\Cart\Facades\Cart;
use App\Models\Product;
use Filament\Notifications\Notification;
use Livewire\Attributes\{Layout, Title};
use Livewire\Component;

new
#[Layout('components.layouts.pages')]
#[Title('Macam Ni Rupanya Cara Nak Bercinta, Mudahnya Lahai!')]
class extends Component {
    public ?Product $product = null;

    public int $quantity = 1;

    public function mount(): void
    {
        $this->product = Product::query()->where('slug', 'cara-bercinta')->first();
    }

    public function increaseQuantity(): void
    {
        if ($this->quantity < 9) {
            $this->quantity++;
        }
    }

    public function decreaseQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart(): void
    {
        if (! $this->product) {
            Notification::make()
                ->title('Produk tidak dijumpai')
                ->body('Produk yang diminta tidak wujud.')
                ->danger()
                ->send();

            return;
        }

        Cart::add(
            (string) $this->product->id,
            $this->product->name,
            $this->product->price,
            $this->quantity,
            [
                'slug' => $this->product->slug,
                'category' => 'books',
            ]
        );

        Notification::make()
            ->title('Berjaya ditambah')
            ->body('Produk telah ditambah ke troli.')
            ->success()
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->duration(3000)
            ->send();

        $this->dispatch('cart-updated');
        $this->redirect('/cart', navigate: true);
    }

    public function buyNow(): void
    {
        $this->addToCart();
        $this->redirect('/checkout', navigate: true);
    }
} ?>

@php
    $cartQuantity = Cart::getTotalQuantity();
    $price = $product ? \Akaunting\Money\Money::MYR($product->price)->format() : 'RM50.00';
@endphp

<div class="storefront-shell storefront-shell-bottom relative overflow-hidden pb-16">
    <div class="relative z-10">
        <x-brand-header :cart-quantity="$cartQuantity" />

        <main class="space-y-12 pb-14 pt-6 sm:space-y-16 sm:pt-8">
            <section class="storefront-container">
                <div class="grid gap-8 xl:grid-cols-[0.96fr_1.04fr] xl:items-start">
                    <div class="space-y-4">
                        <div class="text-sm text-store-soft">Utama • Buku • {{ $product?->name ?? 'Macam Ni Rupanya Cara Nak Bercinta' }}</div>
                        <div class="storefront-soft-card rounded-[2.4rem] p-5 sm:p-6">
                            <div class="rounded-[2rem] bg-white p-5 shadow-[0_24px_56px_rgba(120,87,72,0.1)]">
                                <img src="{{ asset('storage/images/cover/cara-bercinta.webp') }}" alt="{{ $product?->name ?? 'Macam Ni Rupanya Cara Nak Bercinta' }}" class="mx-auto h-[30rem] w-auto max-w-full object-contain" />
                            </div>
                            <div class="mt-4 grid grid-cols-4 gap-3">
                                @foreach (range(1, 4) as $index)
                                    <div class="rounded-[1.2rem] border border-store bg-white/80 p-3 shadow-[0_10px_20px_rgba(120,87,72,0.06)]">
                                        <img src="{{ asset('storage/images/cover/cara-bercinta.webp') }}" alt="Preview {{ $index }}" class="mx-auto h-20 w-auto object-contain" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="storefront-badge">Buku terlaris Kak Kay ♥</div>
                        <div>
                            <h1 class="font-display text-4xl leading-tight text-store sm:text-5xl">{{ $product?->name ?? 'Macam Ni Rupanya Cara Nak Bercinta, Mudahnya Lahai!' }}</h1>
                            <p class="mt-4 text-lg text-store-soft sm:text-xl">{{ $product?->description ?? 'Panduan genting yang menerangkan rahsia bercinta secara praktikal, sempoi, dan penuh kasih sayang.' }}</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 text-sm text-store-soft">
                            <div class="storefront-rating text-lg">★★★★★</div>
                            <span>4.9 (124 ulasan)</span>
                            <span>Telah terjual 10K+ naskhah</span>
                        </div>

                        <div class="flex flex-wrap items-end gap-4">
                            <div class="text-5xl font-semibold text-store-rose">{{ $price }}</div>
                            <div class="pb-2 text-lg text-store-soft line-through">RM69.00</div>
                            <span class="storefront-badge">Jimat RM10.00</span>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach (['Bahasa mudah dan dekat di hati', 'Teknik praktikal boleh terus guna', 'Sesuai untuk semua fasa cinta'] as $point)
                                <div class="storefront-card rounded-[1.4rem] px-4 py-3 text-sm text-store-soft">{{ $point }}</div>
                            @endforeach
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="text-sm font-medium text-store-soft">Kuantiti</span>
                            <div class="flex items-center gap-3 rounded-full border border-store bg-white px-4 py-2 shadow-[0_12px_24px_rgba(120,87,72,0.08)]">
                                <button type="button" wire:click="decreaseQuantity" class="text-store-soft transition hover-store-rose">
                                    <flux:icon.minus class="h-4 w-4" />
                                </button>
                                <span class="min-w-6 text-center font-semibold text-store">{{ $quantity }}</span>
                                <button type="button" wire:click="increaseQuantity" class="text-store-soft transition hover-store-rose">
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
                <div class="grid gap-4 lg:grid-cols-5">
                    @foreach (['Fahami diri & pasangan dengan lebih mendalam', 'Komunikasi lebih baik dan kurang salah faham', 'Bina hubungan sihat berdasarkan ilmu & cinta', 'Atasi konflik dengan cara yang berkesan', 'Bonus refleksi & doa untuk pasangan'] as $highlight)
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
                            <h2 class="mt-2 font-display text-4xl text-store">Bab demi bab yang mengajak anda membaca semula cara mencintai.</h2>
                            <p class="mt-4 text-lg text-store-soft">Bab-bab padat dengan ilmu, refleksi, dan latihan praktikal untuk membantu anda mendekati cinta dengan lebih sedar.</p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ([
                                ['title' => 'Kenali Diri, Kenali Cinta', 'desc' => 'Fahami nilai, emosi, dan pola hubungan yang mempengaruhi cara anda mencintai.'],
                                ['title' => 'Komunikasi dari Hati', 'desc' => 'Belajar bercakap dengan lembut tetapi jelas supaya hubungan terasa lebih selamat.'],
                                ['title' => 'Seni Menangani Konflik', 'desc' => 'Teknik praktikal untuk reda, memahami, dan kembali berhubung selepas bergaduh.'],
                                ['title' => 'Cinta yang Diredhai', 'desc' => 'Menjaga hubungan dengan ilmu, adab, dan kesedaran diri yang lebih tinggi.'],
                            ] as $chapter)
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
                                <p class="mt-4 text-store-soft">Penulis, kaunselor, dan pencipta KKDI yang telah membantu ramai wanita memahami diri, hubungan, dan luka hati dengan lebih jernih.</p>
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
                                'Buku ini buat saya lebih faham cinta. Bahasanya santai, tetapi sangat menyentuh dan jelas.' => 'Nur A.',
                                'Setiap bab seperti ada refleksi yang betul-betul tepat pada waktunya.' => 'Syafiqah M.',
                                'Ilmu praktikal yang boleh terus digunakan bersama pasangan. Terima kasih Kak Kay.' => 'Hafizah R.',
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

            <section class="storefront-container">
                <div class="grid gap-6 xl:grid-cols-[1fr_1.05fr]">
                    <div class="storefront-card rounded-[2rem] p-6 sm:p-8">
                        <h2 class="font-display text-3xl text-store">Soalan Lazim</h2>
                        <div class="mt-6 space-y-3">
                            @foreach ([
                                'Buku ini sesuai untuk siapa?' => 'Sesuai untuk sesiapa yang ingin memahami diri, emosi, dan hubungan dengan lebih matang.',
                                'Adakah buku ini sesuai untuk yang baru bercinta?' => 'Ya. Buku ini membantu dari sudut refleksi diri dan asas komunikasi sihat sejak awal hubungan.',
                                'Berapa lama tempoh penghantaran?' => 'Biasanya 1 hingga 3 hari bekerja ke seluruh Malaysia.',
                            ] as $question => $answer)
                                <details class="rounded-[1.4rem] border border-store bg-[#fffaf6] p-4">
                                    <summary class="cursor-pointer list-none font-medium text-store">{{ $question }}</summary>
                                    <p class="mt-3 text-sm text-store-soft">{{ $answer }}</p>
                                </details>
                            @endforeach
                        </div>
                    </div>

                    <div class="storefront-soft-card flex flex-col gap-5 rounded-[2rem] px-6 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                        <div>
                            <div class="font-display text-3xl text-store sm:text-4xl">Bersedia untuk mencintai dengan ilmu?</div>
                            <p class="mt-3 text-store-soft">Dapatkan buku signature Kak Kay hari ini dan mulakan perjalanan cinta anda dengan lebih jelas, lembut, dan bermakna.</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button type="button" wire:click="buyNow" class="storefront-button-primary">Beli Sekarang</button>
                            <button type="button" wire:click="addToCart" class="storefront-button-secondary border-store-strong">Tambah ke Troli</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <div class="storefront-container">
            <x-footer />
        </div>
    </div>
</div>
    </section>

    <!-- SOCIAL PROOF -->
    <section class="relative pb-20">
      <div class="max-w-5xl mx-auto px-4 space-y-10">
        <div class="flex flex-col items-center gap-6 sm:flex-row sm:items-start rounded-[28px] border border-white/10 bg-white/5 p-8 backdrop-blur-sm">
          <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-purple-600 via-rose-500 to-orange-400 text-xl font-bold">KK</div>
          <div>
            <h3 class="text-lg font-semibold text-white">Ditulis oleh Kamalia Kamal (Kak Kay)</h3>
            <p class="text-sm uppercase tracking-[0.2em] text-white/50">Kaunselor &middot; Penulis &middot; Pencipta KKDI</p>
            <p class="mt-3 text-white/75 leading-relaxed">Kak Kay pernah di fasa hubungan yang rasa datar. Jadi dia mula dengan langkah kecil &mdash; satu teknik sehari. Buku ini adalah hasil dari perjalanan tu, supaya pasangan lain tak perlu cari sendiri.</p>
          </div>
        </div>
        <div class="space-y-4">
          <h2 class="font-display text-2xl text-white text-center">Pasangan yang dah cuba cakap apa?</h2>
          <div class="grid gap-6 md:grid-cols-2">
            @foreach ([
              ['quote' => 'Kami dah 8 tahun kahwin. Ingatkan dah normal la rasa "biasa". Lepas cuba 3 teknik je, suami tanya: "Awak okay ke? Awak lain macam... tapi saya suka."', 'name' => 'Nadia & Afiq'],
              ['quote' => 'Saya jenis tak reti nak express. Buku ni bagi saya skrip — ayat tu siap, tinggal cakap je. Pasangan saya nangis dengar. Dalam erti kata yang baik.', 'name' => 'Aisyah & Fathi'],
            ] as $testimonial)
              <figure class="rounded-[24px] border border-white/10 bg-white/5 p-6">
                <blockquote class="text-base leading-relaxed text-white/85">&ldquo;{{ $testimonial['quote'] }}&rdquo;</blockquote>
                <figcaption class="mt-4 text-sm text-white/50">&mdash; {{ $testimonial['name'] }}</figcaption>
              </figure>
            @endforeach
          </div>
          <div class="flex justify-center">
            <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-sm text-white/70">
              <span class="flex items-center gap-1 text-amber-300">
                ★★★★★
              </span>
              <span class="font-semibold text-white">4.95/5</span>
              <span class="h-4 w-px bg-white/20"></span>
              <span>112 ulasan</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- VALUE COMPARISON -->
    <section class="relative pb-14">
      <div class="max-w-3xl mx-auto px-4">
        <div class="overflow-hidden rounded-[28px] border border-white/10 bg-gradient-to-br from-white/[0.06] to-white/[0.02] p-8 backdrop-blur-sm">
          <p class="text-center text-sm uppercase tracking-[0.2em] text-white/40">Bandingkan sendiri</p>
          <div class="mt-6 flex flex-wrap items-end justify-center gap-8">
            <div class="text-center">
              <div class="text-lg text-white/40 line-through">RM200</div>
              <div class="text-xs text-white/40">Satu sesi kaunseling</div>
            </div>
            <div class="text-center">
              <div class="text-lg text-white/40 line-through">RM500+</div>
              <div class="text-xs text-white/40">Kursus hubungan online</div>
            </div>
            <div class="text-center">
              <div class="text-4xl font-bold text-white">RM50</div>
              <div class="text-xs font-medium text-pink-200">34 teknik, guna seumur hidup</div>
            </div>
          </div>
          <div class="mt-6 flex items-center justify-center gap-2 text-sm text-emerald-300/80">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
            Jaminan 30 hari wang dikembalikan &mdash; tiada risiko langsung
          </div>
          <div class="mt-6 flex justify-center">
            <button wire:click="addToCart" class="rounded-full bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 px-10 py-4 text-base font-semibold shadow-[0_16px_36px_rgba(236,72,153,0.35)] transition-transform duration-300 hover:scale-[1.02]">
              Tambah Ke Troli &mdash; RM50
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="relative pb-24">
      <div class="max-w-3xl mx-auto px-4">
        <h2 class="font-display text-2xl text-white text-center">Soalan Lazim</h2>
        <div class="mt-8 divide-y divide-white/10 rounded-[28px] border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
          @foreach ([
            ['q' => 'Teknik-teknik ni mahal ke nak buat?', 'a' => 'Tak langsung. Hampir semua guna bahan yang dah ada kat rumah. Buku ni direka untuk pasangan biasa, bukan yang nak berbelanja besar.'],
            ['q' => 'Kami dua-dua kerja, sempat ke?', 'a' => 'Kebanyakan teknik ambil 15–30 minit je. Ada yang 5 minit pun cukup. Direka untuk pasangan sibuk.'],
            ['q' => 'Kena ikut dari teknik 1 sampai 34?', 'a' => 'Tak perlu. Buka mana-mana muka surat, baca, dan terus buat. Lebih macam menu — pilih ikut mood.'],
            ['q' => 'Macam mana nak terima buku fizikal?', 'a' => 'Pos ke seluruh Malaysia setiap hari bekerja. Nombor tracking dihantar melalui WhatsApp atau emel.'],
          ] as $faq)
            <details class="group py-4">
              <summary class="flex cursor-pointer items-center justify-between text-left text-base font-semibold text-white">
                <span>{{ $faq['q'] }}</span>
                <span class="ml-4 flex h-7 w-7 items-center justify-center rounded-full border border-white/20 text-xs transition-transform duration-300 group-open:rotate-45">＋</span>
              </summary>
              <p class="mt-3 text-sm leading-relaxed text-white/70">{{ $faq['a'] }}</p>
            </details>
          @endforeach
        </div>
        <div class="mt-10 flex flex-col items-center gap-3">
          <button wire:click="addToCart" class="rounded-full bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 px-10 py-4 text-base font-semibold shadow-[0_16px_36px_rgba(236,72,153,0.35)] transition-transform duration-300 hover:scale-[1.02]">
            Tambah Ke Troli &mdash; RM50
          </button>
          <p class="mt-1 text-sm text-emerald-300/80">Jaminan 30 hari wang dikembalikan</p>
          <p class="text-xs uppercase tracking-[0.2em] text-white/50">Satu teknik malam ni. Satu perubahan esok pagi.</p>
        </div>
      </div>
    </section>
  </main>
  </div>
</div>
