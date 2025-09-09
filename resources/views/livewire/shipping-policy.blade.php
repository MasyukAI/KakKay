<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.policy')]
#[Title('Dasar Penghantaran & Pengembalian - Kak Kay')]
class extends Component {
    //
} ?>

<div>
  <!-- NAVIGATION HEADER -->
  <div class="container">
    <header style="padding: 1.5rem 0;">
      <a wire:navigate href="/">
        <div class="brand">
          <div class="logo" aria-hidden="true"></div>
            <div>
              <h1>Kak Kay</h1>
              <div class="tagline">Counsellor • Therapist • KKDI Creator</div>
            </div>
        </div>
      </a>
      <div class="flex items-center gap-4 ml-auto">
        <div class="relative">
          <flux:button variant="primary" wire:navigate href="{{ route('cart') }}" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 shadow-lg">
            <flux:icon.shopping-bag class="h-6 w-6" />
            <span class="hidden sm:inline font-medium">Troli</span>
            <div class="absolute top-0 right-0">
              @livewire('cart-counter')
            </div>
          </flux:button>
        </div>
      </div>
    </header>
  </div>

  <!-- HERO -->
  <header class="hero text-white relative overflow-hidden">
    <div class="max-w-4xl mx-auto px-4 pt-16 pb-20 text-center relative z-10">
      <div class="fade-in-up">
        <div class="policy-badge-glass relative inline-flex items-center gap-2 px-5 py-2 rounded-full font-bold uppercase tracking-wider text-white shadow-xl border-2 border-pink-200/60 backdrop-blur-md animate-badge-shimmer">
          <span class="w-1.5 h-1.5 rounded-full bg-rose shadow-inner"></span>
          <span class="z-10 text-sm md:text-base">Penghantaran & Pengembalian</span>
          <span class="policy-badge-shimmer absolute left-0 top-0 h-full w-full rounded-full pointer-events-none"></span>
        </div>
        <h1 class="mt-8 font-display text-5xl md:text-7xl lg:text-8xl leading-[0.95] tracking-tight relative">
          <span class="relative inline-block">
            <span class="relative z-10 underline decoration-blush decoration-4 md:decoration-6 underline-offset-8 bg-gradient-to-r from-white via-pink-100 to-white bg-clip-text text-transparent drop-shadow-2xl text-4xl md:text-6xl lg:text-7xl">Dasar Penghantaran & Pengembalian</span>
            <span class="absolute -inset-4 bg-gradient-to-r from-blush/30 via-white/40 to-rose/30 rounded-2xl -z-10 blur-xl animate-pulse"></span>
            <span class="absolute -inset-1 bg-gradient-to-r from-transparent via-white/20 to-transparent rounded-lg -z-5"></span>
            <span class="absolute left-1/2 top-full -translate-x-1/2 mt-2 w-1/2 h-4 bg-pink-400/30 blur-xl rounded-full opacity-60 animate-glow"></span>
          </span>
        </h1>
        <p class="mt-8 text-white/95 text-xl md:text-2xl leading-relaxed max-w-2xl mx-auto font-serif drop-shadow-lg">
          <span class="text-base md:text-lg">Maklumat lengkap mengenai penghantaran produk, kos, tempoh masa, dan prosedur pengembalian.</span>
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
        <!-- PRODUK DIGITAL -->
        <div class="rad-card rounded-2xl border p-8 mb-8 bg-gradient-to-r from-champagne/20 to-cream/20">
          <h2 class="font-display text-3xl text-maroon mb-4">📱 Produk Digital</h2>
          <p class="text-lg text-slate-700 leading-relaxed mb-4">
            Untuk e-book dan produk digital lain, anda akan menerima produk secara serta-merta selepas pembayaran berjaya.
          </p>
          <div class="grid md:grid-cols-2 gap-4">
            <div class="p-4 rounded-xl bg-white/60 border">
              <h4 class="font-semibold text-maroon mb-2">✅ Kelebihan Digital</h4>
              <ul class="text-sm text-slate-700 space-y-1">
                <li>• Akses serta-merta</li>
                <li>• Tiada kos penghantaran</li>
                <li>• Boleh baca di mana-mana</li>
                <li>• Backup selamanya</li>
              </ul>
            </div>
            <div class="p-4 rounded-xl bg-white/60 border">
              <h4 class="font-semibold text-maroon mb-2">📧 Cara Menerima</h4>
              <ul class="text-sm text-slate-700 space-y-1">
                <li>• E-mel dengan pautan muat turun</li>
                <li>• Link aktif selama 30 hari</li>
                <li>• Boleh muat turun 3 kali</li>
                <li>• Sokongan teknikal tersedia</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- PRODUK FIZIKAL -->
        <div class="space-y-8">
          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">📦 Penghantaran Produk Fizikal</h2>
            <div class="text-slate-700 space-y-4">
              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <h4 class="font-semibold text-maroon mb-3">Kawasan Penghantaran</h4>
                  <ul class="space-y-2">
                    <li class="flex items-center gap-2">
                      <span class="w-2 h-2 rounded-full bg-rose"></span>
                      <span><strong>Semenanjung Malaysia:</strong> 2-3 hari bekerja</span>
                    </li>
                    <li class="flex items-center gap-2">
                      <span class="w-2 h-2 rounded-full bg-rose"></span>
                      <span><strong>Sabah & Sarawak:</strong> 4-7 hari bekerja</span>
                    </li>
                  </ul>
                </div>
                <div>
                  <h4 class="font-semibold text-maroon mb-3">Kos Penghantaran</h4>
                  <div class="space-y-2">
                    <div class="p-3 rounded-lg bg-champagne/20 border">
                      <p class="font-medium">Semenanjung Malaysia</p>
                      <p class="text-sm">RM 7.00 (Pos Laju)</p>
                    </div>
                    <div class="p-3 rounded-lg bg-cream/20 border">
                      <p class="font-medium">Sabah, Sarawak</p>
                      <p class="text-sm">RM 15.00 (Pos Laju)</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">📋 Pemprosesan Pesanan</h2>
            <div class="text-slate-700 space-y-4">
              <div class="grid md:grid-cols-3 gap-4">
                <div class="text-center p-4 bg-white/60 rounded-xl border">
                  <div class="text-2xl mb-2">🛒</div>
                  <p class="font-semibold text-maroon">1. Pesanan Diterima</p>
                  <p class="text-sm">Pesanan diproses dalam 1-2 hari bekerja</p>
                </div>
                <div class="text-center p-4 bg-white/60 rounded-xl border">
                  <div class="text-2xl mb-2">📦</div>
                  <p class="font-semibold text-maroon">2. Pembungkusan</p>
                  <p class="text-sm">Dibungkus dengan teliti dan selamat</p>
                </div>
                <div class="text-center p-4 bg-white/60 rounded-xl border">
                  <div class="text-2xl mb-2">🚚</div>
                  <p class="font-semibold text-maroon">3. Penghantaran</p>
                  <p class="text-sm">Nombor tracking dihantar via Email</p>
                </div>
              </div>
              
              <div class="mt-6 p-4 rounded-xl bg-blush/5 border border-blush/20">
                <h4 class="font-semibold text-maroon mb-2">📅 Jadual Pemprosesan</h4>
                <ul class="text-sm space-y-1">
                  <li>• <strong>Isnin - Jumaat:</strong> Pesanan sebelum 2:00 PM diproses hari yang sama</li>
                  <li>• <strong>Sabtu:</strong> Pesanan sebelum 12:00 PM diproses hari yang sama</li>
                  <li>• <strong>Ahad & Cuti Umum:</strong> Pesanan diproses pada hari bekerja berikutnya</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">📍 Penghantaran Khusus</h2>
            <div class="text-slate-700 space-y-4">
              <div class="grid md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-champagne/20 border border-champagne/30">
                  <h4 class="font-semibold text-maroon mb-2">🏢 Alamat Pejabat</h4>
                  <p class="text-sm mb-2">Untuk penghantaran ke pejabat, sila pastikan:</p>
                  <ul class="text-xs space-y-1">
                    <li>• Nama penerima yang jelas</li>
                    <li>• Nombor telefon yang boleh dihubungi</li>
                    <li>• Nama syarikat dan tingkat/unit</li>
                  </ul>
                </div>
                <div class="p-4 rounded-xl bg-cream/20 border border-cream/30">
                  <h4 class="font-semibold text-maroon mb-2">🏠 Alamat Rumah</h4>
                  <p class="text-sm mb-2">Untuk penghantaran ke rumah:</p>
                  <ul class="text-xs space-y-1">
                    <li>• Alamat lengkap dengan poskod</li>
                    <li>• Nama jalan dan nombor rumah yang tepat</li>
                    <li>• Landmark berdekatan (jika perlu)</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          {{-- <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">🔄 Pengembalian Produk Fizikal</h2>
            <div class="text-slate-700 space-y-4">
              <p class="text-lg font-medium">Jika anda perlu memulangkan produk fizikal:</p>
              
              <div class="grid md:grid-cols-2 gap-4 mt-4">
                <div class="p-4 rounded-xl bg-white/60 border">
                  <h4 class="font-semibold text-maroon mb-2">✅ Syarat Pengembalian</h4>
                  <ul class="text-sm space-y-1">
                    <li>• Dalam tempoh 14 hari</li>
                    <li>• Produk dalam keadaan baik</li>
                    <li>• Pembungkusan asal (jika ada)</li>
                    <li>• Resit atau bukti pembelian</li>
                  </ul>
                </div>
                <div class="p-4 rounded-xl bg-white/60 border">
                  <h4 class="font-semibold text-maroon mb-2">📮 Alamat Pengembalian</h4>
                  <div class="text-sm">
                    <p class="font-medium">Kamalia Kamal (Kak Kay)</p>
                    <p>123, Jalan Kasih Sayang</p>
                    <p>Taman Bahagia</p>
                    <p>50200 Kuala Lumpur</p>
                    <p>Malaysia</p>
                  </div>
                </div>
              </div>
              
              <div class="mt-4 p-4 rounded-xl bg-rose/5 border border-rose/20">
                <p class="text-sm"><strong>Nota Penting:</strong> Kos pos pengembalian ditanggung oleh pelanggan kecuali produk rosak atau salah item dihantar.</p>
              </div>
            </div>
          </div> --}}

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">🚨 Isu Penghantaran</h2>
            <div class="text-slate-700 space-y-4">
              <div class="grid md:grid-cols-2 gap-4">
                <div>
                  <h4 class="font-semibold text-maroon mb-3">Pakej Hilang/Rosak</h4>
                  <ul class="text-sm space-y-2">
                    <li class="flex items-start gap-2">
                      <span class="w-2 h-2 rounded-full bg-rose mt-2"></span>
                      <span>Hubungi kami dalam 7 hari jika pakej tidak sampai</span>
                    </li>
                    <li class="flex items-start gap-2">
                      <span class="w-2 h-2 rounded-full bg-rose mt-2"></span>
                      <span>Untuk pakej rosak, ambil gambar dan lapor segera</span>
                    </li>
                    <li class="flex items-start gap-2">
                      <span class="w-2 h-2 rounded-full bg-rose mt-2"></span>
                      <span>Kami akan menghantar ganti atau refund penuh</span>
                    </li>
                  </ul>
                </div>
                <div>
                  <h4 class="font-semibold text-maroon mb-3">Alamat Salah</h4>
                  <ul class="text-sm space-y-2">
                    <li class="flex items-start gap-2">
                      <span class="w-2 h-2 rounded-full bg-rose mt-2"></span>
                      <span>Periksa alamat dengan teliti sebelum checkout</span>
                    </li>
                    <li class="flex items-start gap-2">
                      <span class="w-2 h-2 rounded-full bg-rose mt-2"></span>
                      <span>Kos penghantaran semula ditanggung pelanggan</span>
                    </li>
                    <li class="flex items-start gap-2">
                      <span class="w-2 h-2 rounded-full bg-rose mt-2"></span>
                      <span>Hubungi kami segera untuk perubahan alamat</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">📞 Tracking & Sokongan</h2>
            <div class="text-slate-700 space-y-4">
              <div class="grid md:grid-cols-3 gap-4">
                <div class="text-center p-4 bg-white/60 rounded-xl border">
                  <div class="text-2xl mb-2">🔍</div>
                  <p class="font-semibold text-maroon">Tracking Number</p>
                  <p class="text-sm">Diberikan dalam 24 jam selepas posting</p>
                </div>
                <div class="text-center p-4 bg-white/60 rounded-xl border">
                  <div class="text-2xl mb-2">💬</div>
                  <p class="font-semibold text-maroon">Email Updates</p>
                  <p class="text-sm">Kemas kini status penghantaran secara real-time</p>
                </div>
                <div class="text-center p-4 bg-white/60 rounded-xl border">
                  <div class="text-2xl mb-2">🕐</div>
                  <p class="font-semibold text-maroon">Sokongan Berterusan</p>
                  <p class="text-sm">Team khidmat pelanggan sedia membantu</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- BACK TO HOME -->
  <section class="max-w-4xl mx-auto px-4 pb-16">
    <div class="text-center">
      <a wire:navigate href="{{ route('home') }}" class="btn primary rounded-full px-8 py-3 font-semibold text-white shadow-glow hover:scale-105 transition-all duration-300">
        Kembali ke Laman Utama
      </a>
    </div>
  </section>

  <div class="container">
        <x-footer />
    </div>
</div>
