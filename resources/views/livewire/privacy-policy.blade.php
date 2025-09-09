<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.policy')]
#[Title('Dasar Privasi - Kak Kay')]
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

  <!-- Stunning Heading -->
  <div class="max-w-3xl mx-auto px-4 pt-16 pb-10 text-center">
    <h1 class="font-display text-4xl md:text-5xl font-semibold text-white mb-2 tracking-tight">Dasar Privasi</h1>
    <div class="flex justify-center mb-4">
      <span class="block w-16 h-1 rounded bg-gradient-to-r from-pink-400 to-purple-500 opacity-80"></span>
    </div>
    <p class="text-base md:text-lg text-white/80 font-normal leading-relaxed">
      Kami menghormati privasi anda dan komited untuk melindungi maklumat peribadi yang anda kongsikan dengan kami.
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
            Dasar Privasi ini menerangkan bagaimana <strong>Kamalia Kamal Resources International (Kak Kay)</strong> ("kami", "kita", atau "milik kami") mengumpul, menggunakan, dan melindungi maklumat anda apabila anda melawat laman web kami atau membeli produk daripada kami.
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
