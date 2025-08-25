<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kak Kay - Counsellor • Therapist • KKDI Creator</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat+Brush&family=Montserrat:wght@700;800;900&family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Shadows+Into+Light&display=swap" rel="stylesheet">
  @vite('resources/css/home.css')
</head>
<body class="h-full bg-[#1a0d1a] bg-[image:var(--grad-hero)] text-white font-serif leading-[1.5] antialiased">
  <div class="container">
    <header>
      <div class="brand">
        <div class="logo" aria-hidden="true"></div>
        <div>
          <h1>Kak Kay</h1>
          <div class="tagline">Counsellor • Therapist • KKDI Creator</div>
        </div>
      </div>
      <nav>
        {{-- <a class="pill" href="#featured">Featured Book</a>
        <a class="pill" href="#books">All Books</a>
        <a class="pill" href="#contact">Contact</a> --}}
      </nav>
    </header>

    <section class="hero">
      <div class="hero-inner">
        <div>
          {{-- <div class="eyebrow">Empowering Women</div> --}}
          <h2 class="title" style="font-family:'Caveat Brush'">Bantu You <span style="background:linear-gradient(90deg,var(--pink),var(--magenta)); -webkit-background-clip:text; background-clip:text; color:transparent;">Berdamai</span> Dengan Parents & Masa Lalu</h2>
          <p class="subtitle">
Kau tahu tak, wanita ni bukan sekadar pelengkap je… tapi dia boleh jadi punca perubahan. Suara dia mampu bergema, langkah dia boleh bukak jalan. Dalam yakin tu ada kekuatan, dalam lembut tu ada keberanian. Dan setiap wanita sebenarnya layak berdiri bangga, bina dunia dengan jiwa dia sendiri.          </p>
          <div class="cta-row">
            <a class="btn primary" href="#featured">Buku Terhangat di Pasaran →</a>
            <a class="btn ghost" href="#books">Karya Kak Kay</a>
          </div>
        </div>

        <div class="scene" aria-label="Hero visual with portrait and book stacks">
          <div class="ring" aria-hidden="true"></div>
          <img class="portrait" src="https://kay.test/storage/kakkayhero.png" alt="Confident portrait" style="width: clamp(220px, 36vw, 380px); aspect-ratio: 4/5; border-radius: calc(var(--radius) * 1.3); object-fit: cover; object-position: center 30%; box-shadow: var(--shadow-lg); position: relative; z-index: 1;">
          <div class="book b1"><span>Hardcover Edition</span></div>
          <div class="book b2"><span>Softcover Anthology</span></div>
          <div class="book b3 only-desktop"><span>Collector’s Series</span></div>
        </div>
      </div>
    </section>

    <!-- FEATURED BOOK SPOTLIGHT -->
    <section id="featured" class="section">
      <div class="feature">
        <div class="glow-wrap">
          <div class="glow" aria-hidden="true"></div>
          <div class="big-book">
            <img src="{{ $featuredImageUrl }}" alt="Book Cover" class="book-cover" />
            <div class="spine" aria-hidden="true"></div>
          </div>
        </div>
        <div>
          <h3>Buku Terhangat : <span style="background:linear-gradient(90deg,var(--pink),var(--ruby)); -webkit-background-clip:text; background-clip:text; color:transparent;"><br />Cara Nak Bercinta ❤️</span></h3>
          <p>
            A masterclass in turning vision into reality. This signature title blends personal narrative, practical frameworks, and empowering insights—designed for creators, founders, and leaders who want to move with clarity and courage.
          </p>
          <div class="meta" style="margin:.6rem 0 1.1rem;">
            <span class="chip">Softcover • 320 pages</span>
            <span class="chip">Bestseller</span>
            <span class="chip">New Release</span>
          </div>
          <div class="cta-row">
            <a class="btn primary" href="#buy">Buy Now</a>
            <a class="btn ghost" href="#sample">Read a Sample</a>
          </div>
        </div>
      </div>
    </section>

    <!-- BOOKS GALLERY -->
    <section id="books" class="section">
      <h3 style="font:900 clamp(1.2rem, 1.6vw + .6rem, 1.8rem) Montserrat, system-ui, sans-serif; margin:0 0 .6rem;">Book Showcase</h3>
      <p style="margin:.2rem 0 1rem; color:#ffe9f5; opacity:.9;">Elegant, tactile covers presented in a responsive grid. Mobile‑friendly and beautifully organized.</p>
      <div class="books-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 2rem;">
        <article class="card">
          <div class="cover" style="background:linear-gradient(135deg, var(--purple), var(--pink));">
            <img src="{{ asset('storage/perkara-tak-boleh-cakap.png') }}" alt="Cara Cakap Dengan Dia">
            <div class="pages" aria-hidden="true"></div></div>
          <h4>Edge of Possibility</h4>
          <div class="desc">Strategies for creators navigating the modern economy.</div>
        </article>
        <article class="card">
          <div class="cover" style="background:linear-gradient(135deg, var(--magenta), var(--ruby));">
            <img src="{{ asset('storage/sebab-dia-terasa.png') }}" alt="Sebab Dia Terasa">
            <div class="pages" aria-hidden="true"></div></div>
          <h4>Quiet Power</h4>
          <div class="desc">Lead with clarity, design with intention, deliver with impact.</div>
        </article>
        <article class="card">
          <div class="cover" style="background:linear-gradient(135deg, var(--maroon), var(--ruby));">
            <img src="{{ asset('storage/kasihi-puteri.png') }}" alt="Sebab Dia Terasa">
            <div class="pages" aria-hidden="true"></div></div>
          <h4>Signals & Stories</h4>
          <div class="desc">A playbook for brand storytelling in an AI world.</div>
        </article>
        <article class="card">
          <div class="cover" style="background:linear-gradient(135deg, var(--pink), var(--magenta));">
            <img src="{{ asset('storage/cara-cakap.png') }}" alt="Cara Cakap Dengan Dia">
            <div class="pages" aria-hidden="true"></div></div>
          <h4>Cara Cakap Dengan Dia</h4>
          <div class="desc">Small moves that compound into breakthrough outcomes.</div>
        </article>
      </div>
    </section>

    <footer id="contact">
      <div class="container">
        <div style="display:flex; flex-wrap:wrap; gap:16px; align-items:center; justify-content:space-between;">
          <div>
            <strong style="font-family:Montserrat, system-ui, sans-serif;">Let’s collaborate</strong>
            <div>Speaking • Workshops • Consulting • Media</div>
          </div>
          <a class="btn primary" href="mailto:hello@example.com">kakkaylovesme@gmail.com
</a>
        </div>
      </div>
    </footer>
  </div>
</body>
</html>