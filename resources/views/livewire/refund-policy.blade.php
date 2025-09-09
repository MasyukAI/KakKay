<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.policy')]
#[Title('Dasar Pemulangan - Kak Kay')]
class extends Component {
    //
} ?>


<div class="container">
  <!-- NAVIGATION HEADER -->
  <header style="padding: 1.5rem 0;">
    <a href="/">
      <div class="brand">
        <div class="logo" aria-hidden="true"></div>
        <div>
          <h1>Kak Kay</h1>
          <div class="tagline text-xs sm:text-base">Counsellor • Therapist • KKDI Creator</div>
        </div>
      </div>
    </a>
    <div class="flex items-center gap-4 ml-auto">
      <div class="relative">
        <flux:button variant="primary" href="{{ route('cart') }}" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 shadow-lg">
          <flux:icon.shopping-bag class="h-6 w-6" />
          <span class="hidden sm:inline font-medium">Troli</span>
          <div class="absolute top-0 right-0">
            @livewire('cart-counter')
          </div>
        </flux:button>
      </div>
    </div>
  </header>

  <!-- HERO -->
  <header class="hero text-white relative overflow-hidden">
    <div class="max-w-4xl mx-auto px-4 pt-16 pb-20 text-center relative z-10">
      <div class="fade-in-up">
        <div class="policy-badge-glass relative inline-flex items-center gap-2 px-5 py-2 rounded-full font-bold uppercase tracking-wider text-white shadow-xl border-2 border-pink-200/60 backdrop-blur-md animate-badge-shimmer">
          <span class="w-1.5 h-1.5 rounded-full bg-rose shadow-inner"></span>
          <span class="z-10 text-sm md:text-base">Kepuasan Pelanggan</span>
          <span class="policy-badge-shimmer absolute left-0 top-0 h-full w-full rounded-full pointer-events-none"></span>
        </div>
        <h1 class="mt-8 font-display text-5xl md:text-7xl lg:text-8xl leading-[0.95] tracking-tight relative">
          <span class="relative inline-block">
            <span class="relative z-10 underline decoration-blush decoration-4 md:decoration-6 underline-offset-8 bg-gradient-to-r from-white via-pink-100 to-white bg-clip-text text-transparent drop-shadow-2xl text-4xl md:text-6xl lg:text-7xl">Dasar Pemulangan</span>
            <span class="absolute -inset-4 bg-gradient-to-r from-blush/30 via-white/40 to-rose/30 rounded-2xl -z-10 blur-xl animate-pulse"></span>
            <span class="absolute -inset-1 bg-gradient-to-r from-transparent via-white/20 to-transparent rounded-lg -z-5"></span>
            <span class="absolute left-1/2 top-full -translate-x-1/2 mt-2 w-1/2 h-4 bg-pink-400/30 blur-xl rounded-full opacity-60 animate-glow"></span>
          </span>
        </h1>
        <p class="mt-8 text-white/95 text-xl md:text-2xl leading-relaxed max-w-2xl mx-auto font-serif drop-shadow-lg">
          <span class="text-base md:text-lg">Kepuasan anda adalah keutamaan kami. Ketahui hak dan jaminan anda sebagai pelanggan yang dihargai.</span>
        </p>
      </div>
    </div>
    <!-- Decorative elements -->
    <div class="absolute top-10 left-10 w-20 h-20 bg-blush/10 rounded-full blur-xl"></div>
    <div class="absolute bottom-10 right-10 w-32 h-32 bg-orchid/10 rounded-full blur-2xl"></div>
  </header>

  <!-- CONTENT -->
  <main class="relative">
    <!-- Enhanced decorative elements -->
    <div class="decorative-blob absolute top-20 left-4 w-32 h-32 bg-gradient-to-r from-orchid/20 to-blush/20 rounded-full"></div>
    <div class="decorative-blob absolute top-40 right-8 w-24 h-24 bg-gradient-to-r from-rose/20 to-pink/20 rounded-full" style="animation-delay: -2s;"></div>
    <div class="decorative-blob absolute bottom-60 left-12 w-20 h-20 bg-gradient-to-r from-purple/15 to-magenta/15 rounded-full" style="animation-delay: -4s;"></div>
    <div class="decorative-blob absolute bottom-40 right-16 w-28 h-28 bg-gradient-to-r from-blush/15 to-rose/15 rounded-full" style="animation-delay: -1s;"></div>
    
    <section class="max-w-4xl mx-auto px-4 py-16">
      <div class="prose prose-lg max-w-none">
        <div class="rad-card rounded-2xl border p-8 mb-8 bg-gradient-to-r from-champagne/20 to-cream/20">
          <h2 class="font-display text-3xl text-maroon mb-4">Jaminan Kepuasan 100%</h2>
          <p class="text-lg text-slate-700 leading-relaxed">
            Kami yakin dengan kualiti dan keberkesanan produk kami. Jika anda tidak berpuas hati sepenuhnya, kami akan membantu anda dengan jaminan pemulangan yang adil dan mudah.
          </p>
        </div>

        <div class="space-y-8">
          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">1. Jaminan 7 Hari untuk Produk Digital</h2>
            <div class="text-slate-700 space-y-4">
              <p><strong>Untuk e-book dan produk digital:</strong></p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Anda boleh meminta pemulangan dalam <strong>7 hari</strong> selepas pembelian</li>
                <li>Cuba sekurang-kurangnya <strong>2 teknik</strong> yang diterangkan dalam buku</li>
                <li>Jika teknik tersebut langsung tidak membantu, hubungi kami untuk penyelesaian</li>
                <li>Pemulangan wang penuh akan diproses dalam 3-5 hari bekerja</li>
              </ul>
              
              <div class="mt-4 p-4 rounded-xl bg-rose/5 border border-rose/20">
                <p class="text-sm"><strong>Nota:</strong> Kami mahu hasil untuk rumah tangga anda. Jaminan ini membolehkan anda mencuba produk tanpa risiko.</p>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">2. Jaminan 14 Hari untuk Produk Fizikal</h2>
            <div class="text-slate-700 space-y-4">
              <p><strong>Untuk buku bercetak dan produk fizikal:</strong></p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Tempoh pemulangan <strong>14 hari</strong> dari tarikh penerimaan</li>
                <li>Produk mesti dalam keadaan baik seperti baru</li>
                <li>Kos pos pemulangan ditanggung oleh pelanggan</li>
                <li>Pemulangan wang akan diproses selepas produk diterima dan diperiksa</li>
              </ul>
              
              <div class="mt-4 p-4 rounded-xl bg-blush/5 border border-blush/20">
                <p class="text-sm"><strong>Pengecualian:</strong> Produk yang rosak ketika dihantar akan diganti percuma atau dipulangkan wang sepenuhnya.</p>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">3. Cara Meminta Pemulangan</h2>
            <div class="text-slate-700 space-y-4">
              <p><strong>Langkah mudah untuk pemulangan:</strong></p>
              <div class="grid md:grid-cols-3 gap-4 mt-4">
                <div class="text-center p-4 bg-white/60 rounded-xl border">
                  <div class="text-2xl mb-2">📧</div>
                  <p class="font-semibold text-maroon">1. Hubungi Kami</p>
                  <p class="text-sm">E-mel kepada kakkaylovesme@gmail.com dengan butiran pesanan</p>
                </div>
                <div class="text-center p-4 bg-white/60 rounded-xl border">
                  <div class="text-2xl mb-2">📝</div>
                  <p class="font-semibold text-maroon">2. Maklum Sebab</p>
                  <p class="text-sm">Beritahu kami sebab pemulangan untuk penambahbaikan</p>
                </div>
                <div class="text-center p-4 bg-white/60 rounded-xl border">
                  <div class="text-2xl mb-2">💰</div>
                  <p class="font-semibold text-maroon">3. Terima Refund</p>
                  <p class="text-sm">Wang dikembalikan ke kaedah pembayaran asal</p>
                </div>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">4. Maklumat Diperlukan</h2>
            <div class="text-slate-700 space-y-4">
              <p>Untuk memproses permintaan pemulangan dengan cepat, sila sediakan:</p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Nombor pesanan atau e-mel yang digunakan semasa pembelian</li>
                <li>Sebab pemulangan (untuk membantu kami menambah baik)</li>
                <li>Untuk produk fizikal: gambar produk dalam keadaan semasa</li>
                <li>Maklumat akaun untuk pemulangan wang</li>
              </ul>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">5. Tempoh Pemprosesan</h2>
            <div class="text-slate-700 space-y-4">
              <div class="grid md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-champagne/20 border border-champagne/30">
                  <h4 class="font-semibold text-maroon mb-2">Produk Digital</h4>
                  <ul class="text-sm space-y-1">
                    <li>• Maklum balas dalam 24 jam</li>
                    <li>• Pemprosesan 1-2 hari bekerja</li>
                    <li>• Pemulangan wang 3-5 hari bekerja</li>
                  </ul>
                </div>
                <div class="p-4 rounded-xl bg-cream/20 border border-cream/30">
                  <h4 class="font-semibold text-maroon mb-2">Produk Fizikal</h4>
                  <ul class="text-sm space-y-1">
                    <li>• Maklum balas dalam 24 jam</li>
                    <li>• Pemprosesan selepas terima produk</li>
                    <li>• Pemulangan wang 5-7 hari bekerja</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">6. Pengecualian</h2>
            <div class="text-slate-700 space-y-4">
              <p>Pemulangan tidak boleh diproses untuk:</p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Permintaan selepas tempoh jaminan tamat</li>
                <li>Produk yang rosak disebabkan penyalahgunaan</li>
                <li>Produk digital yang telah dikongsi atau diedarkan</li>
                <li>Permintaan tanpa sebab yang munasabah</li>
              </ul>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6 bg-gradient-to-r from-rose/10 to-blush/10">
            <h2 class="font-display text-2xl text-maroon mb-4">Komitmen Kami</h2>
            <div class="text-slate-700 space-y-4">
              <p class="text-lg font-medium">Kami bukan sekadar menjual buku, tetapi mahu melihat rumah tangga anda bahagia.</p>
              <p>Jika teknik dalam buku kami tidak membantu, kami akan:</p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Memberikan panduan tambahan secara percuma</li>
                <li>Mencadangkan teknik alternatif yang sesuai</li>
                <li>Memproses pemulangan dengan adil jika memang tidak sesuai</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- BACK TO HOME -->
  <section class="max-w-4xl mx-auto px-4 pb-16">
    <div class="text-center">
  <a href="{{ route('home') }}" class="btn primary rounded-full px-8 py-3 font-semibold text-white shadow-glow hover:scale-105 transition-all duration-300">
        Kembali ke Laman Utama
      </a>
    </div>
  </section>

  <div class="container">
        <x-footer />
    </div>
</div>
