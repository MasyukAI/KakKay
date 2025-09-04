<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.pages')]
#[Title('Dasar Privasi - Kak Kay')]
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
          Privasi & Keselamatan
        </div>
        <h1 class="mt-6 font-display text-4xl md:text-6xl lg:text-7xl leading-[0.95] tracking-tight">
          <span class="relative">
            <span class="underline decoration-blush decoration-4 md:decoration-6 underline-offset-8">Dasar Privasi</span>
            <span class="absolute -inset-2 bg-blush/10 rounded-lg -z-10 blur-sm"></span>
          </span>
        </h1>
        <p class="mt-6 text-white/95 text-lg md:text-xl leading-relaxed max-w-2xl mx-auto">
          Kami menghormati privasi anda dan komited untuk melindungi maklumat peribadi yang anda kongsikan dengan kami.
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
            Dasar Privasi ini menerangkan bagaimana <strong>Kamalia Kamal (Kak Kay)</strong> ("kami", "kita", atau "milik kami") mengumpul, menggunakan, dan melindungi maklumat anda apabila anda melawat laman web kami atau membeli produk daripada kami.
          </p>
        </div>

        <div class="space-y-8">
          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">1. Maklumat Yang Kami Kumpul</h2>
            <div class="text-slate-700 space-y-4">
              <p><strong>Maklumat Peribadi:</strong></p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Nama penuh</li>
                <li>Alamat e-mel</li>
                <li>Nombor telefon</li>
                <li>Alamat penghantaran dan pengebilan</li>
                <li>Maklumat pembayaran (diproses secara selamat melalui gateway pembayaran yang bertauliah)</li>
              </ul>
              
              <p class="mt-4"><strong>Maklumat Automatik:</strong></p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Alamat IP</li>
                <li>Jenis pelayar dan peranti</li>
                <li>Halaman yang dilawat dan masa lawatan</li>
                <li>Cookies untuk meningkatkan pengalaman pengguna</li>
              </ul>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">2. Bagaimana Kami Menggunakan Maklumat Anda</h2>
            <div class="text-slate-700">
              <ul class="list-disc list-inside space-y-2">
                <li>Memproses dan menyelesaikan pesanan anda</li>
                <li>Menghantar produk digital atau fizikal</li>
                <li>Berkomunikasi mengenai pesanan dan khidmat pelanggan</li>
                <li>Mengirim kemas kini produk dan penawaran istimewa (dengan kebenaran anda)</li>
                <li>Meningkatkan laman web dan pengalaman pengguna</li>
                <li>Mematuhi keperluan undang-undang</li>
              </ul>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">3. Perkongsian Maklumat</h2>
            <div class="text-slate-700 space-y-4">
              <p>Kami <strong>tidak</strong> menjual, menyewa, atau berkongsi maklumat peribadi anda dengan pihak ketiga kecuali:</p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li><strong>Penyedia Perkhidmatan:</strong> Syarikat yang membantu kami memproses pembayaran, menghantar produk, atau menyediakan perkhidmatan teknikal</li>
                <li><strong>Keperluan Undang-undang:</strong> Apabila dikehendaki oleh undang-undang atau untuk melindungi hak kami</li>
                <li><strong>Kebenaran Anda:</strong> Dengan kebenaran eksplisit anda</li>
              </ul>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">4. Keselamatan Data</h2>
            <div class="text-slate-700 space-y-4">
              <p>Kami melaksanakan langkah-langkah keselamatan yang sesuai untuk melindungi maklumat peribadi anda:</p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Enkripsi SSL untuk semua transaksi pembayaran</li>
                <li>Akses terhad kepada maklumat peribadi</li>
                <li>Kemas kini keselamatan yang kerap</li>
                <li>Pemantauan aktiviti yang mencurigakan</li>
              </ul>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">5. Hak-hak Anda</h2>
            <div class="text-slate-700 space-y-4">
              <p>Anda berhak untuk:</p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Mengakses maklumat peribadi yang kami simpan tentang anda</li>
                <li>Meminta pembetulan maklumat yang tidak tepat</li>
                <li>Meminta pemadaman maklumat peribadi anda</li>
                <li>Menarik diri daripada komunikasi pemasaran</li>
                <li>Memfailkan aduan dengan pihak berkuasa perlindungan data</li>
              </ul>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">6. Cookies</h2>
            <div class="text-slate-700 space-y-4">
              <p>Kami menggunakan cookies untuk:</p>
              <ul class="list-disc list-inside ml-4 space-y-2">
                <li>Mengingat pilihan anda</li>
                <li>Menganalisis trafik laman web</li>
                <li>Menyediakan pengalaman yang diperibadikan</li>
              </ul>
              <p class="mt-4">Anda boleh menyahaktifkan cookies melalui tetapan pelayar anda, tetapi ini mungkin menjejaskan fungsi laman web.</p>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">7. Kemas Kini Dasar</h2>
            <div class="text-slate-700">
              <p>Kami mungkin mengemas kini Dasar Privasi ini dari semasa ke semasa. Perubahan akan dimaklumkan melalui laman web ini dengan tarikh "Berkuat kuasa" yang baru.</p>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6 bg-gradient-to-r from-champagne/30 to-cream/30">
            <h2 class="font-display text-2xl text-maroon mb-4">Hubungi Kami</h2>
            <div class="text-slate-700">
              <p class="mb-4">Jika anda mempunyai sebarang soalan mengenai Dasar Privasi ini, sila hubungi kami:</p>
              <div class="space-y-2">
                <p><strong>E-mel:</strong> kakkaylovesme@gmail.com</p>
                <p><strong>Telefon:</strong> +60 12-345 6789</p>
                <p><strong>Alamat:</strong> 123, Jalan Kasih Sayang, Taman Bahagia, 50200 Kuala Lumpur, Malaysia</p>
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
          <p style="margin: 0.5rem 0;">&copy; 2025 Kamalia Kamal Research International (Kak Kay)</p>
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
