<div class="container" style="padding: 0 1rem;">
    <header style="padding: 1.5rem 0;">
      <div class="brand">
        <div class="logo" aria-hidden="true"></div>
        <div>
          <h1>Kak Kay</h1>
          <div class="tagline">Counsellor • Therapist • KKDI Creator</div>
        </div>
      </div>
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

          <article class="card" style="flex: 0 1 200px; max-width: 300px; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform-origin: center;"
                   onmouseover="this.style.transform='translateY(-8px) scale(1.02)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.3), 0 0 30px rgba(255,105,180,0.2)'; this.querySelector('.cover').style.transform='rotateY(-5deg) rotateX(5deg)';"
                   onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='none'; this.querySelector('.cover').style.transform='rotateY(0) rotateX(0)';">
            <div class="cover" style="background:linear-gradient(135deg, var(--purple), var(--pink)); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform-style: preserve-3d;">
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

    <footer id="contact">
      <div>
        <div style="display:flex; flex-wrap:wrap; gap:24px; align-items:flex-start; justify-content:space-between; padding: 2rem 0;">
          <div class="text-center sm:text-left w-full sm:w-auto">
            <strong style="font-family:Montserrat, system-ui, sans-serif;">Let's collaborate</strong>
            <div class="mt-2">Speaking • Workshops • Consulting • Media</div>
          </div>
          <div class="w-full sm:w-auto flex flex-col items-center sm:items-end gap-4">
            <div style="display: flex; gap: 16px; align-items: center;">
              <a href="https://instagram.com/kamaliakamal" target="_blank" rel="noopener"
                 style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #E1306C, #F77737); color: white; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
                 onmouseover="this.style.transform='translateY(-2px) scale(1.1)'; this.style.boxShadow='0 8px 25px rgba(225,48,108,0.4)';"
                 onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='none';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
              </a>
              <a href="https://facebook.com/kamaliakamal" target="_blank" rel="noopener"
                 style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #1877F2; color: white; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
                 onmouseover="this.style.transform='translateY(-2px) scale(1.1)'; this.style.boxShadow='0 8px 25px rgba(24,119,242,0.4)';"
                 onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='none';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
              </a>
              <a href="https://tiktok.com/@kakkayloveme" target="_blank" rel="noopener"
                 style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #000; color: white; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
                 onmouseover="this.style.transform='translateY(-2px) scale(1.1)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.4)'; this.style.background='linear-gradient(135deg, #ff0050, #00f2ea)';"
                 onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='none'; this.style.background='#000';">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                </svg>
              </a>
              {{-- <a href="https://youtube.com/@kakkaylovesme" target="_blank" rel="noopener"
                 style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #FF0000; color: white; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
                 onmouseover="this.style.transform='translateY(-2px) scale(1.1)'; this.style.boxShadow='0 8px 25px rgba(255,0,0,0.4)';"
                 onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='none';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
              </a> --}}
            </div>
            <a class="btn primary" href="mailto:kakkaylovesme@gmail.com"
               style="transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
               onmouseover="this.style.transform='translateY(-3px) scale(1.05)'; this.style.boxShadow='0 15px 35px rgba(255,105,180,0.4), 0 5px 15px rgba(0,0,0,0.3)'; this.style.filter='brightness(1.1)';"
               onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow=''; this.style.filter='brightness(1)';">kakkaylovesme@gmail.com
            </a>
          </div>
        </div>
        
        <!-- Policy Links Section - Centered Below -->
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
