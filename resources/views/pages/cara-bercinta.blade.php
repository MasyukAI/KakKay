<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;
use App\Models\Product;
use MasyukAI\Cart\Facades\Cart;
use Filament\Notifications\Notification;

new
#[Layout('components.layouts.pages')]
class extends Component {

    public function addToCart()
    {
        $product = Product::where('slug', 'cara-bercinta')->first();
        
        if (!$product) {
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

        // Middleware handles cart instance switching

        Cart::add(
            (string) $product->id,
            $product->name,
            $product->price, // Convert from cents to dollars for the cart
            1,
            [
                'slug' => $product->slug,
                'category' => $product->category_id ? $product->category->name : 'books'
            ]
        );

        // Notification::make()
        //     ->title('Berjaya Ditambah!')
        //     ->body('Produk telah ditambah ke keranjang!')
        //     ->success()
        //     ->icon('heroicon-o-check-circle')
        //     ->iconColor('success')
        //     ->duration(3000)
        //     ->send();

        $this->dispatch('product-added-to-cart');
        $this->redirect('/cart');
    }
} ?>

<div>
  <!-- HERO -->
    <!-- HERO -->
  <header class="hero text-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 pt-16 pb-20 md:pb-28 grid md:grid-cols-2 gap-12 items-center relative z-10">
      <div class="fade-in-up order-last md:order-first">
        <div class="inline-flex items-center gap-2 badge px-4 py-2 rounded-full text-xs text-maroon font-semibold shadow-elegant">
          <span class="w-2 h-2 rounded-full bg-rose shadow-inner"></span>
          Pelancaran Edisi Istimewa
        </div>
        <h1 class="mt-6 font-display text-4xl md:text-6xl lg:text-7xl leading-[0.95] tracking-tight">
          "Macam ni rupanya <span class="relative">
            <span class="underline decoration-blush decoration-4 md:decoration-6 underline-offset-8">cara nak bercinta</span>
            <span class="absolute -inset-2 bg-blush/10 rounded-lg -z-10 blur-sm"></span>
          </span>… <span class="text-blush drop-shadow-lg">mudahnya lahai!</span>"
        </h1>
        <p class="mt-6 text-white/95 text-lg md:text-xl leading-relaxed max-w-2xl">
          Buku <strong class="text-champagne">34 Teknik Bercinta Dengan Pasangan</strong> oleh <strong class="text-champagne">Kamalia Kamal (Kak Kay)</strong> ialah panduan praktikal untuk <em class="text-blush font-medium">hidupkan semula rasa</em> — tanpa bajet besar, tanpa drama. Hanya usaha kecil yang ikhlas, konsisten, dan manis.
        </p>
                  <div class="mt-8 flex flex-wrap gap-4">
            <button wire:click="addToCart" class="rounded-full px-8 py-4 text-base font-semibold bg-rose text-white hover:bg-rose-600 transition-all duration-300 hover:scale-105">
              Beli Hari Ini
            </button>
            <a href="#learn" class="rounded-full px-8 py-4 text-base font-semibold bg-white/20 backdrop-blur-sm ring-1 ring-white/40 hover:bg-white/30 hover:ring-white/60 transition-all duration-300 hover:scale-105">
              Baca Lagi
            </a>
          </div>
        <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-3 text-[13px] text-white/90">
          <div class="bg-white/15 backdrop-blur-sm rounded-xl p-3 border border-white/20 hover:bg-white/25 transition-all duration-300">
            <div class="font-medium">Mudah & Murah</div>
          </div>
          <div class="bg-white/15 backdrop-blur-sm rounded-xl p-3 border border-white/20 hover:bg-white/25 transition-all duration-300">
            <div class="font-medium">Mesra Syariah</div>
          </div>
          <div class="bg-white/15 backdrop-blur-sm rounded-xl p-3 border border-white/20 hover:bg-white/25 transition-all duration-300">
            <div class="font-medium">Sesuai Pasangan Sibuk</div>
          </div>
          <div class="bg-white/15 backdrop-blur-sm rounded-xl p-3 border border-white/20 hover:bg-white/25 transition-all duration-300">
            <div class="font-medium">34 Teknik Praktikal</div>
          </div>
        </div>
      </div>
            <div class="relative flex justify-center md:justify-end order-first md:order-last">
        <div class="relative group">
          <!-- Floating glow effect -->
          <div class="absolute -inset-4 rounded-3xl blur-2xl opacity-75 bg-gradient-to-r from-blush/30 via-orchid/30 to-ruby/30 group-hover:opacity-100 transition-opacity duration-500"></div>
          <!-- Book container -->
          <div class="relative glass rounded-3xl p-6 shadow-luxury group-hover:shadow-dreamy transition-all duration-500 group-hover:scale-105">
            <img src="{{ asset('storage/images/cover/cara-bercinta.png') }}" alt="Buku 34 Teknik Bercinta" class="max-w-[280px] md:max-w-[320px] mx-auto h-auto rounded-2xl shadow-elegant" />
          </div>
          <!-- Floating elements -->
          <div class="absolute -top-4 -right-4 glass-dark rounded-full p-3 shadow-luxury">
            <div class="text-white text-xs font-bold">34 Teknik</div>
          </div>
          <div class="absolute -bottom-4 -left-4 glass-dark rounded-full p-4 shadow-luxury">
            <div class="text-white text-xs font-bold">RM 50</div>
          </div>
        </div>
      </div>
    </div>
    <!-- Decorative elements -->
    <div class="absolute top-10 left-10 w-20 h-20 bg-blush/10 rounded-full blur-xl"></div>
    <div class="absolute bottom-10 right-10 w-32 h-32 bg-orchid/10 rounded-full blur-2xl"></div>
  </header>

