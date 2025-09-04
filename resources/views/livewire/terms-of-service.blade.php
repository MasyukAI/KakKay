<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.pages')]
#[Title('Terma & Syarat - Kak Kay')]
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
          Terma & Syarat
        </div>
        <h1 class="mt-6 font-display text-4xl md:text-6xl lg:text-7xl leading-[0.95] tracking-tight">
          <span class="relative">
            <span class="underline decoration-blush decoration-4 md:decoration-6 underline-offset-8">Terma Perkhidmatan</span>
            <span class="absolute -inset-2 bg-blush/10 rounded-lg -z-10 blur-sm"></span>
          </span>
        </h1>
        <p class="mt-6 text-white/95 text-lg md:text-xl leading-relaxed max-w-2xl mx-auto">
          Syarat-syarat penggunaan laman web dan pembelian produk daripada Kak Kay.
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
        <div class="rad-card rounded-2xl border p-8 mb-8">
          <p class="text-slate-600 text-sm mb-4">Berkuat kuasa: 3 September 2025</p>
          <p class="leading-relaxed text-slate-700 mb-6">
            Terma & Syarat ini mengatur penggunaan laman web dan pembelian produk daripada <strong>Kamalia Kamal (Kak Kay)</strong>. Dengan menggunakan perkhidmatan kami, anda bersetuju untuk mematuhi terma-terma berikut.
          </p>
        </div>

        <div class="space-y-8">
          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">1. Penerimaan Terma</h2>
            <div class="text-slate-700 space-y-4">
              <p>Dengan mengakses dan menggunakan laman web ini, anda bersetuju untuk terikat dengan Terma & Syarat ini. Jika anda tidak bersetuju dengan mana-mana bahagian terma ini, sila jangan gunakan perkhidmatan kami.</p>
              
              <div class="p-4 rounded-xl bg-champagne/20 border border-champagne/30">
                <p class="text-sm"><strong>Nota:</strong> Terma ini boleh dikemas kini dari semasa ke semasa. Sila semak secara berkala untuk perubahan terkini.</p>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">2. Maklumat Syarikat</h2>
            <div class="text-slate-700 space-y-4">
              <div class="grid md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-white/60 border">
                  <h4 class="font-semibold text-maroon mb-2">Butiran Perniagaan</h4>
                  <div class="text-sm space-y-1">
                    <p><strong>Nama:</strong> Kamalia Kamal (Kak Kay)</p>
                    <p><strong>Jenis:</strong> Perniagaan Tunggal</p>
                    <p><strong>SSM:</strong> 53123456789</p>
                    <p><strong>GST:</strong> 000123456789</p>
                  </div>
                </div>
                <div class="p-4 rounded-xl bg-white/60 border">
                  <h4 class="font-semibold text-maroon mb-2">Alamat Perniagaan</h4>
                  <div class="text-sm">
                    <p>123, Jalan Kasih Sayang</p>
                    <p>Taman Bahagia</p>
                    <p>50200 Kuala Lumpur</p>
                    <p>Malaysia</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">3. Produk & Perkhidmatan</h2>
            <div class="text-slate-700 space-y-4">
              <p><strong>Produk yang Ditawarkan:</strong></p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>E-book dan buku bercetak mengenai pembangunan diri dan perhubungan</li>
                <li>Khidmat kaunseling dan terapi (jika berkenaan)</li>
                <li>Program pembangunan diri</li>
                <li>Kandungan digital dan bahan pembelajaran</li>
              </ul>
              
              <div class="mt-4 p-4 rounded-xl bg-rose/5 border border-rose/20">
                <p class="text-sm"><strong>Penafian:</strong> Produk kami adalah untuk tujuan pendidikan dan pembangunan diri. Ia bukan pengganti khidmat profesional seperti terapi psikologi klinikal atau kaunseling perubatan.</p>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">4. Pembelian & Pembayaran</h2>
            <div class="text-slate-700 space-y-4">
              <h4 class="font-semibold text-maroon">Proses Pembelian:</h4>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Semua harga dinyatakan dalam Ringgit Malaysia (RM)</li>
                <li>Harga termasuk GST (jika berkenaan)</li>
                <li>Pembayaran mesti dibuat sepenuhnya sebelum produk dihantar</li>
                <li>Kami menerima pembayaran melalui kad kredit, debit, dan e-wallet</li>
              </ul>
              
              <h4 class="font-semibold text-maroon mt-4">Kaedah Pembayaran:</h4>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-2">
                <div class="text-center p-2 bg-white/60 rounded-lg border text-xs">Visa</div>
                <div class="text-center p-2 bg-white/60 rounded-lg border text-xs">Mastercard</div>
                <div class="text-center p-2 bg-white/60 rounded-lg border text-xs">Touch 'n Go</div>
                <div class="text-center p-2 bg-white/60 rounded-lg border text-xs">GrabPay</div>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">5. Hak Cipta & Harta Intelek</h2>
            <div class="text-slate-700 space-y-4">
              <p><strong>Semua kandungan di laman web ini adalah hak cipta Kamalia Kamal.</strong></p>
              
              <h4 class="font-semibold text-maroon">Yang Tidak Dibenarkan:</h4>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Menyalin, mengedar, atau menjual semula produk digital tanpa kebenaran</li>
                <li>Berkongsi akaun atau maklumat login</li>
                <li>Menggunakan kandungan untuk tujuan komersial tanpa lesen</li>
                <li>Mengedit atau mengubah kandungan asal</li>
              </ul>
              
              <h4 class="font-semibold text-maroon mt-4">Yang Dibenarkan:</h4>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Penggunaan peribadi untuk pembelajaran dan pembangunan diri</li>
                <li>Berkongsi testimoni dan ulasan yang jujur</li>
                <li>Merujuk kandungan dengan petikan yang sesuai</li>
              </ul>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">6. Tingkah Laku Pengguna</h2>
            <div class="text-slate-700 space-y-4">
              <p><strong>Anda bersetuju untuk TIDAK:</strong></p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Menggunakan perkhidmatan untuk aktiviti yang menyalahi undang-undang</li>
                <li>Mengganggu atau merosakkan operasi laman web</li>
                <li>Menghantar spam atau kandungan yang tidak diingini</li>
                <li>Menyamar sebagai orang lain atau entiti lain</li>
                <li>Melanggar hak orang lain</li>
              </ul>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">7. Penafian & Had Liabiliti</h2>
            <div class="text-slate-700 space-y-4">
              <div class="p-4 rounded-xl bg-rose/5 border border-rose/20">
                <h4 class="font-semibold text-maroon mb-2">Penafian Penting</h4>
                <ul class="text-sm space-y-1">
                  <li>• Produk kami adalah untuk tujuan pendidikan dan motivasi sahaja</li>
                  <li>• Hasil individu mungkin berbeza-beza</li>
                  <li>• Tiada jaminan hasil atau kejayaan yang spesifik</li>
                  <li>• Bukan pengganti nasihat profesional dalam kesihatan mental</li>
                </ul>
              </div>
              
              <p class="mt-4">Kami tidak bertanggungjawab terhadap sebarang kerugian atau kerosakan yang timbul daripada penggunaan produk atau perkhidmatan kami, kecuali yang dikehendaki oleh undang-undang.</p>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">8. Penamatan</h2>
            <div class="text-slate-700 space-y-4">
              <p>Kami berhak untuk menamatkan atau menggantung akses anda kepada perkhidmatan kami jika:</p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Anda melanggar Terma & Syarat ini</li>
                <li>Kami mengesyaki aktiviti penipuan atau menyalahi undang-undang</li>
                <li>Atas budi bicara kami dengan notis yang munasabah</li>
              </ul>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">9. Undang-undang yang Berkenaan</h2>
            <div class="text-slate-700 space-y-4">
              <p>Terma & Syarat ini dikawal oleh undang-undang Malaysia. Sebarang pertikaian akan diselesaikan di mahkamah Malaysia yang mempunyai bidang kuasa.</p>
              
              <div class="p-4 rounded-xl bg-champagne/20 border border-champagne/30">
                <p class="text-sm"><strong>Penyelesaian Pertikaian:</strong> Kami menggalakkan penyelesaian secara aman melalui perbincangan sebelum tindakan undang-undang.</p>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">10. Perubahan Terma</h2>
            <div class="text-slate-700 space-y-4">
              <p>Kami berhak untuk mengubah Terma & Syarat ini pada bila-bila masa. Perubahan akan berkuat kuasa serta-merta selepas disiarkan di laman web ini.</p>
              
              <p>Penggunaan berterusan perkhidmatan kami selepas perubahan menandakan penerimaan anda terhadap terma yang telah dikemas kini.</p>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6 bg-gradient-to-r from-champagne/30 to-cream/30">
            <h2 class="font-display text-2xl text-maroon mb-4">Hubungi Kami</h2>
            <div class="text-slate-700">
              <p class="mb-4">Jika anda mempunyai sebarang soalan mengenai Terma & Syarat ini:</p>
              <div class="space-y-2">
                <p><strong>E-mel:</strong> kakkaylovesme@gmail.com</p>
                <p><strong>Telefon:</strong> +60 12-345 6789</p>
                <p><strong>WhatsApp:</strong> +60 12-345 6789</p>
                <p><strong>Alamat:</strong> 24, Jalan Pakis 1, Taman Fern Grove, 43200 Cheras, Selangor</p>
                {{-- <p><strong>Waktu Operasi:</strong> Isnin - Jumaat, 9:00 AM - 6:00 PM (GMT+8)</p> --}}
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

  <!-- STANDARDIZED FOOTER -->
  <footer class="bg-maroon text-white">
    <div class="max-w-6xl mx-auto px-4 py-10">
      <!-- Policy Links Section - Centered -->
      <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
        <div style="text-align: center; max-width: 800px; margin: 0 auto;">
          <h4 style="color: #ffe9f5; font-size: 1.1rem; margin-bottom: 1rem; font-weight: 600;">Dasar & Polisi</h4>
          <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; align-items: center;">
            <a href="/privacy-policy" style="color: rgba(255,233,245,0.8); text-decoration: none; font-size: 0.9rem; transition: all 0.3s; padding: 0.3rem 0;" onmouseover="this.style.color='#ff69b4'; this.style.transform='translateY(-2px)'" onmouseout="this.style.color='rgba(255,233,245,0.8)'; this.style.transform='translateY(0)'">Dasar Privasi</a>
            <span style="color: rgba(255,233,245,0.3); font-size: 0.8rem;">•</span>
            <a href="/refund-policy" style="color: rgba(255,233,245,0.8); text-decoration: none; font-size: 0.9rem; transition: all 0.3s; padding: 0.3rem 0;" onmouseover="this.style.color='#ff69b4'; this.style.transform='translateY(-2px)'" onmouseout="this.style.color='rgba(255,233,245,0.8)'; this.style.transform='translateY(0)'">Dasar Pemulangan</a>
            <span style="color: rgba(255,233,245,0.3); font-size: 0.8rem;">•</span>
            <a href="/shipping-policy" style="color: rgba(255,233,245,0.8); text-decoration: none; font-size: 0.9rem; transition: all 0.3s; padding: 0.3rem 0;" onmouseover="this.style.color='#ff69b4'; this.style.transform='translateY(-2px)'" onmouseout="this.style.color='rgba(255,233,245,0.8)'; this.style.transform='translateY(0)'">Dasar Penghantaran</a>
            <span style="color: rgba(255,233,245,0.3); font-size: 0.8rem;">•</span>
            <a href="/terms-of-service" style="color: rgba(255,233,245,0.8); text-decoration: none; font-size: 0.9rem; transition: all 0.3s; padding: 0.3rem 0;" onmouseover="this.style.color='#ff69b4'; this.style.transform='translateY(-2px)'" onmouseout="this.style.color='rgba(255,233,245,0.8)'; this.style.transform='translateY(0)'">Terma & Syarat</a>
          </div>
        </div>
        
        <!-- Contact Info Footer -->
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05); text-align: center; color: rgba(255,233,245,0.6); font-size: 0.8rem;">
          <p style="margin: 0.5rem 0;">&copy; 2025 Kamalia Kamal Research International (Kak Kay)<br />Hak Terpelihara</p>
          <div style="display: flex; flex-wrap: wrap; gap: 0.5rem 1rem; justify-content: center; align-items: center; margin-top: 0.5rem;">
            <span style="display: flex; align-items: center; gap: 0.3rem;">
              <span style="color: #ff69b4;">📍</span>
              24, Jalan Pakis 1, Taman Fern Grove, 43200 Cheras, Selangor
            </span>
            <span style="color: rgba(255,233,245,0.3);">•</span>
            <span style="display: flex; align-items: center; gap: 0.3rem;">
              <span style="color: #ff69b4;">📱</span>
              <a href="https://wa.me/60138846594" style="color: inherit; text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='#ff69b4'" onmouseout="this.style.color='inherit'">+60 12-345 6789</a>
            </span>
          </div>
        </div>
      </div>
    </div>
  </footer>
</div>
