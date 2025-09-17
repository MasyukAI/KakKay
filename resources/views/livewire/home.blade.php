<div class="relative min-h-screen overflow-hidden bg-[#0f0218] text-white">
    <div class="pointer-events-none absolute -top-48 -left-32 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-pink-500/40 via-purple-500/25 to-rose-500/35 blur-3xl"></div>
    <div class="pointer-events-none absolute top-1/4 -right-36 h-[540px] w-[540px] rounded-full bg-gradient-to-br from-fuchsia-500/25 via-rose-500/25 to-orange-400/35 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-64 left-1/2 h-[520px] w-[520px] -translate-x-1/2 rounded-full bg-gradient-to-br from-purple-600/30 via-indigo-500/20 to-pink-400/30 blur-3xl"></div>

    <div class="relative z-10">
        <header class="mx-auto flex w-full max-w-7xl items-center justify-between px-6 pt-10 sm:px-8">
            <a href="/" class="flex items-center gap-4">
                <div class="logo" aria-hidden="true"></div>
                <div class="space-y-0.5">
                    <h1 class="text-xl font-black tracking-tight">Kak Kay</h1>
                    <div class="tagline text-xs sm:text-base">Counsellor • Therapist • KKDI Creator</div>
                </div>
            </a>
            {{-- <nav class="hidden items-center gap-6 text-sm font-semibold uppercase tracking-[0.22em] text-white/70 md:flex">
                <a href="#ritual" class="transition hover:text-white">Ritual Cinta</a>
                <a href="#featured" class="transition hover:text-white">Buku Terhangat</a>
                <a href="#library" class="transition hover:text-white">Karya</a>
                <a href="#community" class="transition hover:text-white">Komuniti</a>
            </nav> --}}
            @php
                $hasCartItems = ($cartQuantity ?? 0) > 0;
                $cartButtonBase = 'group relative flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition-all duration-300 ease-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0f0218]';
                $cartButtonPalette = $hasCartItems
                    ? 'bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 text-white shadow-[0_22px_52px_rgba(236,72,153,0.45)] ring-1 ring-white/20'
                    : 'border border-white/20 bg-white/10 text-white/80 shadow-[0_12px_32px_rgba(12,5,24,0.35)] hover:border-white/40 hover:text-white';
            @endphp
            <div class="flex items-center gap-4">
                <flux:button href="{{ route('cart') }}" class="{{ $cartButtonBase }} {{ $cartButtonPalette }} hover:-translate-y-0.5 hover:scale-[1.03] hover:shadow-[0_28px_70px_rgba(236,72,153,0.5)]">
                    <span class="pointer-events-none absolute inset-0 rounded-full opacity-0 transition duration-300 group-hover:opacity-50 {{ $hasCartItems ? 'bg-white/20' : 'bg-gradient-to-r from-pink-400/20 via-rose-400/10 to-purple-500/20' }}"></span>
                    <span class="pointer-events-none absolute -inset-1 rounded-full blur-xl opacity-0 transition duration-500 group-hover:opacity-60 {{ $hasCartItems ? 'bg-pink-500/30' : 'bg-white/15' }}"></span>
                    <flux:icon.shopping-bag class="relative z-10 h-5 w-5 {{ $hasCartItems ? 'text-white' : 'text-white/80' }}" />
                    <span class="relative z-10 hidden sm:inline">Troli</span>
                    <span class="absolute -top-2 -right-2 z-20">
                        @livewire('cart-counter')
                    </span>
                </flux:button>
            </div>
        </header>

        <main class="space-y-24 pb-24 sm:space-y-32">
            <!-- HERO -->
            <section class="relative">
                <div class="mx-auto flex max-w-7xl flex-col gap-16 px-6 pt-12 sm:px-8 lg:grid lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                    <div class="order-2 space-y-8 lg:order-1">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-5 py-2 text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Memperkasakan Wanita Zaman Moden</span>
                        <h1 style="font-family:'Caveat Brush'" 
                            class="font-display text-[4rem] leading-[0.92] tracking-tight sm:text-[4.5rem] lg:text-[5rem]">
                            Kak Kay bantu you <span class="bg-gradient-to-r from-pink-300 via-rose-400 to-orange-200 bg-clip-text text-transparent">berdamai</span> dengan Parents & Masa Lalu
                        </h1>
                        <p class="max-w-2xl text-lg leading-relaxed text-white/85 sm:text-xl">
                            Kau tahu tak, wanita ni bukan sekadar pelengkap je… tapi dia boleh jadi punca perubahan. Suara dia mampu bergema, langkah dia boleh bukak jalan. Dalam yakin tu ada kekuatan, dalam lembut tu ada keberanian. Dan setiap wanita sebenarnya layak berdiri bangga, bina dunia dengan jiwa dia sendiri.</p>
                        <div class="flex flex-wrap items-center gap-4">
                            <a href="/{{ $featuredProduct->slug }}" class="btn primary cart-button-primary flex items-center gap-3 rounded-full px-7 py-3 text-base font-semibold"
                                style="transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(0px) scale(1); filter: brightness(1);" onmouseover="this.style.transform='translateY(-3px) scale(1.05)'; this.style.boxShadow='0 15px 35px rgba(255,105,180,0.4), 0 5px 15px rgba(0,0,0,0.3)'; this.style.filter='brightness(1.1)';" onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow=''; this.style.filter='brightness(1)';"
                            >
                                <span>Buku Panas Dari Oven</span>
                                <flux:icon.arrow-up-right class="h-4 w-4" />
                            </a>
                            <a href="#library" class="btn ghost rounded-full border border-white/30 px-7 py-3 text-base font-semibold text-white/80 backdrop-blur-sm transition hover:border-white/60 hover:text-white"
                                style="transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(0px);" onmouseover="this.style.transform='translateY(-2px)'; this.style.backgroundColor='rgba(255,255,255,0.1)'; this.style.borderColor='rgba(255,105,180,0.8)'; this.style.color='#ffffff';" onmouseout="this.style.transform='translateY(0)'; this.style.backgroundColor=''; this.style.borderColor=''; this.style.color='';"
                            >
                                Karya Kak Kay
                            </a>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            @php($heroStats = [
                                ['label' => 'Sesi Bimbingan Peribadi', 'value' => '1K+'],
                                ['label' => 'Acara Berkumpulan', 'value' => '300+'],
                                ['label' => 'Ujian Karakter KKDI', 'value' => '250K+'],
                            ])
                            @foreach ($heroStats as $stat)
                                <div class="rounded-2xl border border-white/10 bg-white/10 p-4 text-sm text-white/80 shadow-[0_15px_35px_rgba(12,5,24,0.35)]">
                                    <div class="text-2xl font-semibold text-white">{{ $stat['value'] }}</div>
                                    <div class="mt-1 text-xs uppercase tracking-[0.28em] text-white/60">{{ $stat['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="order-1 relative flex justify-center lg:order-2 lg:justify-end">
                        <div class="group relative">
                            <div class="absolute -inset-6 rounded-[40px] bg-gradient-to-br from-pink-400/40 via-fuchsia-400/25 to-purple-500/35 opacity-80 blur-3xl transition duration-500 group-hover:opacity-100"></div>
                            <div class="relative overflow-hidden rounded-[36px] border border-white/15 bg-white/10 p-6 backdrop-blur-2xl shadow-[0_35px_110px_rgba(15,3,37,0.55)] transition duration-500 group-hover:-translate-y-1">
                                {{-- <div class="flex items-center justify-between text-xs uppercase tracking-[0.28em] text-white/60">
                                    <span>Sesi manja</span>
                                    <span>Real-time</span>
                                </div> --}}
                                <div class="relative overflow-hidden rounded-[28px]">
                                    <img src="{{ asset('storage/images/kakkayhero.png') }}" alt="Kak Kay" class="w-full rounded-[28px] object-cover shadow-[0_25px_60px_rgba(17,0,34,0.55)]">
                                    <div class="pointer-events-none absolute inset-0 border border-white/20"></div>
                                </div>
                                <div class="mt-6 grid gap-3 text-sm text-white/80">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-lg">💬</span>
                                        <div>
                                            <div class="font-semibold text-white">“Biar usaha kita kecil, tapi hati kita besar.”</div>
                                            <div class="text-xs text-white/60">– Kak Kay</div>
                                        </div>
                                    </div>
                                    {{-- <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-xs uppercase tracking-[0.3em] text-white/60">Ritual cinta • terapi lembut • jaga jiwa</div> --}}
                                </div>
                            </div>
                            <div class="absolute -right-16 -bottom-12 hidden w-48 rotate-12 rounded-3xl border border-white/10 bg-white/10 p-5 text-xs text-white/80 shadow-[0_20px_60px_rgba(15,3,37,0.4)] backdrop-blur-xl lg:block">
                                <div class="font-semibold text-white">Nota geng KKDI</div>
                                <p class="mt-2 leading-snug">“Setiap sesi Kak Kay rasa macam pelukan untuk hati. Terima kasih.”</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- RITUAL HIGHLIGHTS -->
            <section id="ritual">
                <div class="mx-auto grid max-w-6xl gap-6 rounded-[34px] border border-white/10 bg-white/5 px-6 py-10 backdrop-blur-xl shadow-[0_30px_90px_rgba(12,5,24,0.4)] sm:px-10 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="space-y-6">
                        <h2 class="font-display text-3xl text-white sm:text-4xl">Usaha kecil, impak besar.</h2>
                        <p class="text-base leading-relaxed text-white/80">
                            Pagi, petang, malam — ada skrip manja untuk setiap mood. Buku dan bengkel Kak Kay tolong pasangan kekal chill, patuh syariah, dan tak bosan urus kerja cinta hari-hari.
                        </p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 text-sm text-white/80">
                                <div class="text-lg font-semibold text-white">Ritual comel harian</div>
                                <p class="mt-2 text-white/70">3 gerak kecil yang jaga mood sayang.</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 text-sm text-white/80">
                                <div class="text-lg font-semibold text-white">Bahasa jiwa</div>
                                <p class="mt-2 text-white/70">Skrip puji, minta maaf & boost semangat.</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 text-sm text-white/80">
                                <div class="text-lg font-semibold text-white">Geng KKDI</div>
                                <p class="mt-2 text-white/70">Sokongan sis-sis seluruh Malaysia & Nusantara.</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 text-sm text-white/80">
                                <div class="text-lg font-semibold text-white">Sesi premium</div>
                                <p class="mt-2 text-white/70">Live coaching & bengkel pasangan fun tiap bulan.</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="rounded-[28px] border border-white/10 bg-gradient-to-br from-white/20 via-pink-200/10 to-purple-200/10 p-6 text-sm text-white/80 shadow-[0_25px_70px_rgba(15,3,37,0.4)]">
                            <h3 class="text-xl font-semibold text-white">Ritual malam 15 minit (versi manja)</h3>
                            <ul class="mt-3 space-y-2 text-white/75">
                                <li class="flex gap-3"><span class="mt-1 h-2 w-2 rounded-full bg-pink-300"></span>Picnic kecil depan TV.</li>
                                <li class="flex gap-3"><span class="mt-1 h-2 w-2 rounded-full bg-pink-300"></span>Soalan lembut: “Apa highlight manis harini?”</li>
                                <li class="flex gap-3"><span class="mt-1 h-2 w-2 rounded-full bg-pink-300"></span>3 pujian jujur sebelum tidur.</li>
                            </ul>
                            <p class="mt-4 text-xs uppercase tracking-[0.3em] text-white/60">Dari buku 34 Teknik Bercinta</p>
                        </div>
                        <div class="rounded-[28px] border border-white/10 bg-white/10 p-6 text-sm text-white/75">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-lg">🎧</span>
                                <div>
                                    <div class="font-semibold text-white">Playlist nostalgia</div>
                                    <p class="text-xs text-white/60">Lagu nostalgia curated Kak Kay untuk mood cuddles.</p>
                                </div>
                            </div>
                            <div class="mt-5 rounded-2xl border border-white/10 bg-white/5 p-4 text-xs text-white/65">
                                “Kami repeat picnic + playlist ni tiap Jumaat. Rumah terus wangi bahagia.” – Hani & Zikri
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FEATURED BOOK -->
            <section id="featured">
                <div class="mx-auto flex max-w-6xl flex-col gap-12 px-6 sm:px-8 lg:flex-row lg:items-center">
                    <div class="relative flex-1">
                        <div class="absolute -inset-8 rounded-[40px] bg-gradient-to-br from-pink-400/35 via-rose-500/25 to-purple-500/35 blur-3xl"></div>
                        <div class="relative overflow-hidden rounded-[36px] border border-white/15 bg-white/10 p-8 backdrop-blur-xl shadow-[0_35px_110px_rgba(15,3,37,0.55)]">
                            <div class="relative mx-auto w-full max-w-sm">
                                <div class="absolute inset-0 rounded-[30px] border border-white/20"></div>
                                <img src="{{ asset('storage/images/cover/' . $featuredProduct->slug . '.png') }}" alt="{{ $featuredProduct->name }}" class="relative w-full rounded-[30px] border border-white/20 object-cover shadow-[0_30px_80px_rgba(17,0,34,0.55)]">
                            </div>
                            {{-- <div class="mt-6 grid gap-3 text-sm text-white/80">
                                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-pink-200">Hot pick minggu ini</div>
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-xs text-white/60">
                                    Bonus: Template love note + soalan refleksi fun untuk pasangan.
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    <div class="flex-1 space-y-6">
                        <h2 class="font-display text-3xl text-white sm:text-4xl">{{ $featuredProduct->name }}</h2>
                        <p class="text-lg leading-relaxed text-white/80">{{ $featuredProduct->description }}</p>
                        <div class="flex flex-wrap items-center gap-3 text-sm uppercase tracking-[0.28em] text-white/60">
                            <span class="rounded-full border border-white/20 bg-white/10 px-4 py-1">Pilihan Top</span>
                            <span class="rounded-full border border-white/20 bg-white/10 px-4 py-1">Segar Dari Ladang</span>
                            <span class="rounded-full border border-white/20 bg-white/10 px-4 py-1">Versi Fizikal</span>
                        </div>
                        <div class="flex items-center gap-6">
                            <div>
                                <div class="text-xs uppercase tracking-[0.3em] text-white/60">Harga manja</div>
                                <div class="text-3xl font-semibold text-white">{{ \Akaunting\Money\Money::MYR($featuredProduct->price)->format() }}</div>
                            </div>
                            <a href="/{{ $featuredProduct->slug }}" class="btn primary cart-button-primary flex items-center gap-3 rounded-full px-8 py-3 text-base font-semibold"
                               style="transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); transform: translateY(0px) scale(1); filter: brightness(1);" onmouseover="this.style.transform='translateY(-3px) scale(1.05)'; this.style.boxShadow='0 15px 35px rgba(255,105,180,0.4), 0 5px 15px rgba(0,0,0,0.3)'; this.style.filter='brightness(1.1)';" onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow=''; this.style.filter='brightness(1)';"
                            >
                                <flux:icon.arrow-right class="h-5 w-5" />
                                Nak usha detail
                            </a>
                        </div>
                        <div class="grid gap-4 text-sm text-white/75 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                <div class="font-semibold text-white">Apa awak bakal rasa</div>
                                <p class="mt-2 text-white/70">Lebih tenang, yakin dan tahu cara mulakan borak manja setiap hari.</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                <div class="font-semibold text-white">Paling ngam untuk</div>
                                <p class="mt-2 text-white/70">Pasangan sibuk yang nak spark balik tanpa drama atau bajet besar.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- LIBRARY -->
            <section id="library" class="scroll-mt-40">
                <div class="mx-auto max-w-7xl px-6 sm:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h2 class="font-display text-3xl text-white sm:text-4xl">Rak cinta Kak Kay</h2>
                        <p class="mt-3 text-lg leading-relaxed text-white/80">Buku dan modul pilihan untuk topup rasa mesra, confident dan berani bersayang setiap hari.</p>
                    </div>
                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 justify-center">
                        @foreach ($products as $product)
                            <a href="/{{ $product->slug }}" class="group relative flex h-full flex-col overflow-hidden rounded-[30px] border border-white/15 bg-white/5 p-5 text-left text-white/80 shadow-[0_25px_70px_rgba(12,5,24,0.4)] transition duration-300 hover:-translate-y-2 hover:shadow-[0_35px_90px_rgba(236,72,153,0.3)]">
                                <div class="relative overflow-hidden rounded-[22px]">
                                    <img src="{{ asset('storage/images/cover/' . $product->slug . '.png') }}" alt="{{ $product->name }}" class="w-full rounded-[22px] border border-white/20 object-cover shadow-[0_20px_60px_rgba(17,0,34,0.45)]">
                                    <div class="absolute inset-0 bg-gradient-to-t from-[#0f0218]/80 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></div>
                                    <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between text-xs uppercase tracking-[0.28em] text-white/80">
                                        <span>{{ $product->category->name ?? 'Buku Kak Kay' }}</span>
                                        <span>{{ \Akaunting\Money\Money::MYR($product->price)->format() }}</span>
                                    </div>
                                </div>
                                <div class="mt-5 space-y-3">
                                    <h3 class="text-xl font-semibold text-white">{{ $product->name }}</h3>
                                    <p class="line-clamp-3 text-sm text-white/70">{{ $product->description }}</p>
                                    <div class="inline-flex items-center gap-2 text-sm font-semibold text-pink-200">
                                        Selak sekarang
                                        <flux:icon.arrow-right class="h-4 w-4" />
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    {{-- <div class="mt-10 flex justify-center">
                        <a href="{{ route('cart') }}" class="rounded-full border border-white/25 px-8 py-3 text-sm font-semibold uppercase tracking-[0.28em] text-white/70 transition hover:border-white/60 hover:text-white">Masuk troli & plan date night</a>
                    </div> --}}
                </div>
            </section>

            <!-- COMMUNITY -->
            <section id="community">
                <div class="mx-auto max-w-6xl rounded-[34px] border border-white/10 bg-white/5 px-6 py-12 backdrop-blur-xl shadow-[0_30px_90px_rgba(12,5,24,0.4)] sm:px-10">
                    <div class="mx-auto max-w-3xl text-center">
                        <h2 class="font-display text-3xl text-white sm:text-4xl">Cerita geng Kak Kay</h2>
                        <p class="mt-3 text-lg text-white/80">Pasangan dan sis yang decide untuk usaha bahagia dengan cara paling chill tapi penuh makna.</p>
                    </div>
                    @php($testimonials = [
                        ['quote' => 'Setiap ritual santai tapi power. Kami makin senang borak pasal hati tanpa beku.', 'name' => 'Aina & Firdaus, Kuala Lumpur'],
                        ['quote' => 'Buku Kak Kay ni macam kompas. Bila rasa jauh, selak satu teknik terus cuba malam tu.', 'name' => 'Hannah, Melaka'],
                        ['quote' => 'Komuniti KKDI buat saya rasa tak keseorangan. Sis-sis semua sokong dengan ikhlas.', 'name' => 'Siti, Brunei'],
                    ])
                    <div class="mt-10 grid gap-6 md:grid-cols-3">
                        @foreach ($testimonials as $testimonial)
                            <figure class="flex h-full flex-col justify-between rounded-[26px] border border-white/10 bg-white/10 p-6 text-white/80 shadow-[0_20px_60px_rgba(12,5,24,0.35)]">
                                <blockquote class="text-lg leading-relaxed text-white/85">“{{ $testimonial['quote'] }}”</blockquote>
                                <figcaption class="mt-6 text-sm uppercase tracking-[0.28em] text-white/60">— {{ $testimonial['name'] }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                    <div class="mt-12 grid gap-6 sm:grid-cols-3">
                        <div class="rounded-[26px] border border-white/10 bg-white/10 p-5 text-sm text-white/75">
                            <div class="text-lg font-semibold text-white">Workshop live</div>
                            <p class="mt-2 text-white/65">Bengkel santai untuk pasangan — modul latihan & sesi soal jawab.</p>
                        </div>
                        <div class="rounded-[26px] border border-white/10 bg-white/10 p-5 text-sm text-white/75">
                            <div class="text-lg font-semibold text-white">KKDI Circle</div>
                            <p class="mt-2 text-white/65">Komuniti tertutup untuk diskusi, doa bersama & accountability manis.</p>
                        </div>
                        <div class="rounded-[26px] border border-white/10 bg-white/10 p-5 text-sm text-white/75">
                            <div class="text-lg font-semibold text-white">Freebies manja</div>
                            <p class="mt-2 text-white/65">Nota doa, checklist date night & template sticky note cinta.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section>
                <div class="mx-auto flex max-w-5xl flex-col gap-6 rounded-[34px] border border-white/10 bg-gradient-to-br from-pink-500/15 via-rose-500/10 to-purple-500/15 px-6 py-10 text-center backdrop-blur-xl shadow-[0_30px_90px_rgba(15,3,37,0.4)] sm:px-12">
                    <h2 class="font-display text-3xl text-white sm:text-4xl">Jom mula ritual malam ni.</h2>
                    <p class="text-lg text-white/80">Pilih satu teknik, baca 5 minit, dan rasa perubahan lembut dalam rumah. Bahagia bukan nasib — kita usahakan sama-sama dengan Kak Kay.</p>
                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <a href="/{{ $featuredProduct->slug }}" class="cart-button-primary flex items-center gap-3 rounded-full px-8 py-3 text-base font-semibold">
                            <flux:icon.book-open class="h-5 w-5" />
                            Grab buku</a>
                        <a href="{{ route('checkout') }}" class="rounded-full border border-white/30 px-8 py-3 text-base font-semibold text-white/80 backdrop-blur-sm transition hover:border-white/60 hover:text-white">
                            Join KKDI Circle
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <div class="container">
            <x-footer />
        </div>
    </div>
</div>