  <!-- LONG COPY -->
  <main class="bg-velvet relative" id="learn">
    <!-- Floating decorative elements -->
    <div class="absolute top-20 left-4 w-16 h-16 bg-orchid/5 rounded-full blur-xl"></div>
    <div class="absolute bottom-40 right-8 w-24 h-24 bg-rose/5 rounded-full blur-2xl"></div>
    <section class="max-w-3xl mx-auto px-4 py-16">
      <h2 class="font-display text-3xl md:text-4xl text-maroon">Kalau ikut perasaan, cinta surut. Kalau ikut <i>usaha</i>, cinta subur.</h2>
      <p class="mt-5 leading-relaxed text-[17px] text-slate-700">Ramai pasangan tunggu <b>mood</b> datang. Tunggu masa lapang, tunggu anak tidur awal, tunggu ada duit lebih. Sementara menunggu, rumah jadi macam <i>hostel</i>: jumpa, makan, tidur. Rasa rindu jadi jarang, bicara jadi urusan. Anda tak salah; hidup memang sibuk. Tapi kalau kita sanggup buat usaha kecil setiap hari—cinta tak perlu tunggu mood. Ia boleh <b>dihidupkan</b>.</p>
      <p class="mt-4 leading-relaxed text-[17px] text-slate-700">Itu falsafah buku ini. <b>34 Teknik Bercinta</b> bukan teori psikologi yang rumit. Ia ialah <b>34 aksi kecil</b> — yang murah, lembut, halal — untuk menyentuh hati pasangan tanpa memaksa. Malam hujan, masak mi segera nostalgia. Malam biasa, buat picnic dalam bilik. Pagi Sabtu, tampal sticky note cinta di cermin bilik air. Kecil… tapi <i>berkesan</i>.</p>
      <div class="mt-8 grid gap-4">
        <div class="rad-card rounded-2xl border p-6">
          <h3 class="font-semibold text-maroon">Apa yang anda akan rasa selepas 7 hari</h3>
          <ul class="mt-3 grid sm:grid-cols-2 gap-3 text-slate-700">
            <li class="flex gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-rose"></span><span>Lebih kerap senyum bila memandang pasangan.</span></li>
            <li class="flex gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-rose"></span><span>Perbualan jadi lembut—kurang isu, lebih rasa.</span></li>
            <li class="flex gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-rose"></span><span>Anak-anak pun rasa suasana rumah lebih hangat.</span></li>
            <li class="flex gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-rose"></span><span>Anda mula percaya: “Bahagia ni sebenarnya <i>boleh diusahakan</i>.”</span></li>
          </ul>
        </div>
        <div class="rad-card rounded-2xl border p-6">
          <h3 class="font-semibold text-maroon">Kenapa pendekatan ini menjadi</h3>
          <p class="mt-2 text-slate-700">Kita tak cuba ‘pukau’ pasangan dengan kejutan mewah yang jarang-jarang. Kita bina <b>mikro-momen</b> — aksi kecil tapi konsisten — yang memberitahu hati, “awak penting untuk saya, setiap hari.” Inilah beza antara hubungan yang <i>menunggu</i> bahagia dengan hubungan yang <i>membina</i> bahagia.</p>
        </div>
      </div>
    </section>

