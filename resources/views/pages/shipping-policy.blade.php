<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.pages')]
#[Title('Dasar Penghantaran & Pengembalian - Kak Kay')]
class extends Component {
    //
} ?>

<div>
  <!-- HERO -->
  <header class="hero text-white relative overflow-hidden">
    <div class="max-w-4xl mx-auto px-4 pt-16 pb-20 text-center relative z-10">
      <div class="fade-in-up">
        <div class="inline-flex items-center gap-2 badge px-4 py-2 rounded-full text-xs text-maroon font-semibold shadow-elegant">
          <span class="w-2 h-2 rounded-full bg-rose shadow-inner"></span>
          Penghantaran & Pengembalian
        </div>
        <h1 class="mt-6 font-display text-4xl md:text-6xl lg:text-7xl leading-[0.95] tracking-tight">
          <span class="relative">
            <span class="underline decoration-blush decoration-4 md:decoration-6 underline-offset-8">Dasar Penghantaran</span>
            <span class="absolute -inset-2 bg-blush/10 rounded-lg -z-10 blur-sm"></span>
          </span> & <span class="text-blush drop-shadow-lg">Pengembalian</span>
        </h1>
        <p class="mt-6 text-white/95 text-lg md:text-xl leading-relaxed max-w-2xl mx-auto">
          Maklumat lengkap mengenai penghantaran produk, kos, tempoh masa, dan prosedur pengembalian.
        </p>
      </div>
    </div>
    <!-- Decorative elements -->
    <div class="absolute top-10 left-10 w-20 h-20 bg-blush/10 rounded-full blur-xl"></div>
    <div class="absolute bottom-10 right-10 w-32 h-32 bg-orchid/10 rounded-full blur-2xl"></div>
  </header>

  <!-- CONTENT -->
  <main class="bg-velvet relative">
    <div class="absolute top-20 left-4 w-16 h-16 bg-orchid/5 rounded-full blur-xl"></div>
    <div class="absolute bottom-40 right-8 w-24 h-24 bg-rose/5 rounded-full blur-2xl"></div>
    
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
                    <li class="flex items-center gap-2">
                      <span class="w-2 h-2 rounded-full bg-rose"></span>
                      <span><strong>Singapura:</strong> 5-10 hari bekerja</span>
                    </li>
                  </ul>
                </div>
                <div>
                  <h4 class="font-semibold text-maroon mb-3">Kos Penghantaran</h4>
                  <div class="space-y-2">
                    <div class="p-3 rounded-lg bg-champagne/20 border">
                      <p class="font-medium">Semenanjung Malaysia</p>
                      <p class="text-sm">RM 6.00 (Pos Laju)</p>
                    </div>
                    <div class="p-3 rounded-lg bg-cream/20 border">
                      <p class="font-medium">Sabah, Sarawak & Singapura</p>
                      <p class="text-sm">RM 12.00 (Pos Laju)</p>
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
                  <p class="text-sm">Nombor tracking dihantar via WhatsApp/E-mel</p>
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

          <div class="rad-card rounded-2xl border p-6">
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
          </div>

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
                  <p class="font-semibold text-maroon">WhatsApp Updates</p>
                  <p class="text-sm">Kemas kini status penghantaran secara real-time</p>
                </div>
                <div class="text-center p-4 bg-white/60 rounded-xl border">
                  <div class="text-2xl mb-2">🕐</div>
                  <p class="font-semibold text-maroon">Sokongan 24/7</p>
                  <p class="text-sm">Tim khidmat pelanggan sedia membantu</p>
                </div>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6 bg-gradient-to-r from-champagne/30 to-cream/30">
            <h2 class="font-display text-2xl text-maroon mb-4">Hubungi Kami</h2>
            <div class="text-slate-700">
              <p class="mb-4">Untuk sebarang pertanyaan mengenai penghantaran atau pengembalian:</p>
              <div class="grid md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <p><strong>E-mel:</strong> kakkaylovesme@gmail.com</p>
                  <p><strong>WhatsApp:</strong> +60 12-345 6789</p>
                  <p><strong>Telefon:</strong> +60 3-1234 5678</p>
                </div>
                <div class="space-y-2">
                  <p><strong>Waktu Operasi:</strong></p>
                  <p class="text-sm">Isnin - Jumaat: 9:00 AM - 6:00 PM</p>
                  <p class="text-sm">Sabtu: 9:00 AM - 1:00 PM</p>
                  <p class="text-sm">Ahad: Tutup</p>
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
      <a href="{{ route('home') }}" class="btn-cta rounded-full px-8 py-3 font-semibold text-white shadow-glow hover:scale-105 transition-all duration-300">
        Kembali ke Laman Utama
      </a>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="bg-maroon text-white">
    <div class="max-w-6xl mx-auto px-4 py-10 text-center">
      <p class="font-display text-xl">Kak Kay - Penghantaran & Pengembalian</p>
      <p class="text-white/90 text-sm mt-2">Hak cipta © <span id="year">2025</span> Kamalia Kamal. Semua hak terpelihara.</p>
    </div>
  </footer>
</div>
