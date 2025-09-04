<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.pages')]
#[Title('Dasar Pemulangan - Kak Kay')]
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
          Kepuasan Pelanggan
        </div>
        <h1 class="mt-6 font-display text-4xl md:text-6xl lg:text-7xl leading-[0.95] tracking-tight">
          <span class="relative">
            <span class="underline decoration-blush decoration-4 md:decoration-6 underline-offset-8">Dasar Pemulangan</span>
            <span class="absolute -inset-2 bg-blush/10 rounded-lg -z-10 blur-sm"></span>
          </span>
        </h1>
        <p class="mt-6 text-white/95 text-lg md:text-xl leading-relaxed max-w-2xl mx-auto">
          Kepuasan anda adalah keutamaan kami. Ketahui hak dan jaminan anda sebagai pelanggan yang dihargai.
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
                <li>Produk mesti dalam keadaan baik (boleh dibaca tetapi tidak perlu seperti baru)</li>
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
                <li>Untuk produk fizikal: gambar produk dan keadaan semasa</li>
                <li>Maklumat akaun untuk pemulangan wang (jika berbeza dari kaedah pembayaran asal)</li>
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

          <div class="rad-card rounded-2xl border p-6 bg-gradient-to-r from-champagne/30 to-cream/30">
            <h2 class="font-display text-2xl text-maroon mb-4">Hubungi Kami</h2>
            <div class="text-slate-700">
              <p class="mb-4">Untuk sebarang pertanyaan mengenai pemulangan atau pertukaran:</p>
              <div class="space-y-2">
                <p><strong>E-mel:</strong> kakkaylovesme@gmail.com</p>
                <p><strong>WhatsApp:</strong> +60 12-345 6789</p>
                <p><strong>Waktu Operasi:</strong> Isnin - Jumaat, 9:00 AM - 6:00 PM (GMT+8)</p>
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