    <!-- WHAT'S INSIDE -->
    <section class="max-w-7xl mx-auto px-4 py-12 md:py-16">
      <div class="rounded-[32px] border shadow-luxury bg-white overflow-hidden relative">
        <!-- Decorative background -->
        <div class="absolute inset-0 bg-gradient-to-br from-rose/5 via-white to-orchid/5 pointer-events-none"></div>

        <div class="px-6 md:px-12 py-12 md:py-16 relative">
          <div class="text-center mb-12">
            <h2 class="font-display text-4xl md:text-5xl lg:text-6xl text-gradient leading-tight mb-4">
              Dalam buku ini, anda akan temui…
            </h2>
            <p class="text-xl md:text-2xl text-slate-600 max-w-3xl mx-auto leading-relaxed">
              34 teknik yang dibahagi kepada kategori mudah. Setiap teknik disertakan cadangan waktu, pantang ringkas dan tujuan—supaya anda boleh terus praktik malam ini.
            </p>
          </div>

          <div class="grid lg:grid-cols-3 gap-8 mb-12">
            <div class="rad-card rounded-2xl border p-6">
              <h3 class="font-semibold text-maroon">1) Makanan & Nostalgia</h3>
              <p class="text-slate-700 mt-2">Masak menu kenangan bersama, pilih kudapan ‘zaman kita bercinta’, minum teh tarik berdua. Rasa lama yang baik itu ditarik pulang.</p>
              <ul class="mt-3 text-sm text-slate-700 space-y-1 list-disc list-inside">
                <li>Menu Nostalgia (Teknik #1)</li>
                <li>Teh Tarik Tengah Malam</li>
                <li>Kuih Suku Hati</li>
              </ul>
            </div>
            <div class="rad-card rounded-2xl border p-6">
              <h3 class="font-semibold text-maroon">2) Rumah Jadi Dating Spot</h3>
              <p class="text-slate-700 mt-2">Picnic dalam bilik/ruang tamu, filem lembut tanpa telefon, lampu malap & bantal tambahan. Murah, selamat, romantik.</p>
              <ul class="mt-3 text-sm text-slate-700 space-y-1 list-disc list-inside">
                <li>Picnic Dalam Rumah (Teknik #2)</li>
                <li>Malam Filem Tanpa Skrin Kedua</li>
                <li>Sarapan Di Lantai</li>
              </ul>
            </div>
            <div class="rad-card rounded-2xl border p-6">
              <h3 class="font-semibold text-maroon">3) Sentuhan, Doa & Bahasa Cinta</h3>
              <p class="text-slate-700 mt-2">Genggam tangan, doa berdua, pujian yang spesifik. Halal, lembut, dan menguatkan.</p>
              <ul class="mt-3 text-sm text-slate-700 space-y-1 list-disc list-inside">
                <li>Doa Pegang Tangan</li>
                <li>3 Pujian Sehari</li>
                <li>Pelukan 20 Saat</li>
              </ul>
            </div>
            <div class="rad-card rounded-2xl border p-6">
              <h3 class="font-semibold text-maroon">4) Cerita & Komunikasi Manis</h3>
              <p class="text-slate-700 mt-2">Soalan pembuka hati, sesi ‘replay memori’, dan skrip minta maaf yang menyejukkan—untuk hari yang tegang.</p>
              <ul class="mt-3 text-sm text-slate-700 space-y-1 list-disc list-inside">
                <li>Soalan ‘Apa Paling Manis Hari Ini?’</li>
                <li>Replay Memori 10 Minit</li>
                <li>Skrip ‘Maaf Sayang’</li>
              </ul>
            </div>
            <div class="rad-card rounded-2xl border p-6">
              <h3 class="font-semibold text-maroon">5) Kejutan Murah Yang Bermakna</h3>
              <p class="text-slate-700 mt-2">Sticky note cinta, nota di bekal, pesanan ringkas yang kelakar. Kos rendah, kesan tinggi.</p>
              <ul class="mt-3 text-sm text-slate-700 space-y-1 list-disc list-inside">
                <li>Sticky Note Cinta</li>
                <li>‘Voice Note 30 Saat’</li>
                <li>Hadiah RM10 Yang Ikhlas</li>
              </ul>
            </div>
            <div class="rad-card rounded-2xl border p-6">
              <h3 class="font-semibold text-maroon">6) Audit Halus & Rutin</h3>
              <p class="text-slate-700 mt-2">Jejak tarikh, ruang refleksi 3 baris, dan kalendar ‘Kita’ supaya cinta jadi tabiat, bukan projek sekali-sekala.</p>
              <ul class="mt-3 text-sm text-slate-700 space-y-1 list-disc list-inside">
                <li>Audit Halus 3 Baris</li>
                <li>Kalendar Kita</li>
                <li>Hadiah Diri: Teruskan 7 Hari</li>
              </ul>
            </div>
          </div>

          <div class="text-center">
            <div class="inline-flex flex-col items-center gap-4">
              <button wire:click="addToCart" id="openCartMid" class="btn-cta rounded-full px-10 py-4 text-lg font-semibold text-white shadow-glow transform hover:scale-105 transition-all duration-300">
                Beli Hari Ini
              </button>
              <p class="text-sm text-slate-500 italic">✨ Mulakan perjalanan cinta yang lebih bermakna</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="max-w-5xl mx-auto px-4 py-20">
      <div class="glass rounded-3xl p-8 md:p-12 shadow-luxury border relative overflow-hidden">
        <!-- Decorative background -->
        <div class="absolute inset-0 bg-gradient-to-br from-champagne/20 via-cream/30 to-pearl/20 pointer-events-none"></div>

        <div class="relative">
          <div class="text-center mb-8">
            <h2 class="font-display text-4xl md:text-5xl text-gradient leading-tight mb-4">
              Ditulis dengan hati oleh <br class="hidden md:block"/>
              <span class="text-maroon">Kamalia Kamal</span> (Kak Kay)
            </h2>
          </div>

          <div class="grid md:grid-cols-[1fr_300px] gap-8 items-center">
            <div>
              <p class="text-xl md:text-2xl text-slate-700 leading-relaxed font-light mb-6">
                Kak Kay pernah berada di fasa <em class="text-mulberry font-medium">"letih menunggu rasa"</em>. Lalu beliau pilih jalan yang lebih mudah: <strong class="text-maroon font-semibold">buat dulu</strong> walau kecil.
              </p>

              <div class="grid sm:grid-cols-3 gap-4 mb-6">
                <div class="text-center p-4 bg-white/60 rounded-2xl border border-rose/10">
                  <div class="text-2xl mb-2">💌</div>
                  <p class="text-sm text-slate-600">Tulis nota comel</p>
                </div>
                <div class="text-center p-4 bg-white/60 rounded-2xl border border-rose/10">
                  <div class="text-2xl mb-2">🍜</div>
                  <p class="text-sm text-slate-600">Masak mi telur goyang</p>
                </div>
                <div class="text-center p-4 bg-white/60 rounded-2xl border border-rose/10">
                  <div class="text-2xl mb-2">🤲</div>
                  <p class="text-sm text-slate-600">Genggam tangan doa</p>
                </div>
              </div>

              <div class="p-6 bg-gradient-to-r from-champagne/30 to-cream/30 rounded-2xl border border-rose/10">
                <p class="text-lg text-slate-700 font-medium italic">
                  "Hasilnya? Rumah terasa hangat semula. Buku ini merangkum pengalaman itu menjadi langkah yang jelas supaya pasangan lain dapat merasai kelegaan yang sama: <strong class="text-maroon not-italic">bahagia itu boleh diusahakan</strong>."
                </p>
              </div>
            </div>

            <div class="flex justify-center">
              <div class="relative group">
                <div class="absolute -inset-2 rounded-3xl blur-xl opacity-50 bg-gradient-to-r from-rose/20 via-orchid/20 to-maroon/20 group-hover:opacity-75 transition-opacity duration-500"></div>
                <div class="relative glass rounded-3xl p-8 text-center shadow-elegant">
                  <div class="mx-auto w-24 h-24 rounded-full bg-gradient-to-br from-maroon via-rose to-blush flex items-center justify-center text-white font-bold text-2xl shadow-luxury mb-4">
                    KK
                  </div>
                  <h3 class="font-display text-xl font-bold text-maroon mb-2">Kamalia Kamal</h3>
                  <p class="text-sm text-slate-600 mb-4">Penulis & Kaunselor</p>
                  <blockquote class="text-maroon font-medium italic">
                    "Usaha kecil. Hati besar."
                  </blockquote>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- <!-- STORY / AUTHOR -->
    <section class="max-w-4xl mx-auto px-4 py-16">
      <h2 class="font-display text-3xl md:text-4xl text-maroon">Ditulis dengan hati oleh Kamalia Kamal (Kak Kay)</h2>
      <p class="mt-4 text-[17px] text-slate-700 leading-relaxed">Kak Kay pernah berada di fasa “letih menunggu rasa”. Lalu beliau pilih jalan yang lebih mudah: <i>buat dulu</i> walau kecil. Tulis nota comel, masak mi telur goyang, genggam tangan ketika doa. Hasilnya? Rumah terasa hangat semula. Buku ini merangkum pengalaman itu menjadi langkah yang jelas supaya pasangan lain dapat merasai kelegaan yang sama: <b>bahagia itu boleh diusahakan</b>.</p>
    </section>

    <!-- HOW IT WORKS -->
    <section class="max-w-5xl mx-auto px-4 pb-10">
      <div class="grid md:grid-cols-3 gap-6">
        <div class="rad-card rounded-2xl border p-6">
          <div class="text-2xl font-extrabold text-maroon">1</div>
          <h3 class="font-semibold text-maroon mt-2">Baca 5 minit</h3>
          <p class="text-slate-700">Pilih satu teknik. Satu halaman pun cukup.</p>
        </div>
        <div class="rad-card rounded-2xl border p-6">
          <div class="text-2xl font-extrabold text-maroon">2</div>
          <h3 class="font-semibold text-maroon mt-2">Buat malam ini</h3>
          <p class="text-slate-700">Ikut cadangan waktu & pantang ringkas.</p>
        </div>
        <div class="rad-card rounded-2xl border p-6">
          <div class="text-2xl font-extrabold text-maroon">3</div>
          <h3 class="font-semibold text-maroon mt-2">Rasa perubahannya</h3>
          <p class="text-slate-700">Catat 3 baris refleksi. Esok cuba teknik lain.</p>
        </div>
      </div>
      <div class="text-center mt-8">
        <button id="openCartLow" class="btn-cta rounded-full px-8 py-3 font-semibold text-white shadow-glow">Tambah ke Troli</button>
      </div>
    </section> --}}

    <!-- TESTIMONIALS -->
    <section class="max-w-6xl mx-auto px-4 py-16">
      <div class="text-center">
        <h2 class="font-display text-3xl md:text-4xl text-maroon">Apa kata pasangan yang mencuba</h2>
        <p class="mt-3 text-slate-700">Gantikan dengan testimoni sebenar anda—contoh di bawah sebagai pemegang tempat.</p>
      </div>
      <div class="mt-10 grid md:grid-cols-3 gap-6">
        <figure class="rad-card rounded-2xl border p-6">
          <blockquote class="italic text-slate-700">“Teknik #1 kami cuba masa hujan. Simple sangat tapi rasa rapat tu datang balik. Anak pun pelik tengok kami asyik senyum.”</blockquote>
          <figcaption class="mt-4 text-sm text-slate-500">— I & S, Shah Alam</figcaption>
        </figure>
        <figure class="rad-card rounded-2xl border p-6">
          <blockquote class="italic text-slate-700">“Bahasanya lembut, langkahnya jelas. Buku paling ‘boleh buat’ yang pernah kami beli.”</blockquote>
          <figcaption class="mt-4 text-sm text-slate-500">— Aisyah</figcaption>
        </figure>
        <figure class="rad-card rounded-2xl border p-6">
          <blockquote class="italic text-slate-700">“Audit halus tu power, kami boleh sembang tanpa defensive. Highly recommended.”</blockquote>
          <figcaption class="mt-4 text-sm text-slate-500">— Farhan & Mira</figcaption>
        </figure>
      </div>
    </section>

    <!-- VALUE STACK / OFFER -->
    {{-- <section class="max-w-7xl mx-auto px-4 py-16">
      <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div class="rad-card rounded-3xl border p-8">
          <h2 class="font-display text-3xl text-maroon">Apa yang anda dapat</h2>
          <ul class="mt-5 space-y-3 text-slate-700">
            <li class="flex gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-rose"></span><span><b>Buku 34 Teknik Bercinta</b> (pilih Edisi Digital atau Fizikal).</span></li>
            <li class="flex gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-rose"></span><span><b>Bonus #1:</b> Template <i>Sticky Note Cinta</i> (PDF, boleh cetak).</span></li>
            <li class="flex gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-rose"></span><span><b>Bonus #2:</b> Senarai lagu nostalgia untuk temani momen berdua.</span></li>
            <li class="flex gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-rose"></span><span><b>Bonus #3:</b> Checklist “Boleh Siap Dalam 15 Minit”.</span></li>
          </ul>
          <div class="mt-6 p-4 rounded-2xl bg-rose/5 border text-sm text-slate-700">Jaminan: Cuba <b>2 teknik</b> pertama. Jika langsung tak membantu, hubungi kami dalam 7 hari untuk penyelesaian terbaik. Kami mahu hasil untuk rumah tangga anda.</div>
        </div>
        <div class="rad-card rounded-3xl border p-8 text-center">
          <div class="inline-flex items-center gap-2 badge px-3 py-1 rounded-full text-xs text-maroon font-semibold">Harga Pelancaran</div>
          <div class="mt-3 font-display text-4xl text-maroon">Pilih Edisi Anda</div>
          <div class="mt-6 grid sm:grid-cols-2 gap-6">
            <div class="rounded-2xl border p-6">
              <div class="text-sm font-semibold text-maroon uppercase tracking-wide">Edisi Digital (PDF)</div>
              <div class="mt-2 text-3xl font-display text-maroon">RM<span id="priceE">39</span></div>
              <p class="mt-2 text-slate-700 text-sm">Muat turun segera. Baca di telefon / tablet.</p>
              <button data-variant="Edisi Digital" class="addCart btn-cta mt-5 w-full rounded-xl px-5 py-3 font-semibold text-white">Tambah ke Troli</button>
            </div>
            <div class="rounded-2xl border p-6">
              <div class="text-sm font-semibold text-maroon uppercase tracking-wide">Edisi Fizikal (Cetak)</div>
              <div class="mt-2 text-3xl font-display text-maroon">RM<span id="priceP">59</span></div>
              <p class="mt-2 text-slate-700 text-sm">Kertas berkualiti, penghantaran seluruh Malaysia.</p>
              <button data-variant="Edisi Fizikal" class="addCart btn-cta mt-5 w-full rounded-xl px-5 py-3 font-semibold text-white">Tambah ke Troli</button>
            </div>
          </div>
          <p class="mt-4 text-xs text-slate-500">*Kemaskan harga mengikut promosi semasa.</p>
        </div>
      </div>
    </section> --}}

    <!-- FAQ -->
    <section class="max-w-4xl mx-auto px-4 pb-24">
      <h2 class="font-display text-3xl md:text-4xl text-center text-maroon">Soalan Lazim</h2>
      <div class="mt-8 divide-y">
        <details class="group py-5">
          <summary class="cursor-pointer font-semibold text-maroon flex items-center justify-between">Adakah tekniknya mahal? <span class="transition-transform group-open:rotate-45">＋</span></summary>
          <p class="mt-3 text-slate-700">Tidak. Fokus pada usaha kecil yang jujur—nota cinta, genggam tangan, picnic dalam bilik—bukan kejutan mewah.</p>
        </details>
        <details class="group py-5">
          <summary class="cursor-pointer font-semibold text-maroon flex items-center justify-between">Sesuai untuk pasangan sibuk? <span class="transition-transform group-open:rotate-45">＋</span></summary>
          <p class="mt-3 text-slate-700">Ya. Banyak teknik siap dalam 15–30 minit, boleh diselit dalam rutin harian.</p>
        </details>
        <details class="group py-5">
          <summary class="cursor-pointer font-semibold text-maroon flex items-center justify-between">Perlu ikut turutan #1 ke #34? <span class="transition-transform group-open:rotate-45">＋</span></summary>
          <p class="mt-3 text-slate-700">Tidak. Pilih mana-mana teknik, baca bersama, dan praktik. Buku ini boleh dibuka bila-bila masa sebagai “pengingat cinta”.</p>
        </details>
        <details class="group py-5">
          <summary class="cursor-pointer font-semibold text-maroon flex items-center justify-between">Bagaimana penghantaran edisi fizikal? <span class="transition-transform group-open:rotate-45">＋</span></summary>
          <p class="mt-3 text-slate-700">Kami pos seluruh Malaysia setiap hari bekerja. Nombor tracking akan diberikan melalui WhatsApp/Emel.</p>
        </details>
        <details class="group py-5">
          <summary class="cursor-pointer font-semibold text-maroon flex items-center justify-between">Boleh hadiahkan kepada orang lain? <span class="transition-transform group-open:rotate-45">＋</span></summary>
          <p class="mt-3 text-slate-700">Boleh! Masukkan alamat penerima (untuk fizikal) atau emel penerima (untuk digital).</p>
        </details>
      </div>
      <div class="text-center mt-2">
        <button wire:click="addToCart" id="openCartBottom" class="btn-cta rounded-full px-8 py-3 font-semibold text-white shadow-glow">Beli Hari Ini</button>
      </div>
    </section>
  </main>

              {{-- <div class="mt-4 space-y-3">
              <div class="text-center">
                <h4 class="text-lg font-semibold text-white">{{ $product->name }}</h4>
                <p class="text-pink-300 text-lg font-bold">RM {{ number_format($product->price / 100, 2) }}</p>
              </div>

              <div class="flex justify-center">
                @livewire('add-to-cart', ['product' => $product], key($product->id))
              </div>
            </div> --}}

  <!-- FOOTER -->
  <footer class="bg-maroon text-white">
    <div class="max-w-6xl mx-auto px-4 py-10 text-center">
      <p class="font-display text-xl">34 Teknik Bercinta</p>
      <p class="text-white/90 text-sm mt-2">Hak cipta © <span id="year"></span> Kamalia Kamal. Semua hak terpelihara.</p>
    </div>
  </footer>

  <!-- FLOATING BAR CTA (Mobile) -->
  <div class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/80 backdrop-blur border-t border-rose/20">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
      <div>
        <div class="text-[13px] text-slate-600">Hidupkan semula rasa</div>
        <div class="text-lg font-display text-maroon -mt-1">RM50</div>
      </div>
      <button id="openCartFloat" class="btn-cta rounded-full px-5 py-2.5 text-white font-semibold">Order Sekarang!</button>
    </div>
  </div>

  <!-- CART MODAL / DRAWER -->
  <div id="cartModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="absolute right-0 top-0 h-full w-full sm:w-[460px] bg-white shadow-2xl p-6 overflow-y-auto">
      <div class="flex items-center justify-between">
        <h3 class="font-display text-2xl text-maroon">Beli Hari Ini</h3>
        <button id="closeCart" class="text-slate-500 hover:text-rose">✕</button>
      </div>
      <div class="mt-6 grid grid-cols-[80px_1fr] gap-4">
        <img src="{{  asset('images/cover/cara-bercinta.png') }}" class="w-20 h-40 object-cover rounded-xl border" alt="Buku 34 Teknik Bercinta"/>
        <div>
          <div class="font-semibold">34 Teknik Bercinta — Kamalia Kamal</div>
          <div class="text-sm text-slate-600">Pilih edisi, kuantiti & terus checkout.</div>
        </div>
      </div>
      <div class="mt-6 space-y-4">
        <div>
          <label class="text-sm font-medium text-slate-700">Edisi</label>
          <div class="mt-2 grid grid-cols-2 gap-3">
            <button data-variant="Edisi Digital" class="variantBtn rounded-xl border p-3 text-sm font-semibold">Edisi Digital (RM39)</button>
            <button data-variant="Edisi Fizikal" class="variantBtn rounded-xl border p-3 text-sm font-semibold">Edisi Fizikal (RM59)</button>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium text-slate-700">Kuantiti</label>
            <div class="mt-2 inline-flex items-center rounded-xl border px-2">
              <button id="qtyDec" class="px-3 py-2 text-lg">−</button>
              <input id="qty" type="number" value="1" min="1" class="w-14 text-center py-2 outline-none"/>
              <button id="qtyInc" class="px-3 py-2 text-lg">＋</button>
            </div>
          </div>
          <div>
            <label class="text-sm font-medium text-slate-700">Harga</label>
            <div class="mt-2 text-2xl font-display text-maroon">RM<span id="modalPrice">39</span></div>
            <div class="text-xs text-slate-500">Harga sementara (boleh ubah di kod).</div>
          </div>
        </div>
        <div class="p-4 rounded-2xl bg-rose/5 border text-sm text-slate-700">
          <b>Bonus ikut serta:</b> Template Sticky Note Cinta (PDF), Senarai Lagu Nostalgia, Checklist 15 Minit.
        </div>
        <div class="flex flex-col gap-3">
          <button id="confirmAdd" class="btn-cta rounded-xl px-6 py-3 text-white font-semibold">Sahkan & Tambah</button>
          <a id="checkoutBtn" href="https://wa.me/XXXXXXXXXX?text=Saya%20nak%20order%2034%20Teknik%20Bercinta" class="rounded-xl px-6 py-3 text-center font-semibold border bg-white hover:bg-rose/5">Terus WhatsApp Untuk Checkout</a>
          <p class="text-xs text-slate-500">*Gantikan nombor WhatsApp anda pada pautan di atas, atau tukar kepada link payment (ToyyibPay/Stripe) mengikut sistem anda.</p>
        </div>
      </div>
    </div>
  </div>

