<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Component;
use AIArmada\Products\Models\Product;
use AIArmada\Cart\Facades\Cart;
use Filament\Notifications\Notification;

new
#[Layout('components.layouts.pages')]
#[Title('34 Teknik Bercinta Dengan Pasangan')]
class extends Component {
    public function addToCart()
    {
        $product = Product::where('slug', 'cara-bercinta')->first();

        if (! $product) {
            Notification::make()
                ->title('Produk Tidak Dijumpai')
                ->body('Produk yang diminta tidak wujud.')
                ->danger()
                ->icon('heroicon-o-exclamation-triangle')
                ->iconColor('danger')
                ->persistent()
                ->send();

            return;
        }

        Cart::add(
            (string) $product->id,
            $product->name,
            $product->price,
            1,
            [
                'slug' => $product->slug,
                'category' => $product->categories->first()?->name ?? 'books',
            ]
        );

        Notification::make()
            ->title('Berjaya Ditambah!')
            ->body('Produk telah ditambah ke keranjang!')
            ->success()
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->duration(3000)
            ->send();

        $this->dispatch('cart-updated');

        return $this->redirect('/cart', navigate: true);
    }
} ?>

<div class="relative isolate overflow-hidden bg-[#0f0218] text-white ambient-glow-pages">

  @php
      $cartQuantity = Cart::getTotalQuantity();
  @endphp

  <div class="relative z-10">
  <x-brand-header :cart-quantity="$cartQuantity" />

  <main class="relative">
    <!-- HERO -->
    <section class="pt-20 pb-16">
      <div class="max-w-7xl mx-auto px-4">
        <div class="grid items-center gap-12 lg:grid-cols-[minmax(0,1fr)_380px]">
          <div class="space-y-6">
            <h1 class="font-display text-4xl leading-[0.95] tracking-tight text-white sm:text-5xl lg:text-6xl">
              Cinta tak perlu <span class="bg-gradient-to-r from-pink-300 via-rose-400 to-orange-200 bg-clip-text text-transparent">grand gesture.</span><br>
              Cuma perlu usaha yang jujur.
            </h1>
            <p class="max-w-xl text-lg leading-relaxed text-white/85">
              <strong class="text-pink-200">34 Teknik Bercinta Dengan Pasangan</strong> &mdash; panduan aksi ditulis oleh Kak Kay dari pengalaman sebenar bersama ratusan pasangan. Buka mana-mana muka surat, baca 5 minit, dan buat malam ni.
            </p>
            <div class="flex items-center gap-4">
              <span class="text-3xl font-bold text-white">RM50</span>
              <div class="h-6 w-px bg-white/20"></div>
              <span class="flex items-center gap-2 text-sm text-emerald-300/80">
                <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
                Jaminan 30 hari wang dikembalikan
              </span>
            </div>
            <div class="flex flex-wrap items-center gap-4">
              <button wire:click="addToCart" class="rounded-full bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 px-8 py-4 text-base font-semibold shadow-[0_8px_20px_rgba(236,72,153,0.35)] transition-all duration-300 hover:scale-[1.02] hover:shadow-[0_16px_36px_rgba(236,72,153,0.45)]">
                Tambah Ke Troli &mdash; RM50
              </button>
              <a href="#inside" class="rounded-full border border-white/30 px-8 py-4 text-base font-semibold backdrop-blur-sm transition-all duration-300 hover:border-white/60 hover:bg-white/10">
                Tengok Apa Dalam Ni
              </a>
            </div>
          </div>
          <div class="relative flex justify-center">
            <div class="group relative">
              <div class="absolute -inset-6 rounded-[36px] bg-gradient-to-br from-pink-400/40 via-fuchsia-500/30 to-purple-500/40 opacity-75 blur-2xl transition-all duration-300 group-hover:opacity-100"></div>
              <div class="relative overflow-hidden rounded-[32px] border border-white/15 bg-white/10 p-6 backdrop-blur-sm shadow-[0_18px_48px_rgba(15,3,37,0.45)] transition-all duration-300 group-hover:-translate-y-1">
                <img src="{{ asset('storage/images/cover/cara-bercinta.webp') }}" alt="34 Teknik Bercinta" class="w-full max-w-xs rounded-[26px] object-cover shadow-[0_16px_36px_rgba(17,0,34,0.55)]">
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- TRUST STRIP -->
    <section class="pb-20">
      <div class="max-w-4xl mx-auto px-4">
        <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-3 rounded-full border border-white/10 bg-white/5 px-6 py-4 text-sm text-white/70 backdrop-blur-sm">
          @foreach ([
            ['icon' => '🌸', 'text' => 'Kos rendah, kesan tinggi'],
            ['icon' => '🕌', 'text' => 'Patuh syariah'],
            ['icon' => '⏰', 'text' => '15 minit dah cukup'],
            ['icon' => '📖', 'text' => '34 aksi siap pakai'],
          ] as $badge)
            <span class="flex items-center gap-2">
              <span>{{ $badge['icon'] }}</span>
              {{ $badge['text'] }}
            </span>
          @endforeach
        </div>
      </div>
    </section>

    <!-- THE SHIFT -->
    <section id="learn" class="relative pb-20">
      <div class="max-w-5xl mx-auto px-4">
        <div class="overflow-hidden rounded-[36px] border border-white/10 bg-white/5 p-10 backdrop-blur-sm shadow-[0_18px_48px_rgba(12,5,24,0.4)]">
          <div class="mx-auto max-w-3xl space-y-8">
            <h2 class="font-display text-3xl sm:text-4xl text-white text-center">
              Kebanyakan pasangan tak putus sebab tak <span class="bg-gradient-to-r from-pink-300 via-rose-400 to-orange-200 bg-clip-text text-transparent">sayang</span>.<br>
              Tapi sebab lupa cara nak tunjuk.
            </h2>
            <p class="text-center text-lg leading-relaxed text-white/80">
              Hari-hari sama. Sembang pasal kerja je. Rindu rasa macam dulu tapi tak tahu cara nak dapat balik. Buku ini bagi kau 34 aksi kecil yang boleh terus dibuat &mdash; tanpa perlu jadi orang lain. Cuma hadir, dengan niat.
            </p>
            <div class="grid gap-4 sm:grid-cols-3">
              <div class="rounded-2xl border border-white/10 bg-white/10 p-5 text-center">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-sm font-bold">1</span>
                <div class="mt-3 font-semibold text-white">Faham</div>
                <p class="mt-1 text-xs text-white/65">Bacaan 5 minit &mdash; terus ke point, kenapa ia penting.</p>
              </div>
              <div class="rounded-2xl border border-white/10 bg-white/10 p-5 text-center">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-sm font-bold">2</span>
                <div class="mt-3 font-semibold text-white">Buat</div>
                <p class="mt-1 text-xs text-white/65">Skrip, aktiviti, atau aksi yang siap untuk kau ikut malam ni.</p>
              </div>
              <div class="rounded-2xl border border-white/10 bg-white/10 p-5 text-center">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-sm font-bold">3</span>
                <div class="mt-3 font-semibold text-white">Refleksi</div>
                <p class="mt-1 text-xs text-white/65">Soalan ringkas untuk sembang dan rapat balik dengan pasangan.</p>
              </div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-gradient-to-r from-white/10 to-transparent p-5 text-center text-sm text-white/60 uppercase tracking-[0.2em]">
              Aksi kecil + niat ikhlas + konsisten = cinta yang hidup
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- INSIDE -->
    <section id="inside" class="relative pb-20">
      <div class="max-w-6xl mx-auto px-4">
        <div class="relative overflow-hidden rounded-[40px] border border-white/10 bg-white text-slate-900">
          <div class="absolute inset-0 bg-gradient-to-br from-rose-50 via-white to-pink-100/60"></div>
          <div class="relative px-6 py-14 sm:px-12 sm:py-16">
            <div class="text-center">
              <h2 class="font-display text-4xl text-transparent bg-gradient-to-r from-rose-500 via-fuchsia-500 to-purple-500 bg-clip-text">Apa sebenarnya dalam buku ni?</h2>
              <p class="mt-4 text-lg text-slate-600">34 teknik yang boleh dibuka kat mana-mana muka surat. Pilih yang kau rasa perlu, baca, dan buat.</p>
            </div>
            <div class="mt-12 grid gap-6 md:grid-cols-3">
              @foreach ([
                ['title' => 'Masak, Kejutan & Kenangan', 'desc' => 'Menu nostalgia, teh tarik tengah malam, nota kecil dalam beg kerja, voice note random — benda simple yang buat pasangan senyum.', 'items' => ['Masak sama-sama lepas Isyak', 'Nota cinta dalam poket baju', 'Hadiah bawah RM10 tapi personal']],
                ['title' => 'Sentuhan, Doa & Sembang', 'desc' => 'Genggam tangan masa doa. Soalan yang buka hati, bukan gaduh. Skrip minta maaf yang betul-betul menyejukkan.', 'items' => ['Pelukan 20 saat sebelum tidur', '3 pujian spesifik sehari', 'Replay kenangan manis 10 minit']],
                ['title' => 'Date Night & Konsisten', 'desc' => 'Picnic atas lantai, movie night tanpa scroll phone, dan cara mudah jejak progress tanpa rasa macam kerja.', 'items' => ['Picnic dalam bilik', 'Refleksi 3 baris setiap minggu', 'Cabaran 7 hari pertama']],
              ] as $chapter)
                <div class="group relative overflow-hidden rounded-[28px] border border-rose-200/60 bg-white/80 p-6 shadow-[0_14px_28px_rgba(244,114,182,0.15)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_18px_42px_rgba(244,114,182,0.2)]">
                  <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-gradient-to-br from-rose-300/30 via-fuchsia-200/30 to-orange-200/30 blur-2xl"></div>
                  <div class="relative">
                    <h3 class="font-semibold text-maroon text-xl">{{ $chapter['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $chapter['desc'] }}</p>
                    <ul class="mt-4 space-y-1 text-sm text-slate-500">
                      @foreach ($chapter['items'] as $item)
                        <li class="flex gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-rose-400"></span>{{ $item }}</li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              @endforeach
            </div>
            <div class="mt-12 flex flex-col items-center gap-3">
              <button wire:click="addToCart" class="rounded-full bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 px-10 py-4 text-lg font-semibold text-white shadow-[0_16px_36px_rgba(236,72,153,0.35)] transition-transform duration-300 hover:scale-[1.02]">
                Dapatkan Buku Ini &mdash; RM50
              </button>
              <p class="text-sm text-slate-500">Bonus: Template nota cinta &amp; skrip pujian harian &middot; Jaminan 30 hari</p>
            </div>
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
