<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.policy')]
#[Title('Terma & Syarat - Kak Kay')]
class extends Component {
    //
} ?>

<div>
  <!-- NAVIGATION HEADER -->
  <div class="container">
    <header style="padding: 1.5rem 0;">
  <a href="/">
        <div class="brand">
          <div class="logo" aria-hidden="true"></div>
            <div>
              <h1>Kak Kay</h1>
              <div class="tagline  text-xs sm:text-base">Counsellor • Therapist • KKDI Creator</div>
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
  </div>

  <!-- Minimalistic Heading -->
  <div class="max-w-3xl mx-auto px-4 pt-16 pb-10 text-center">
    <h1 class="font-display text-4xl md:text-5xl font-semibold text-white mb-2 tracking-tight">Terma Perkhidmatan</h1>
    <div class="flex justify-center mb-4">
      <span class="block w-16 h-1 rounded bg-gradient-to-r from-pink-400 to-purple-500 opacity-80"></span>
    </div>
    <p class="text-base md:text-lg text-white/80 font-normal leading-relaxed">
      Syarat-syarat penggunaan laman web dan pembelian produk daripada Kak Kay.
    </p>
  </div>

  <!-- CONTENT -->
  <main class="relative">
    <!-- Enhanced decorative elements -->
    <div class="decorative-blob absolute top-20 left-4 w-32 h-32 bg-gradient-to-r from-orchid/20 to-blush/20 rounded-full"></div>
    <div class="decorative-blob absolute top-40 right-8 w-24 h-24 bg-gradient-to-r from-rose/20 to-pink/20 rounded-full" style="animation-delay: -2s;"></div>
    <div class="decorative-blob absolute bottom-60 left-12 w-20 h-20 bg-gradient-to-r from-purple/15 to-magenta/15 rounded-full" style="animation-delay: -4s;"></div>
    <div class="decorative-blob absolute bottom-40 right-16 w-28 h-28 bg-gradient-to-r from-blush/15 to-rose/15 rounded-full" style="animation-delay: -1s;"></div>
    
    <section class="max-w-4xl mx-auto px-4 py-16">
      <div class="prose prose-lg max-w-none">
        <div class="rad-card rounded-2xl border p-8 mb-8">
          <p class="text-slate-600 text-sm mb-4">Berkuat kuasa: 3 September 2025</p>
          <p class="leading-relaxed text-slate-700 mb-6">
            Terma & Syarat ini mengatur penggunaan laman web dan pembelian produk daripada <strong>Kamalia Kamal Resources International (Kak Kay)</strong>. Dengan menggunakan perkhidmatan kami, anda bersetuju untuk mematuhi terma-terma berikut.
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
                    <p><strong>Nama:</strong> Kamalia Kamal Resources International</p>
                    <p><strong>Jenis:</strong> Sendirian Berhad</p>
                    <p><strong>SSM:</strong> 202101019097 (1419397-X)</p>
                  </div>
                </div>
                <div class="p-4 rounded-xl bg-white/60 border">
                  <h4 class="font-semibold text-maroon mb-2">Alamat Perniagaan</h4>
                  <div class="text-sm">
                    <p>24, Jalan Pakis 1,</p>
                    <p>Taman Fern Grove,</p>
                    <p>43200 Cheras, Selangor</p>
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
              </ul>
              
              <h4 class="font-semibold text-maroon mt-4">Kaedah Pembayaran:</h4>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-2">
                <div class="text-center p-2 bg-white/60 rounded-lg border text-xs">FPX</div>
                <div class="text-center p-2 bg-white/60 rounded-lg border text-xs">Visa</div>
                <div class="text-center p-2 bg-white/60 rounded-lg border text-xs">Mastercard</div>
                <div class="text-center p-2 bg-white/60 rounded-lg border text-xs">eWallet</div>
              </div>
            </div>
          </div>

          <div class="rad-card rounded-2xl border p-6">
            <h2 class="font-display text-2xl text-maroon mb-4">5. Hak Cipta & Harta Intelek</h2>
            <div class="text-slate-700 space-y-4">
              <p><strong>Semua kandungan di laman web ini adalah hak cipta Kak Kay.</strong></p>
              
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