{{--  <script>--}}
{{--    // Basic cart state (front-end only)--}}
{{--    const state = { variant: 'Edisi Digital', price: 39, qty: 1 };--}}

{{--    const cartModal = document.getElementById('cartModal');--}}
{{--    const openers = ['openCartTop','openCartMid','openCartLow','openCartBottom','openCartFloat']--}}
{{--      .map(id => document.getElementById(id)).filter(Boolean);--}}
{{--    const addCartButtons = Array.from(document.querySelectorAll('.addCart'));--}}
{{--    const closeCart = document.getElementById('closeCart');--}}
{{--    const modalPrice = document.getElementById('modalPrice');--}}
{{--    const qtyInput = document.getElementById('qty');--}}
{{--    const qtyDec = document.getElementById('qtyDec');--}}
{{--    const qtyInc = document.getElementById('qtyInc');--}}
{{--    const confirmAdd = document.getElementById('confirmAdd');--}}
{{--    const checkoutBtn = document.getElementById('checkoutBtn');--}}

{{--    const variantBtns = Array.from(document.querySelectorAll('.variantBtn'));--}}

{{--    function openModal() { cartModal.classList.remove('hidden'); document.body.style.overflow='hidden'; }--}}
{{--    function closeModal() { cartModal.classList.add('hidden'); document.body.style.overflow=''; }--}}
{{--    function setVariant(v) {--}}
{{--      state.variant = v;--}}
{{--      state.price = v.includes('Fizikal') ? 59 : 39;--}}
{{--      modalPrice.textContent = (state.price * state.qty).toFixed(0);--}}
{{--      variantBtns.forEach(b=>{--}}
{{--        if (b.dataset.variant === v) b.classList.add('bg-rose/10','border-rose');--}}
{{--        else b.classList.remove('bg-rose/10','border-rose');--}}
{{--      });--}}
{{--      // update WhatsApp intent--}}
{{--      const wa = `https://wa.me/XXXXXXXXXX?text=Saya%20nak%20order%20${encodeURIComponent('34 Teknik Bercinta')}%20-%20${encodeURIComponent(state.variant)}%20x${state.qty}%20(RM${(state.price*state.qty).toFixed(0)})`;--}}
{{--      checkoutBtn.href = wa;--}}
{{--    }--}}
{{--    function setQty(q) {--}}
{{--      state.qty = Math.max(1, q);--}}
{{--      qtyInput.value = state.qty;--}}
{{--      modalPrice.textContent = (state.price * state.qty).toFixed(0);--}}
{{--      setVariant(state.variant);--}}
{{--    }--}}

{{--    openers.forEach(btn=>btn && btn.addEventListener('click', openModal));--}}
{{--    addCartButtons.forEach(btn=>btn.addEventListener('click', ()=>{ setVariant(btn.dataset.variant); openModal(); }));--}}
{{--    closeCart.addEventListener('click', closeModal);--}}
{{--    cartModal.addEventListener('click', (e)=>{ if(e.target===cartModal) closeModal(); });--}}

{{--    variantBtns.forEach(b=> b.addEventListener('click', ()=> setVariant(b.dataset.variant)));--}}

{{--    qtyDec.addEventListener('click', ()=> setQty(state.qty-1));--}}
{{--    qtyInc.addEventListener('click', ()=> setQty(state.qty+1));--}}
{{--    qtyInput.addEventListener('change', ()=> setQty(parseInt(qtyInput.value || '1',10)));--}}

{{--    confirmAdd.addEventListener('click', ()=>{--}}
{{--      const items = JSON.parse(localStorage.getItem('cartItems')||'[]');--}}
{{--      items.push({ sku: '34TB', name: '34 Teknik Bercinta', variant: state.variant, qty: state.qty, price: state.price });--}}
{{--      localStorage.setItem('cartItems', JSON.stringify(items));--}}
{{--      confirmAdd.textContent = 'Ditambah ✓';--}}
{{--      confirmAdd.disabled = true;--}}
{{--      setTimeout(()=>{ confirmAdd.textContent = 'Sahkan & Tambah'; confirmAdd.disabled = false; }, 1500);--}}
{{--    });--}}

{{--    // Initialize--}}
{{--    document.getElementById('year').textContent = new Date().getFullYear();--}}
{{--    setVariant(state.variant);--}}
{{--  </script>--}}
</div>
