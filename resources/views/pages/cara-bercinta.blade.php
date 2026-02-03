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

<div class="relative isolate overflow-hidden bg-[#0f0218] text-white">
  <div class="pointer-events-none absolute -top-40 -left-32 h-[480px] w-[480px] rounded-full bg-gradient-to-br from-pink-500/40 via-purple-500/30 to-rose-500/40 blur-2xl"></div>
  <div class="pointer-events-none absolute top-1/3 -right-24 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-fuchsia-500/30 via-rose-500/20 to-orange-400/30 blur-2xl"></div>
  <div class="pointer-events-none absolute bottom-[-240px] left-1/2 h-[460px] w-[460px] -translate-x-1/2 rounded-full bg-gradient-to-br from-purple-600/30 via-indigo-500/20 to-pink-400/40 blur-2xl"></div>

  @php
      $cartQuantity = Cart::getTotalQuantity();
  @endphp

  <div class="relative z-10">
  <x-brand-header :cart-quantity="$cartQuantity" />

  <main class="relative">
    <!-- HERO -->
    <section class="pt-20 pb-24 lg:pb-32">
      <div class="max-w-7xl mx-auto px-4">
        <div class="grid items-center gap-14 lg:grid-cols-[minmax(0,1fr)_420px]">
          <div class="space-y-6">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-5 py-2 text-sm font-semibold text-white/90 shadow-[0_0_40px_rgba(244,114,182,0.35)]">
              <span class="h-2 w-2 rounded-full bg-gradient-to-r from-rose-400 to-fuchsia-400 shadow-[0_0_16px_rgba(244,114,182,0.7)]"></span>
              Pelancaran Edisi Istimewa
            </div>
            <h1 class="font-display text-4xl leading-[0.95] tracking-tight text-white sm:text-5xl lg:text-6xl">
              "Macam ni rupanya <span class="relative inline-block"><span class="bg-gradient-to-r from-pink-300 via-rose-400 to-orange-200 bg-clip-text text-transparent">cara nak bercinta</span><span class="absolute -left-4 -top-2 h-12 w-12 -rotate-6 rounded-full border border-white/20"></span></span>… <span class="bg-gradient-to-r from-rose-400 via-fuchsia-400 to-purple-400 bg-clip-text text-transparent">mudahnya lahai!</span>"
            </h1>
            <p class="max-w-2xl text-lg leading-relaxed text-white/90 sm:text-xl">
              <strong class="text-pink-200">34 Teknik Bercinta Dengan Pasangan</strong> oleh <strong class="text-pink-100">Kamalia Kamal (Kak Kay)</strong> ialah panduan praktikal untuk hidupkan kembali rasa — tanpa bajet besar dan tanpa drama. Hanya usaha kecil yang manis, konsisten, dan halal.
            </p>
            <div class="flex flex-wrap items-center gap-4">
              <button wire:click="addToCart" class="rounded-full bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 px-8 py-4 text-base font-semibold shadow-[0_8px_20px_rgba(236,72,153,0.35)] transition-all duration-300 hover:scale-[1.02] hover:shadow-[0_16px_36px_rgba(236,72,153,0.45)]">
                Beli Hari Ini
              </button>
              <a href="#learn" class="rounded-full border border-white/30 px-8 py-4 text-base font-semibold backdrop-blur-sm transition-all duration-300 hover:border-white/60 hover:bg-white/10">
                Baca Lagi
              </a>
              <div class="hidden sm:flex items-center gap-3 rounded-full border border-white/20 bg-white/5 px-4 py-3 text-xs uppercase tracking-[0.18em] text-white/70">
                <span class="h-2 w-2 rounded-full bg-green-400 shadow-[0_0_12px_rgba(74,222,128,0.8)]"></span>
                Ready Stock Fizikal & Digital
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
              @foreach ([
                ['text' => 'Mudah & Murah', 'icon' => '🌸'],
                ['text' => 'Mesra Syariah', 'icon' => '🕌'],
                ['text' => 'Sesuai Pasangan Sibuk', 'icon' => '⏰'],
                ['text' => '34 Teknik Praktikal', 'icon' => '📖'],
              ] as $feature)
                <div class="relative overflow-hidden rounded-2xl border border-white/15 bg-white/5 px-4 py-5 shadow-[0_8px_20px_rgba(7,5,15,0.25)] transition-all duration-300 hover:-translate-y-1 hover:bg-white/10 hover:shadow-[0_14px_28px_rgba(236,72,153,0.28)]">
                  <div class="absolute -top-10 right-2 h-20 w-20 rounded-full bg-gradient-to-br from-pink-400/20 to-purple-400/20 blur-lg"></div>
                  <div class="relative flex flex-col gap-2">
                    <span class="text-2xl">{{ $feature['icon'] }}</span>
                    <span class="text-sm font-medium text-white/85">{{ $feature['text'] }}</span>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
          <div class="relative flex justify-center lg:justify-end">
            <div class="group relative">
              <div class="absolute -inset-6 rounded-[36px] bg-gradient-to-br from-pink-400/40 via-fuchsia-500/30 to-purple-500/40 opacity-75 blur-2xl transition-all duration-300 group-hover:opacity-100"></div>
              <div class="relative overflow-hidden rounded-[32px] border border-white/15 bg-white/10 p-6 backdrop-blur-sm shadow-[0_18px_48px_rgba(15,3,37,0.45)] transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-[0_22px_64px_rgba(236,72,153,0.35)]">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white/80">
                  <span class="h-1.5 w-1.5 rounded-full bg-pink-300"></span>
                  Special Edition Cover
                </div>
                <div class="relative mx-auto flex justify-center">
                  <div class="absolute inset-0 rounded-[28px] border border-white/20"></div>
                  <img src="{{ asset('storage/images/cover/cara-bercinta.webp') }}" alt="34 Teknik Bercinta" class="relative w-full max-w-xs rounded-[26px] object-cover shadow-[0_16px_36px_rgba(17,0,34,0.55)]">
                </div>
                <div class="mt-6 flex flex-col gap-3 rounded-2xl bg-white/10 p-4 text-sm text-white/80">
                  <div class="flex items-center gap-3">
                    <span class="rounded-full bg-pink-400/20 px-3 py-1 text-xs font-semibold text-pink-100">Bonus</span>
                    Template sticky note cinta + senarai lagu nostalgia
                  </div>
                  <div class="flex items-center gap-3">
                    <span class="rounded-full bg-purple-400/20 px-3 py-1 text-xs font-semibold text-purple-100">Format</span>
                    Digital (PDF) & Fizikal (Cetak premium)
                  </div>
                </div>
              </div>
              <div class="absolute -right-16 -bottom-10 hidden w-44 rotate-12 rounded-2xl border border-white/10 bg-white/10 p-4 text-xs text-white/80 shadow-[0_12px_30px_rgba(15,3,37,0.35)] backdrop-blur-sm lg:block">
                <div class="font-semibold text-white">💬 Kak Kay tips</div>
                <p class="mt-1 leading-snug">“Usaha kecil. Hati besar. Cuba satu teknik malam ini dan rasa beza esok pagi.”</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- WHY IT WORKS -->
    <section id="learn" class="relative pb-20">
      <div class="max-w-6xl mx-auto px-4">
        <div class="overflow-hidden rounded-[36px] border border-white/10 bg-white/5 p-10 backdrop-blur-sm shadow-[0_18px_48px_rgba(12,5,24,0.4)]">
          <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-6">
              <h2 class="font-display text-3xl sm:text-4xl text-white">Kalau ikut perasaan, cinta surut. Kalau ikut <span class="bg-gradient-to-r from-pink-300 via-rose-400 to-orange-200 bg-clip-text text-transparent">usaha</span>, cinta subur.</h2>
              <p class="text-base leading-relaxed text-white/85 sm:text-lg">
                Itu falsafah buku ini. <strong class="text-pink-200">34 Teknik Bercinta</strong> bukan teori psikologi yang rumit — ia ialah aksi kecil, lembut, dan halal yang menyentuh hati pasangan tanpa paksaan. Malam hujan, masak mi segera nostalgia. Malam biasa, picnic dalam bilik. Pagi Sabtu, tampal sticky note cinta di cermin. Kecil… tapi berkesan.
              </p>
              <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                  <h3 class="text-lg font-semibold text-white">Selepas 7 Hari</h3>
                  <ul class="mt-3 space-y-2 text-sm text-white/80">
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 rounded-full bg-pink-300"></span><span>Lebih kerap senyum bila memandang pasangan.</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 rounded-full bg-pink-300"></span><span>Perbualan jadi lembut — kurang isu, lebih rasa.</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 rounded-full bg-pink-300"></span><span>Suasana rumah hangat, anak-anak pun rasa aman.</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 rounded-full bg-pink-300"></span><span>Anda percaya: “Bahagia sebenarnya boleh diusahakan.”</span></li>
                  </ul>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                  <h3 class="text-lg font-semibold text-white">Kenapa Menjadi</h3>
                  <p class="mt-3 text-sm leading-relaxed text-white/80">
                    Kita tak tunggu kejutan mewah. Kita bina <em>micro-moments</em> — aksi kecil tapi konsisten — yang membisikkan “awak penting setiap hari”. Bezanya antara hubungan yang menunggu bahagia dan hubungan yang membina bahagia.
                  </p>
                  <div class="mt-4 rounded-2xl border border-white/10 bg-gradient-to-r from-white/10 to-transparent p-4 text-xs uppercase tracking-[0.2em] text-white/60">
                    Praktik + Refleksi + Doa = Ritual Cinta Harian
                  </div>
                </div>
              </div>
            </div>
            <div class="space-y-5">
              <div class="rounded-[28px] border border-white/10 bg-gradient-to-br from-white/15 via-rose-200/10 to-purple-200/10 p-6 text-sm text-white/85 shadow-[0_10px_24px_rgba(15,3,37,0.4)]">
                <h3 class="text-lg font-semibold text-white">Struktur 3 Langkah</h3>
                <p class="mt-2 text-white/75">Setiap teknik disusun supaya anda boleh:</p>
                <ol class="mt-4 space-y-3">
                  <li class="flex gap-3"><span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">1</span><span>Baca ringkas (teks 5 minit) untuk faham situasi.</span></li>
                  <li class="flex gap-3"><span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">2</span><span>Buat malam ini — resipi, skrip, atau aktiviti siap pakai.</span></li>
                  <li class="flex gap-3"><span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">3</span><span>Refleksi manis supaya rutin cinta jadi tabiat.</span></li>
                </ol>
              </div>
              <div class="flex flex-col gap-4 rounded-[28px] border border-white/10 bg-white/5 p-6 text-sm text-white/80">
                <div class="flex items-center gap-3">
                  <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-r from-pink-400 to-purple-500 text-lg">💌</span>
                  <div>
                    <div class="font-semibold text-white">Nota cinta siap cetak</div>
                    <p class="text-xs text-white/70">Bonus template untuk pasangan yang suka kejutan kecil.</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-r from-fuchsia-400 to-rose-500 text-lg">🎧</span>
                  <div>
                    <div class="font-semibold text-white">Lagu nostalgia pilihan</div>
                    <p class="text-xs text-white/70">Playlist kurasi Kak Kay untuk temani saat manis.</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-r from-rose-400 to-orange-400 text-lg">🤲</span>
                  <div>
                    <div class="font-semibold text-white">Doa & skrip manis</div>
                    <p class="text-xs text-white/70">Skrip minta maaf, doa bersama, dan kata-kata pujian.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- WHAT'S INSIDE -->
    <section class="relative pb-20">
      <div class="max-w-7xl mx-auto px-4">
        <div class="relative overflow-hidden rounded-[40px] border border-white/10 bg-white text-slate-900">
          <div class="absolute inset-0 bg-gradient-to-br from-rose-50 via-white to-pink-100/60"></div>
          <div class="relative px-6 py-14 sm:px-12 sm:py-16">
            <div class="text-center">
              <h2 class="font-display text-4xl text-transparent bg-gradient-to-r from-rose-500 via-fuchsia-500 to-purple-500 bg-clip-text">Dalam buku ini, anda akan temui…</h2>
              <p class="mt-4 text-lg text-slate-600">34 teknik dibahagikan kepada kategori mudah. Setiap teknik lengkap dengan cadangan waktu, pantang ringkas dan tujuan — boleh dipraktik malam ini juga.</p>
            </div>
            <div class="mt-12 grid gap-6 lg:grid-cols-3">
              @foreach ([
                ['title' => 'Makanan & Nostalgia', 'desc' => 'Masak menu kenangan, pilih kudapan zaman bercinta, minum teh tarik berdua.', 'items' => ['Menu Nostalgia (Teknik #1)', 'Teh Tarik Tengah Malam', 'Kuih Suku Hati']],
                ['title' => 'Rumah Jadi Dating Spot', 'desc' => 'Picnic dalam bilik, filem lembut tanpa telefon, lampu malap & bantal tambahan.', 'items' => ['Picnic Dalam Rumah', 'Malam Filem Tanpa Skrin Kedua', 'Sarapan Di Lantai']],
                ['title' => 'Sentuhan, Doa & Bahasa Cinta', 'desc' => 'Genggam tangan, doa berdua, pujian spesifik yang menguatkan.', 'items' => ['Doa Pegang Tangan', '3 Pujian Sehari', 'Pelukan 20 Saat']],
                ['title' => 'Cerita & Komunikasi Manis', 'desc' => 'Soalan pembuka hati, sesi replay memori dan skrip minta maaf menyejukkan.', 'items' => ['Soalan “Apa Paling Manis Hari Ini?”', 'Replay Memori 10 Minit', 'Skrip “Maaf Sayang”']],
                ['title' => 'Kejutan Murah Bermakna', 'desc' => 'Sticky note cinta, nota di bekal, voice note kelakar — kos rendah, kesan tinggi.', 'items' => ['Sticky Note Cinta', 'Voice Note 30 Saat', 'Hadiah RM10 Yang Ikhlas']],
                ['title' => 'Audit Halus & Rutin', 'desc' => 'Jejak tarikh, refleksi tiga baris dan kalendar “Kita” untuk cinta konsisten.', 'items' => ['Audit Halus 3 Baris', 'Kalendar Kita', 'Hadiah Diri: Teruskan 7 Hari']],
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
                Beli Sekarang — Mulakan Ritual Cinta
              </button>
              <p class="text-sm text-slate-500">✨ Mulakan perjalanan cinta yang lebih bermakna</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ABOUT THE AUTHOR -->
    <section class="relative pb-20">
      <div class="max-w-6xl mx-auto px-4">
        <div class="grid gap-10 lg:grid-cols-[0.65fr_0.35fr]">
          <div class="relative overflow-hidden rounded-[36px] border border-white/10 bg-white/10 p-10 backdrop-blur-sm shadow-[0_18px_48px_rgba(17,6,34,0.45)]">
            <div class="absolute -top-20 -right-24 h-60 w-60 rounded-full bg-gradient-to-br from-rose-400/20 via-purple-500/20 to-indigo-400/20 blur-2xl"></div>
            <div class="relative">
              <h2 class="font-display text-4xl text-white">Ditulis dengan hati oleh <span class="bg-gradient-to-r from-pink-300 via-rose-400 to-purple-400 bg-clip-text text-transparent">Kamalia Kamal</span> (Kak Kay)</h2>
              <p class="mt-6 text-lg leading-relaxed text-white/80">Kak Kay pernah berada di fasa <em class="text-pink-200">letih menunggu rasa</em>. Jadi beliau pilih jalan mudah: <strong class="text-pink-100 font-semibold">buat dulu</strong> walau kecil. Buku ini menceritakan pengalaman sebenar itu supaya pasangan lain dapat rasa lega yang sama: bahagia itu boleh diusahakan.</p>
              <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/10 p-4 text-center text-sm text-white/80">
                  <div class="text-2xl">💌</div>
                  Tulis nota comel
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/10 p-4 text-center text-sm text-white/80">
                  <div class="text-2xl">🍜</div>
                  Masak mi telur goyang
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/10 p-4 text-center text-sm text-white/80">
                  <div class="text-2xl">🤲</div>
                  Genggam tangan doa
                </div>
              </div>
              <div class="mt-8 rounded-2xl border border-white/10 bg-gradient-to-r from-white/10 to-transparent p-6 text-lg font-medium italic text-white/85">
                "Rumah kami kembali hangat. Setiap teknik direka supaya pasangan lain pun boleh rasa lega yang sama — <strong class="not-italic text-white">bahagia itu boleh diusahakan</strong>."
              </div>
            </div>
          </div>
          <div class="relative flex items-center justify-center">
            <div class="group relative w-full max-w-xs">
              <div class="absolute -inset-4 rounded-[32px] bg-gradient-to-br from-rose-400/40 via-fuchsia-400/30 to-purple-500/40 blur-2xl opacity-80 transition duration-300 group-hover:opacity-100"></div>
              <div class="relative rounded-[32px] border border-white/15 bg-white/10 p-10 text-center backdrop-blur-sm shadow-[0_16px_36px_rgba(15,3,37,0.45)]">
                <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-purple-600 via-rose-500 to-orange-400 text-2xl font-bold">KK</div>
                <h3 class="mt-6 text-xl font-semibold text-white">Kamalia Kamal</h3>
                <p class="text-sm uppercase tracking-[0.24em] text-white/60">Kaunselor & Penulis</p>
                <blockquote class="mt-6 text-white font-medium italic">“Usaha kecil. Hati besar.”</blockquote>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- TESTIMONIALS -->
    <section class="relative pb-20">
      <div class="max-w-6xl mx-auto px-4">
        <div class="overflow-hidden rounded-[36px] border border-white/10 bg-white/10 p-10 backdrop-blur-sm shadow-[0_18px_48px_rgba(12,5,24,0.4)]">
          <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h2 class="font-display text-3xl text-white">Apa kata pasangan yang mencuba</h2>
              <p class="mt-3 text-white/75">Cerita sebenar pasangan yang jadikan 34 teknik ini sebagai ritual baru.</p>
            </div>
            <div class="inline-flex items-center gap-3 rounded-full bg-white/10 px-4 py-2 text-xs uppercase tracking-[0.2em] text-white/60">
              <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_12px_rgba(74,222,128,0.8)]"></span>
              4.95/5 Rating (112 pasangan)
            </div>
          </div>
          <div class="mt-10 grid gap-6 md:grid-cols-2">
            @foreach ([
              ['quote' => 'Tak sangka picnic dalam bilik pun boleh rasa macam honeymoon. Anak-anak tidur, kami pasang playlist nostalgia — lembut sangat rasanya.', 'name' => 'Nadia & Afiq'],
              ['quote' => 'Kami pasangan sibuk. Teknik 15 minit lepas Isyak tu paling membantu. Komunikasi jadi lebih ehwal hati, bukan pasal kerja lagi.', 'name' => 'Yana & Rahman'],
              ['quote' => 'Sticky note cinta nampak remeh tapi pasangan saya senyum panjang. Buku ni ajar disiplin sayang yang kecil-kecil.', 'name' => 'Aisyah & Fathi'],
              ['quote' => 'Audit halus tu power, kami boleh sembang tanpa defensive. Rasanya macam ada coach cinta dalam rumah.', 'name' => 'Farhan & Mira'],
            ] as $testimonial)
              <figure class="rounded-[28px] border border-white/15 bg-white/10 p-6 text-white/80 shadow-[0_14px_32px_rgba(12,5,24,0.35)]">
                <blockquote class="text-lg leading-relaxed text-white/85">“{{ $testimonial['quote'] }}”</blockquote>
                <figcaption class="mt-4 text-sm text-white/60">— {{ $testimonial['name'] }}</figcaption>
              </figure>
            @endforeach
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="relative pb-24">
      <div class="max-w-4xl mx-auto px-4">
        <div class="text-center">
          <h2 class="font-display text-3xl text-white">Soalan Lazim</h2>
          <p class="mt-3 text-white/70">Masih ragu? Ini jawapan kepada soalan paling kerap kami terima.</p>
        </div>
        <div class="mt-10 divide-y divide-white/10 rounded-[32px] border border-white/10 bg-white/5 p-6 backdrop-blur-sm shadow-[0_18px_42px_rgba(12,5,24,0.35)]">
          @foreach ([
            ['q' => 'Adakah tekniknya mahal?', 'a' => 'Tidak. Fokus pada usaha kecil yang jujur — nota cinta, genggam tangan, picnic dalam bilik — bukan kejutan mewah.'],
            ['q' => 'Sesuai untuk pasangan sibuk?', 'a' => 'Ya. Banyak teknik siap dalam 15–30 minit, mudah diselit dalam rutin harian tanpa menambah stres.'],
            ['q' => 'Perlu ikut turutan #1 ke #34?', 'a' => 'Tidak perlu. Pilih mana-mana teknik, baca bersama dan praktik. Buku ini boleh dibuka bila-bila masa sebagai pengingat cinta.'],
            ['q' => 'Bagaimana penghantaran edisi fizikal?', 'a' => 'Kami pos seluruh Malaysia setiap hari bekerja. Nombor penjejakan akan dihantar melalui WhatsApp atau emel.'],
          ] as $faq)
            <details class="group py-4">
              <summary class="flex cursor-pointer items-center justify-between text-left text-lg font-semibold text-white">
                <span>{{ $faq['q'] }}</span>
                <span class="ml-4 flex h-8 w-8 items-center justify-center rounded-full border border-white/20 text-sm transition-transform duration-300 group-open:rotate-45">＋</span>
              </summary>
              <p class="mt-3 text-sm leading-relaxed text-white/75">{{ $faq['a'] }}</p>
            </details>
          @endforeach
        </div>
        <div class="mt-10 flex flex-col items-center gap-3">
          <button wire:click="addToCart" class="rounded-full bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 px-10 py-4 text-base font-semibold shadow-[0_16px_36px_rgba(236,72,153,0.35)] transition-transform duration-300 hover:scale-[1.02]">
            Tambah Ke Troli — Bahagia Diusahakan
          </button>
          <p class="text-xs uppercase tracking-[0.3em] text-white/50">Cinta jadi budaya, bukan projek sekali-sekala</p>
        </div>
      </div>
    </section>
  </main>
  </div>
</div>
