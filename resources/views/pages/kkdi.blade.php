<x-layouts.pages>
    <x-slot:title>Ujian KKDI — Kak Kay</x-slot>

    @php
        $benefits = [
            ['title' => 'Kenal Diri Lebih Dalam', 'desc' => 'Fahami kekuatan, keperluan emosi, dan nilai diri anda.'],
            ['title' => 'Faham Corak Cinta', 'desc' => 'Kenal pasti corak hubungan yang berulang dan cara anda mencintai.'],
            ['title' => 'Buat Pilihan Lebih Sihat', 'desc' => 'Belajar membuat keputusan cinta yang selaras dengan diri sebenar.'],
            ['title' => 'Pulihkan Luka Lama', 'desc' => 'Kenal sumber luka emosi dan langkah penyembuhan yang membumi.'],
            ['title' => 'Hidup Lebih Selaras', 'desc' => 'Hubungkan nilai hidup, tujuan, dan hubungan yang lebih bermakna.'],
        ];

        $profiles = [
            ['name' => 'Penyayang', 'subtitle' => 'The Nurturer', 'tone' => 'bg-[#f8e8e3]'],
            ['name' => 'Pemikir', 'subtitle' => 'The Thinker', 'tone' => 'bg-[#edf2e6]'],
            ['name' => 'Pejuang', 'subtitle' => 'The Warrior', 'tone' => 'bg-[#f6ead8]'],
            ['name' => 'Pencinta Damai', 'subtitle' => 'The Peacemaker', 'tone' => 'bg-[#efe8f8]'],
            ['name' => 'Si Impian', 'subtitle' => 'The Dreamer', 'tone' => 'bg-[#e6eef5]'],
        ];
    @endphp

    <div class="storefront-shell storefront-shell-bottom relative overflow-hidden pb-16">
        <div class="relative z-10">
            <x-brand-header />

            <main class="space-y-12 pb-14 pt-6 sm:space-y-16 sm:pt-8">
                <section class="storefront-container">
                    <div class="grid gap-8 xl:grid-cols-[1.04fr_0.96fr] xl:items-center">
                        <div class="space-y-6">
                            <div class="storefront-eyebrow">Ujian KKDI</div>
                            <h1 class="font-display text-4xl leading-tight text-[var(--store-text)] sm:text-5xl xl:text-[4rem]">
                                Kenali diri.
                                <br>
                                Fahami emosi.
                                <br>
                                <span class="text-[var(--store-rose)]">Pahami corak cinta.</span>
                            </h1>
                            <p class="max-w-2xl text-lg text-[var(--store-text-soft)] sm:text-xl">
                                Ujian KKDI membantu anda memahami diri dengan lebih jelas supaya anda boleh membuat pilihan cinta yang lebih sihat, tenang, dan selaras dengan nilai anda.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <span class="storefront-badge">34 soalan</span>
                                <span class="storefront-badge">6–8 minit</span>
                                <span class="storefront-badge">100% sulit &amp; selamat</span>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <a href="mailto:hello@kakkay.my?subject=Saya%20ingin%20ambil%20Ujian%20KKDI" class="storefront-button-primary">Mulakan Ujian Sekarang</a>
                                <a href="#contoh-soalan" class="storefront-button-secondary">Bagaimana ia berfungsi?</a>
                            </div>
                        </div>

                        <div class="storefront-soft-card rounded-[2.4rem] p-4 sm:p-6">
                            <div class="grid gap-6 rounded-[2rem] bg-white/75 p-6 md:grid-cols-[0.9fr_1.1fr] md:items-center">
                                <img src="{{ asset('storage/images/kakkay.webp') }}" alt="Ujian KKDI" class="h-full min-h-[22rem] w-full rounded-[2rem] object-cover" />
                                <div class="space-y-4">
                                    <div class="text-5xl leading-none text-[#d8b6aa]">“</div>
                                    <p class="font-display text-2xl leading-tight text-[var(--store-text)] sm:text-3xl">Mengenali diri adalah langkah pertama untuk mencintai dengan lebih bijak.</p>
                                    <p class="text-sm uppercase tracking-[0.24em] text-[var(--store-text-soft)]">— Kak Kay</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="storefront-container space-y-6">
                    <div class="text-center">
                        <span class="storefront-divider">Kenapa ambil Ujian KKDI?</span>
                    </div>
                    <div class="grid gap-4 lg:grid-cols-5">
                        @foreach ($benefits as $benefit)
                            <article class="storefront-card rounded-[1.8rem] p-5 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#f5dfd6] text-[var(--store-rose)]">
                                    <flux:icon.sparkles class="h-6 w-6" />
                                </div>
                                <h2 class="mt-4 text-xl font-semibold text-[var(--store-text)]">{{ $benefit['title'] }}</h2>
                                <p class="mt-3 text-sm text-[var(--store-text-soft)]">{{ $benefit['desc'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="storefront-container" id="contoh-soalan">
                    <div class="grid gap-6 xl:grid-cols-[0.88fr_1.12fr] xl:items-center">
                        <div class="storefront-soft-card rounded-[2.2rem] p-6 sm:p-8">
                            <span class="storefront-eyebrow">Rasa ingin tahu?</span>
                            <h2 class="mt-2 font-display text-3xl text-[var(--store-text)] sm:text-4xl">Lihat contoh soalan Ujian KKDI</h2>
                            <p class="mt-4 text-lg text-[var(--store-text-soft)]">
                                Ujian ini direka dengan penuh empati untuk membantu anda refleksi diri dengan selamat dan bermakna.
                            </p>
                            <ul class="mt-6 space-y-3 text-[var(--store-text-soft)]">
                                <li class="flex items-center gap-3"><span class="storefront-badge">34</span> 34 soalan refleksi kendiri</li>
                                <li class="flex items-center gap-3"><span class="storefront-badge">♥</span> Berasaskan psikologi &amp; pengalaman sebenar</li>
                                <li class="flex items-center gap-3"><span class="storefront-badge">✓</span> 100% sulit, jawapan tidak disimpan</li>
                            </ul>
                            <a href="mailto:hello@kakkay.my?subject=Contoh%20Ujian%20KKDI" class="storefront-button-primary mt-8">Mulakan Ujian Sekarang</a>
                        </div>

                        <div class="storefront-card rounded-[2.2rem] p-6 sm:p-8">
                            <div class="flex items-center justify-between gap-4 text-sm text-[var(--store-text-soft)]">
                                <span>Kemajuan</span>
                                <span>7 / 34</span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-[#f1e2da]">
                                <div class="h-2 w-[22%] rounded-full bg-[var(--store-rose)]"></div>
                            </div>

                            <div class="mt-8 grid gap-6 xl:grid-cols-[1fr_0.36fr] xl:items-start">
                                <div>
                                    <div class="text-sm font-semibold uppercase tracking-[0.24em] text-[var(--store-text-soft)]">Soalan 7</div>
                                    <h3 class="mt-3 font-display text-3xl leading-tight text-[var(--store-text)]">Apabila saya berasa tidak dihargai, saya cenderung untuk…</h3>
                                    <div class="mt-6 space-y-3">
                                        @foreach (['Diam dan pendam perasaan', 'Menyatakan perasaan secara terus', 'Menarik diri dan beri ruang', 'Mencari cara untuk bahagiakan pasangan'] as $index => $option)
                                            <button type="button" class="flex w-full items-center gap-4 rounded-[1.25rem] border px-4 py-4 text-left text-sm {{ $index === 2 ? 'border-[color:var(--store-border-strong)] bg-[#fff1ea] text-[var(--store-rose)]' : 'border-[color:var(--store-border)] bg-white text-[var(--store-text-soft)]' }}">
                                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border {{ $index === 2 ? 'border-[color:var(--store-rose)]' : 'border-[color:var(--store-border)]' }}"></span>
                                                {{ $option }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <div class="mt-6 flex flex-wrap gap-3">
                                        <button type="button" class="storefront-button-secondary">← Sebelumnya</button>
                                        <button type="button" class="storefront-button-primary">Seterusnya →</button>
                                    </div>
                                </div>

                                <aside class="rounded-[1.6rem] border border-[color:var(--store-border)] bg-[#fff9f5] p-4 text-sm text-[var(--store-text-soft)]">
                                    <div class="font-semibold text-[var(--store-text)]">Tip Kak Kay</div>
                                    <p class="mt-3">Jawab dengan jujur tanpa fikirkan jawapan yang “betul”. Ujian ini bukan untuk menilai anda, tetapi untuk memahami anda dengan lebih baik.</p>
                                </aside>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="storefront-container space-y-6">
                    <div class="text-center">
                        <span class="storefront-divider">Kenali sebahagian profil KKDI</span>
                    </div>
                    <div class="grid gap-4 lg:grid-cols-5">
                        @foreach ($profiles as $profile)
                            <article class="storefront-card rounded-[1.8rem] p-5 text-center">
                                <div class="{{ $profile['tone'] }} mx-auto flex h-40 w-full items-end justify-center rounded-[1.5rem] px-4 py-5 text-[var(--store-text)] shadow-[inset_0_1px_0_rgba(255,255,255,0.5)]">
                                    <div>
                                        <div class="font-display text-3xl">{{ mb_substr($profile['name'], 0, 1) }}</div>
                                        <div class="mt-2 text-xs uppercase tracking-[0.24em] text-[var(--store-text-soft)]">Profil</div>
                                    </div>
                                </div>
                                <h3 class="mt-5 text-2xl font-semibold text-[var(--store-text)]">{{ $profile['name'] }}</h3>
                                <p class="mt-1 text-sm italic text-[var(--store-text-soft)]">{{ $profile['subtitle'] }}</p>
                                <button type="button" class="storefront-button-secondary mt-5 w-full text-sm">Lihat Profil</button>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="storefront-container space-y-6">
                    <div class="text-center">
                        <span class="storefront-divider">Apa kata mereka yang dah ambil Ujian KKDI</span>
                    </div>
                    <div class="grid gap-4 lg:grid-cols-3">
                        @foreach ([
                            'Ujian ini buat saya menangis sebab rasa tercermin sangat. Lepas ini saya lebih faham kenapa saya mudah overthink dalam hubungan.' => 'Aisyah, 28',
                            'KKDI bantu saya nampak corak yang selalu berulang dalam hidup saya. Sekarang saya lebih tenang dan pilih dengan lebih bijak.' => 'Farah, 31',
                            'Ringkas tapi sangat tepat. Terima kasih Kak Kay sebab cipta ujian yang sangat relatable dan membumi.' => 'Nora, 26',
                        ] as $quote => $author)
                            <article class="storefront-card rounded-[1.8rem] p-6">
                                <div class="storefront-rating text-lg">★★★★★</div>
                                <p class="mt-4 text-[var(--store-text)]">{{ $quote }}</p>
                                <p class="mt-4 text-sm font-medium text-[var(--store-text-soft)]">— {{ $author }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="storefront-container">
                    <div class="storefront-soft-card flex flex-col gap-5 rounded-[2.2rem] px-6 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                        <div>
                            <div class="font-display text-3xl text-[var(--store-text)] sm:text-4xl">Sedia untuk mengenali diri anda dengan lebih mendalam?</div>
                            <p class="mt-3 text-[var(--store-text-soft)]">Ambil Ujian KKDI sekarang dan mulakan perjalanan baharu ke arah cinta yang lebih sihat dan bermakna.</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="mailto:hello@kakkay.my?subject=Saya%20ingin%20ambil%20Ujian%20KKDI" class="storefront-button-primary">Mulakan Ujian Sekarang</a>
                            <a href="{{ route('consultation') }}" wire:navigate.hover class="storefront-button-secondary">Atau ketahui lebih lanjut →</a>
                        </div>
                    </div>
                </section>
            </main>

            <div class="storefront-container">
                <x-footer />
            </div>
        </div>
    </div>
</x-layouts.pages>