<div class="container">
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

        {{-- <nav class="relative">
          <a class="pill" href="member"
            style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(255,105,180,0.3)'; this.style.backgroundColor='rgba(255,105,180,0.1)';"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.backgroundColor='';">KKDI Member</a>
          {{-- <a class="pill" href="#books">All Books</a>
          <a class="pill" href="#contact">Contact</a>
        </nav> --}}
      </div>
    </header>

    <section class="hero" style="padding: 2rem 0;">
      <div class="hero-inner">
        <div>
          {{-- <div class="eyebrow">Empowering Women</div> --}}
          <h2 class="title" style="font-family:'Caveat Brush'">Bantu You <span style="background:linear-gradient(90deg,var(--pink),var(--magenta)); -webkit-background-clip:text; background-clip:text; color:transparent;">Berdamai</span> Dengan Parents & Masa Lalu</h2>
          <p class="subtitle">
Kau tahu tak, wanita ni bukan sekadar pelengkap je… tapi dia boleh jadi punca perubahan. Suara dia mampu bergema, langkah dia boleh bukak jalan. Dalam yakin tu ada kekuatan, dalam lembut tu ada keberanian. Dan setiap wanita sebenarnya layak berdiri bangga, bina dunia dengan jiwa dia sendiri.          </p>
          <div class="cta-row">
            <a class="btn primary" href="#featured"
               style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
               onmouseover="this.style.transform='translateY(-3px) scale(1.05)'; this.style.boxShadow='0 15px 35px rgba(255,105,180,0.4), 0 5px 15px rgba(0,0,0,0.3)'; this.style.filter='brightness(1.1)';"
               onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow=''; this.style.filter='brightness(1)';">Buku Terhangat di Pasaran →</a>
            <a class="btn ghost" href="#books"
               style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
               onmouseover="this.style.transform='translateY(-2px)'; this.style.backgroundColor='rgba(255,255,255,0.1)'; this.style.borderColor='rgba(255,105,180,0.8)'; this.style.color='#ffffff';"
               onmouseout="this.style.transform='translateY(0)'; this.style.backgroundColor=''; this.style.borderColor=''; this.style.color='';">Karya Kak Kay</a>
          </div>
        </div>

        <div class="scene" aria-label="Hero visual with portrait and book stacks">
          <div class="ring" aria-hidden="true"></div>
          <img class="portrait" src="{{ asset('storage/images/kakkayhero.png') }}" alt="Confident portrait" style="width: clamp(220px, 36vw, 380px); aspect-ratio: 4/5; border-radius: calc(var(--radius) * 1.3); object-fit: cover; object-position: center 30%; box-shadow: var(--shadow-lg); position: relative; z-index: 1;">
          <div class="book b1"><span>Empowering Women</span></div>
          <div class="book b2"><span>Healing Yourself</span></div>
          <div class="book b3 only-desktop"><span>Calm Your Mind</span></div>
        </div>
      </div>
    </section>

    <!-- FEATURED BOOK SPOTLIGHT -->
    <section id="featured" class="section" style="padding: 2rem 0;">
      <div class="feature">
        <div class="glow-wrap">
          <div class="glow" aria-hidden="true"></div>
          <div class="big-book">
            <img src="{{ asset('storage/images/cover/' . $featuredProduct->slug . '.png') }}" alt="Book Cover" class="book-cover" />
            <div class="spine" aria-hidden="true"></div>
          </div>
        </div>
        <div>
          <h3>Buku Terhangat : <span style="background:linear-gradient(90deg,var(--pink),var(--ruby)); -webkit-background-clip:text; background-clip:text; color:transparent;"><br />{{ $featuredProduct->name }}</span></h3>
          <p>
            {{ $featuredProduct->description }}
          </p>
          <div class="meta" style="margin:.6rem 0 1.1rem;">
            <span class="chip">Bestseller</span>
            <span class="chip">New Release</span>
          </div>
          <div class="cta-row">
        <a class="btn primary" href="/{{ $featuredProduct->slug }}"
          style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
          onmouseover="this.style.transform='translateY(-3px) scale(1.05)'; this.style.boxShadow='0 15px 35px rgba(255,105,180,0.4), 0 5px 15px rgba(0,0,0,0.3)'; this.style.filter='brightness(1.1)';"
          onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow=''; this.style.filter='brightness(1)';">Nak tau pasal buku ni? 🤌 ❤️</a>
            {{-- <a class="btn ghost" href="#sample"
               style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
               onmouseover="this.style.transform='translateY(-2px)'; this.style.backgroundColor='rgba(255,255,255,0.1)'; this.style.borderColor='rgba(255,105,180,0.8)'; this.style.color='#ff69b4';"
               onmouseout="this.style.transform='translateY(0)'; this.style.backgroundColor=''; this.style.borderColor=''; this.style.color='';">Baca Sedutan</a> --}}
          </div>
        </div>
      </div>
    </section>

    <!-- BOOKS GALLERY -->
    <section id="books" class="section" style="padding: 2rem 0;">
      <h3 style="font:900 clamp(1.2rem, 1.6vw + .6rem, 1.8rem) Montserrat, system-ui, sans-serif; margin:0 0 .6rem;">Karya Kak Kay</h3>
      <p style="margin:.2rem 0 1rem; color:#ffe9f5; opacity:.9;">Buku-buku ni best tau, ada yang sentuh hati sampai kita tersenyum, ada yang bagi semangat masa rasa nak give up, ada jugak yang macam bisik lembut suruh kita percaya dengan diri sendiri.</p>
      <div class="books-grid" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 2rem; margin-top: 2rem;">
        @foreach ($products as $product)
                  <a href="/{{ $product->slug }}" style="text-decoration: none; color: inherit;">

          <article class="card book-hover" style="flex: 0 1 200px; max-width: 300px; cursor: pointer; transition: box-shadow 0.35s cubic-bezier(0.4, 0, 0.2, 1), transform 0.35s cubic-bezier(0.4, 0, 0.2, 1); transform-origin: center;">
            <div class="cover book-cover-glow" style="background:linear-gradient(135deg, var(--purple), var(--pink)); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform-style: preserve-3d;">
              <img src="{{ asset('storage/images/cover/' . $product->slug . '.png') }}" alt="{{ $product->name }}">
              <div class="pages" aria-hidden="true"></div>
            </div>
            {{-- <h4>{{ $product->name }}</h4> --}}
            <div class="desc pt-2">{{ $product->description }}</div>
          </article>
        </a>
        @endforeach
      </div>
    </section>

  <div class="hidden justify-center">
        <nav class="text-center">
          <a class="pill" href="member"
            style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(255,105,180,0.3)'; this.style.backgroundColor='rgba(255,105,180,0.1)';"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'; this.style.backgroundColor='';">KKDI Member</a>
          {{-- <a class="pill" href="#books">All Books</a>
          <a class="pill" href="#contact">Contact</a> --}}
        </nav>
      </div>

  <x-footer />
</div>