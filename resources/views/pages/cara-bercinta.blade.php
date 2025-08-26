<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>34 Teknik Bercinta — Long-Form Sales Page</title>
  <meta name="description" content="Hidupkan semula rasa cinta dengan 34 teknik bercinta yang mudah, murah dan ikhlas oleh Kamalia Kamal. Long-form sales page tanpa menu dengan CTA Add to Cart." />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            ruby: '#9b111e',
            maroon: '#6b0f1a',
            rose: '#e11d48',
            blush: '#f472b6',
            orchid: '#7c3aed',
            mulberry: '#6d214f',
          },
          fontFamily: {
            sans: ['Poppins', 'ui-sans-serif', 'system-ui'],
            display: ['Playfair Display', 'ui-serif', 'Georgia']
          },
          boxShadow: {
            glow: '0 15px 45px rgba(155,17,30,.35)',
            soft: '0 12px 36px rgba(124,58,237,.22)',
            inset: 'inset 0 1px 0 rgba(255,255,255,.25), inset 0 -1px 0 rgba(0,0,0,.05)'
          }
        }
      }
    }
  </script>
  <style>
    .bg-velvet { background: radial-gradient(1200px 600px at 15% 5%, rgba(241,70,104,.18), transparent 60%), radial-gradient(1000px 700px at 85% 0%, rgba(124,58,237,.18), transparent 65%), radial-gradient(900px 580px at 50% 100%, rgba(155,17,30,.22), transparent 70%), linear-gradient(180deg, #fff 0%, #fff 6%, #fff 20%, #ffe8f1 55%, #fff 100%); }
    .hero { background: linear-gradient(135deg, #7c3aed 0%, #9b111e 45%, #e11d48 70%, #f472b6 100%); }
    .glass { backdrop-filter: blur(10px); background: linear-gradient(180deg, rgba(255,255,255,.85), rgba(255,255,255,.7)); }
    .btn-cta { background: linear-gradient(90deg, #9b111e, #e11d48, #7c3aed); background-size: 200% 200%; transition: background-position .6s ease, transform .2s ease, box-shadow .2s ease; }
    .btn-cta:hover { background-position: 100% 0; transform: translateY(-1px); box-shadow: 0 18px 40px rgba(155,17,30,.35); }
    .badge { background: linear-gradient(120deg, rgba(255,255,255,.8), rgba(255,255,255,.55)); box-shadow: 0 10px 24px rgba(124,58,237,.18); }
    .rad-card { background: radial-gradient(800px 400px at 0% 0%, rgba(244,114,182,.08), transparent 60%), radial-gradient(800px 400px at 100% 0%, rgba(124,58,237,.08), transparent 60%), #fff; }
  </style>
</head>
<body class="font-sans text-slate-800 bg-white selection:bg-blush/50 selection:text-maroon">

  <!-- HERO -->
  <header class="hero text-white">
    <div class="max-w-7xl mx-auto px-4 pt-14 pb-16 md:pb-24 grid md:grid-cols-2 gap-10 items-center">
      <div>
        <div class="inline-flex items-center gap-2 badge px-3 py-1 rounded-full text-xs text-maroon font-semibold">
          <span class="w-1.5 h-1.5 rounded-full bg-rose"></span>
          Pelancaran Edisi Istimewa
        </div>
        <h1 class="mt-4 font-display text-4xl md:text-6xl leading-[1.05]">
          “Macam ni rupanya <span class="underline decoration-blush decoration-4 underline-offset-8">cara nak bercinta</span>… <span class="text-blush">mudahnya lahai!</span>”
        </h1>
        <p class="mt-5 text-white/90 text-lg md:text-xl">Buku <b>34 Teknik Bercinta Dengan Pasangan</b> oleh <b>Kamalia Kamal (Kak Kay)</b> ialah panduan praktikal untuk <i>hidupkan semula rasa</i> — tanpa bajet besar, tanpa drama. Hanya usaha kecil yang ikhlas, konsisten, dan manis.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
          <button id="openCartTop" class="btn-cta shadow-glow rounded-full px-7 py-3 text-base font-semibold text-white">Tambah ke Troli</button>
          <a href="#learn" class="rounded-full px-7 py-3 text-base font-semibold bg-white/20 ring-1 ring-white/40 hover:bg-white/30">Baca Lagi</a>
        </div>
        <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3 text-[13px] text-white/90">
          <div class="bg-white/10 rounded-xl p-3">Mudah & Murah</div>
          <div class="bg-white/10 rounded-xl p-3">Mesra Syariah</div>
          <div class="bg-white/10 rounded-xl p-3">Sesuai Pasangan Sibuk</div>
          <div class="bg-white/10 rounded-xl p-3">34 Teknik Praktikal</div>
        </div>
      </div>
      <div class="relative">
        {{-- <div class="absolute -inset-2 md:-inset-3 rounded-3xl blur-2xl opacity-60" style="background: radial-gradient(closest-side, rgba(241,70,104,.5), transparent), radial-gradient(closest-side, rgba(124,58,237,.35), transparent)"></div> --}}
        <div class="relative">
          <img src="cara-bercinta-3d.png" alt="Muka depan buku 34 Teknik Bercinta" class="w-full justify-center align-middle max-w-80 aspect-[3/4] object-cover"/>
        </div>
      </div>
    </div>
  </header>

  <!-- LONG COPY -->
  <main class="bg-velvet" id="learn">
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
    <section class="max-w-6xl mx-auto px-4 py-4 md:py-8">
      <div class="rounded-[28px] border shadow-soft bg-white overflow-hidden">
        <div class="px-6 md:px-10 py-10 bg-gradient-to-br from-rose/10 via-white to-white">
          <h2 class="font-display text-3xl md:text-4xl text-maroon">Dalam buku ini, anda akan temui…</h2>
          <p class="mt-3 text-slate-700">34 teknik yang dibahagi kepada kategori mudah. Setiap teknik disertakan cadangan waktu, pantang ringkas dan tujuan—supaya anda boleh terus praktik malam ini.</p>
          <div class="mt-8 grid lg:grid-cols-3 gap-6">
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

          <div class="mt-10 text-center">
            <button id="openCartMid" class="btn-cta rounded-full px-8 py-3 font-semibold text-white shadow-glow">Tambah ke Troli</button>
          </div>
        </div>
      </div>
    </section>

    <!-- STORY / AUTHOR -->
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
    </section>

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
    <section class="max-w-7xl mx-auto px-4 py-16">
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
    </section>

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
        <button id="openCartBottom" class="btn-cta rounded-full px-8 py-3 font-semibold text-white shadow-glow">Tambah ke Troli</button>
      </div>
    </section>
  </main>

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
        <div class="text-[13px] text-slate-600">Edisi Digital dari</div>
        <div class="text-lg font-display text-maroon -mt-1">RM39</div>
      </div>
      <button id="openCartFloat" class="btn-cta rounded-full px-5 py-2.5 text-white font-semibold">Tambah ke Troli</button>
    </div>
  </div>

  <!-- CART MODAL / DRAWER -->
  <div id="cartModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="absolute right-0 top-0 h-full w-full sm:w-[460px] bg-white shadow-2xl p-6 overflow-y-auto">
      <div class="flex items-center justify-between">
        <h3 class="font-display text-2xl text-maroon">Tambah ke Troli</h3>
        <button id="closeCart" class="text-slate-500 hover:text-rose">✕</button>
      </div>
      <div class="mt-6 grid grid-cols-[80px_1fr] gap-4">
        <img src="cara-bercinta.png" class="max-w-80 aspect-[3/4] object-cover rounded-xl border" alt="Buku 34 Teknik Bercinta"/>
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

  <script>
    // Basic cart state (front-end only)
    const state = { variant: 'Edisi Digital', price: 39, qty: 1 };

    const cartModal = document.getElementById('cartModal');
    const openers = ['openCartTop','openCartMid','openCartLow','openCartBottom','openCartFloat']
      .map(id => document.getElementById(id)).filter(Boolean);
    const addCartButtons = Array.from(document.querySelectorAll('.addCart'));
    const closeCart = document.getElementById('closeCart');
    const modalPrice = document.getElementById('modalPrice');
    const qtyInput = document.getElementById('qty');
    const qtyDec = document.getElementById('qtyDec');
    const qtyInc = document.getElementById('qtyInc');
    const confirmAdd = document.getElementById('confirmAdd');
    const checkoutBtn = document.getElementById('checkoutBtn');

    const variantBtns = Array.from(document.querySelectorAll('.variantBtn'));

    function openModal() { cartModal.classList.remove('hidden'); document.body.style.overflow='hidden'; }
    function closeModal() { cartModal.classList.add('hidden'); document.body.style.overflow=''; }
    function setVariant(v) {
      state.variant = v;
      state.price = v.includes('Fizikal') ? 59 : 39;
      modalPrice.textContent = (state.price * state.qty).toFixed(0);
      variantBtns.forEach(b=>{
        if (b.dataset.variant === v) b.classList.add('bg-rose/10','border-rose');
        else b.classList.remove('bg-rose/10','border-rose');
      });
      // update WhatsApp intent
      const wa = `https://wa.me/XXXXXXXXXX?text=Saya%20nak%20order%20${encodeURIComponent('34 Teknik Bercinta')}%20-%20${encodeURIComponent(state.variant)}%20x${state.qty}%20(RM${(state.price*state.qty).toFixed(0)})`;
      checkoutBtn.href = wa;
    }
    function setQty(q) {
      state.qty = Math.max(1, q);
      qtyInput.value = state.qty;
      modalPrice.textContent = (state.price * state.qty).toFixed(0);
      setVariant(state.variant);
    }

    openers.forEach(btn=>btn && btn.addEventListener('click', openModal));
    addCartButtons.forEach(btn=>btn.addEventListener('click', ()=>{ setVariant(btn.dataset.variant); openModal(); }));
    closeCart.addEventListener('click', closeModal);
    cartModal.addEventListener('click', (e)=>{ if(e.target===cartModal) closeModal(); });

    variantBtns.forEach(b=> b.addEventListener('click', ()=> setVariant(b.dataset.variant)));

    qtyDec.addEventListener('click', ()=> setQty(state.qty-1));
    qtyInc.addEventListener('click', ()=> setQty(state.qty+1));
    qtyInput.addEventListener('change', ()=> setQty(parseInt(qtyInput.value || '1',10)));

    confirmAdd.addEventListener('click', ()=>{
      const items = JSON.parse(localStorage.getItem('cartItems')||'[]');
      items.push({ sku: '34TB', name: '34 Teknik Bercinta', variant: state.variant, qty: state.qty, price: state.price });
      localStorage.setItem('cartItems', JSON.stringify(items));
      confirmAdd.textContent = 'Ditambah ✓';
      confirmAdd.disabled = true;
      setTimeout(()=>{ confirmAdd.textContent = 'Sahkan & Tambah'; confirmAdd.disabled = false; }, 1500);
    });

    // Initialize
    document.getElementById('year').textContent = new Date().getFullYear();
    setVariant(state.variant);
  </script>
</body>
</html>