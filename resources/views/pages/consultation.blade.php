<x-layouts.pages>
    <x-slot:title>Konsultasi Bersama Kak Kay</x-slot>

    @php
        $sessionTypes = [
            ['title' => 'Sesi Peribadi', 'duration' => '60 minit', 'price' => 'RM395 / sesi', 'desc' => 'Untuk anda yang ingin memahami diri, emosi, arah hidup, atau hubungan dengan lebih yakin.', 'active' => true],
            ['title' => 'Sesi Pasangan', 'duration' => '90 minit', 'price' => 'RM590 / sesi', 'desc' => 'Untuk pasangan yang ingin memahami antara satu sama lain dengan lebih tenang dan matang.', 'active' => false],
            ['title' => 'Follow-Up Session', 'duration' => '45 minit', 'price' => 'RM250 / sesi', 'desc' => 'Sesuai untuk anda yang mahu menyemak kemajuan dan memperdalam pemahaman semasa.', 'active' => false],
            ['title' => 'Debrief KKDI', 'duration' => '60 minit', 'price' => 'RM250 / sesi', 'desc' => 'Sesi penerangan keputusan Ujian KKDI dan pelan tindakan seterusnya.', 'active' => false],
        ];

        $timeSlots = ['9:00 AM', '10:30 AM', '12:00 PM', '2:00 PM', '3:30 PM', '5:00 PM', '7:30 PM'];
        $durations = ['45 minit — RM250', '60 minit — RM395', '90 minit — RM590'];
        $expectations = [
            ['title' => 'Disambut dengan empati', 'desc' => 'Anda dilayan dengan ruang yang selamat, lembut, dan tanpa penghakiman.'],
            ['title' => 'Sesi mendalam & bermakna', 'desc' => 'Kak Kay akan mendengar, membantu anda melihat dengan lebih jelas, dan menyusun langkah seterusnya.'],
            ['title' => 'Panduan yang praktikal', 'desc' => 'Anda akan pulang dengan refleksi, strategi, dan langkah yang boleh dilaksanakan.'],
            ['title' => 'Perjalanan diteruskan', 'desc' => 'Kami bantu anda kekal bergerak supaya perubahan bukan sekadar sementara.'],
        ];
    @endphp

    <div class="storefront-shell storefront-shell-bottom relative overflow-hidden pb-16">
        <div class="relative z-10">
            <x-brand-header />

            <main class="space-y-12 pb-14 pt-6 sm:space-y-16 sm:pt-8">
                <section class="storefront-container">
                    <div class="grid gap-8 xl:grid-cols-[1.05fr_0.95fr] xl:items-center">
                        <div class="space-y-6">
                            <div class="storefront-eyebrow">Konsultasi bersama Kak Kay</div>
                            <h1 class="font-display text-4xl leading-tight text-[var(--store-text)] sm:text-5xl xl:text-[4rem]">
                                Anda didengar.
                                <br>
                                Anda difahami.
                                <br>
                                <span class="text-[var(--store-rose)]">Anda dibimbing.</span>
                            </h1>
                            <p class="max-w-2xl text-lg text-[var(--store-text-soft)] sm:text-xl">
                                Di sini, anda boleh menjadi diri sendiri. Kak Kay akan menemani anda memahami hati, menelusuri pola hubungan, dan mencari arah yang lebih jelas.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <span class="storefront-badge">Ruang selamat tanpa judgement</span>
                                <span class="storefront-badge">Empati &amp; pengalaman</span>
                                <span class="storefront-badge">Rahsia terjamin</span>
                            </div>
                        </div>

                        <div class="storefront-soft-card rounded-[2.4rem] p-4 sm:p-6">
                            <div class="grid gap-6 rounded-[2rem] bg-white/75 p-6 md:grid-cols-[0.9fr_1.1fr] md:items-center">
                                <img src="{{ asset('storage/images/kakkay.webp') }}" alt="Kak Kay" class="h-full min-h-[22rem] w-full rounded-[2rem] object-cover" />
                                <div class="space-y-4">
                                    <div class="text-5xl leading-none text-[#d8b6aa]">“</div>
                                    <p class="font-display text-2xl leading-tight text-[var(--store-text)] sm:text-3xl">Setiap hati ada ceritanya, setiap cerita ada jalan keluarnya.</p>
                                    <p class="text-sm uppercase tracking-[0.24em] text-[var(--store-text-soft)]">— Kak Kay</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="storefront-container space-y-6">
                    <div class="text-center">
                        <span class="storefront-divider">Pilih sesi yang sesuai untuk anda</span>
                    </div>
                    <div class="grid gap-4 xl:grid-cols-4">
                        @foreach ($sessionTypes as $type)
                            <article class="rounded-[2rem] border p-6 transition {{ $type['active'] ? 'border-[color:var(--store-border-strong)] bg-[#fff3ed] shadow-[0_24px_60px_rgba(181,88,99,0.12)]' : 'storefront-card' }}">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-[#f4ded5] text-[var(--store-rose)]">
                                        <flux:icon.user class="h-7 w-7" />
                                    </div>
                                    @if ($type['active'])
                                        <span class="storefront-badge">Pilihan popular</span>
                                    @endif
                                </div>
                                <h2 class="mt-5 text-2xl font-semibold text-[var(--store-text)]">{{ $type['title'] }}</h2>
                                <p class="mt-2 text-sm text-[var(--store-text-soft)]">{{ $type['desc'] }}</p>
                                <div class="mt-5 flex items-center justify-between text-sm text-[var(--store-text-soft)]">
                                    <span>{{ $type['duration'] }}</span>
                                    <span class="font-semibold text-[var(--store-rose)]">{{ $type['price'] }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="storefront-container">
                    <div class="storefront-soft-card rounded-[2.4rem] p-6 sm:p-8">
                        <div class="grid gap-6 xl:grid-cols-[1.15fr_0.95fr_0.95fr_1fr]">
                            <div class="rounded-[1.8rem] bg-white p-5 shadow-[0_16px_36px_rgba(120,87,72,0.08)]">
                                <h2 class="font-display text-2xl text-[var(--store-text)]">Pilih tarikh</h2>
                                <div class="mt-4 rounded-[1.5rem] border border-[color:var(--store-border)] bg-[#fffaf6] p-4">
                                    <div class="mb-4 flex items-center justify-between text-sm text-[var(--store-text-soft)]">
                                        <span>Mei 2026</span>
                                        <span>Sel • Rab • Kha</span>
                                    </div>
                                    <div class="grid grid-cols-7 gap-2 text-center text-sm text-[var(--store-text-soft)]">
                                        @foreach (range(1, 31) as $day)
                                            <div class="flex h-9 items-center justify-center rounded-full {{ $day === 22 ? 'bg-[var(--store-rose)] text-white' : 'bg-white text-[var(--store-text-soft)]' }}">
                                                {{ $day }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-[1.8rem] bg-white p-5 shadow-[0_16px_36px_rgba(120,87,72,0.08)]">
                                <h2 class="font-display text-2xl text-[var(--store-text)]">Pilih masa</h2>
                                <div class="mt-4 space-y-3">
                                    @foreach ($timeSlots as $slot)
                                        <button type="button" class="w-full rounded-[1rem] border px-4 py-3 text-left text-sm font-medium {{ $slot === '10:30 AM' ? 'border-[color:var(--store-border-strong)] bg-[#fff1ea] text-[var(--store-rose)]' : 'border-[color:var(--store-border)] bg-[#fffaf6] text-[var(--store-text-soft)]' }}">
                                            {{ $slot }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-[1.8rem] bg-white p-5 shadow-[0_16px_36px_rgba(120,87,72,0.08)]">
                                <h2 class="font-display text-2xl text-[var(--store-text)]">Pilih tempoh</h2>
                                <div class="mt-4 space-y-3">
                                    @foreach ($durations as $duration)
                                        <button type="button" class="w-full rounded-[1rem] border px-4 py-3 text-left text-sm font-medium {{ str_contains($duration, '60 minit') ? 'border-[color:var(--store-border-strong)] bg-[#fff1ea] text-[var(--store-rose)]' : 'border-[color:var(--store-border)] bg-[#fffaf6] text-[var(--store-text-soft)]' }}">
                                            {{ $duration }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rounded-[1.8rem] bg-white p-5 shadow-[0_16px_36px_rgba(120,87,72,0.08)]">
                                <h2 class="font-display text-2xl text-[var(--store-text)]">Pilih kaedah</h2>
                                <div class="mt-4 space-y-3">
                                    <div class="rounded-[1rem] border border-[color:var(--store-border-strong)] bg-[#fff1ea] p-4 text-sm text-[var(--store-text)]">
                                        <div class="font-semibold text-[var(--store-rose)]">Online (Google Meet)</div>
                                        <p class="mt-1 text-[var(--store-text-soft)]">Sesi dalam talian yang selamat dan fleksibel dari mana-mana.</p>
                                    </div>
                                    <div class="rounded-[1rem] border border-[color:var(--store-border)] bg-[#fffaf6] p-4 text-sm text-[var(--store-text)]">
                                        <div class="font-semibold">Fizikal (Shah Alam)</div>
                                        <p class="mt-1 text-[var(--store-text-soft)]">Sesi bersemuka di ruang konsultasi Kak Kay, Shah Alam, Selangor.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col gap-4 rounded-[1.8rem] border border-[color:var(--store-border)] bg-white px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-sm text-[var(--store-text-soft)]">Sesi dipilih</div>
                                <div class="mt-1 font-semibold text-[var(--store-text)]">Sesi Peribadi • 60 minit • Online • 22 Mei 2026 • 10:30 AM</div>
                            </div>
                            <div class="flex flex-col items-start gap-3 sm:items-end">
                                <div class="text-2xl font-semibold text-[var(--store-rose)]">RM395.00</div>
                                <a href="mailto:hello@kakkay.my?subject=Tempahan%20Sesi%20Konsultasi" class="storefront-button-primary">Teruskan Tempahan</a>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="storefront-container space-y-6">
                    <div class="text-center">
                        <span class="storefront-divider">Apa yang anda boleh jangkakan</span>
                    </div>
                    <div class="grid gap-4 lg:grid-cols-4">
                        @foreach ($expectations as $expectation)
                            <article class="storefront-card rounded-[1.8rem] p-6 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#f5dfd6] text-[var(--store-rose)]">
                                    <flux:icon.heart class="h-6 w-6" />
                                </div>
                                <h3 class="mt-5 text-xl font-semibold text-[var(--store-text)]">{{ $expectation['title'] }}</h3>
                                <p class="mt-3 text-sm text-[var(--store-text-soft)]">{{ $expectation['desc'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="storefront-container grid gap-6 xl:grid-cols-[1fr_1.1fr]" id="soalan-lazim">
                    <div class="storefront-card rounded-[2rem] p-6 sm:p-8">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <span class="storefront-eyebrow">Soalan lazim</span>
                                <h2 class="mt-2 font-display text-3xl text-[var(--store-text)]">Apa yang sering ditanya</h2>
                            </div>
                            <a href="https://wa.me/60138846594" class="storefront-button-secondary text-sm">WhatsApp kami</a>
                        </div>
                        <div class="mt-6 space-y-3">
                            @foreach ([
                                'Adakah sesi ini termasuk terapi atau rawatan?' => 'Sesi ini ialah sesi bimbingan dan refleksi, bukan rawatan klinikal atau terapi perubatan.',
                                'Bagaimana sesi berlangsung?' => 'Sesi berlangsung sama ada melalui Google Meet atau fizikal, dengan struktur yang lembut dan fokus pada keperluan anda.',
                                'Bolehkah saya menukar tarikh selepas membuat tempahan?' => 'Ya, anda boleh hubungi kami lebih awal untuk penjadualan semula tertakluk pada kekosongan.',
                                'Adakah sesi fizikal hanya di Shah Alam?' => 'Buat masa ini, ya. Sesi fizikal diadakan di Shah Alam sahaja.',
                            ] as $question => $answer)
                                <details class="rounded-[1.4rem] border border-[color:var(--store-border)] bg-[#fffaf6] p-4">
                                    <summary class="cursor-pointer list-none font-medium text-[var(--store-text)]">{{ $question }}</summary>
                                    <p class="mt-3 text-sm text-[var(--store-text-soft)]">{{ $answer }}</p>
                                </details>
                            @endforeach
                        </div>
                    </div>

                    <div class="storefront-soft-card rounded-[2rem] p-6 sm:p-8">
                        <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                            <img src="{{ asset('storage/images/kakkay.webp') }}" alt="Tentang Kak Kay" class="h-full min-h-[20rem] w-full rounded-[1.8rem] object-cover" />
                            <div class="space-y-5">
                                <div>
                                    <span class="storefront-eyebrow">Tentang Kak Kay</span>
                                    <h2 class="mt-2 font-display text-3xl text-[var(--store-text)]">Kaunselor, penulis, dan teman perjalanan anda</h2>
                                </div>
                                <p class="text-[var(--store-text-soft)]">
                                    Lebih 10 tahun membantu wanita memahami diri, membina hubungan yang sihat, dan mencari arah yang lebih bermakna. Pendekatan Kak Kay lembut, jelas, dan praktikal.
                                </p>
                                @if ($highlightedProduct)
                                    <a href="/{{ $highlightedProduct->slug }}" wire:navigate.hover class="storefront-card flex items-center gap-4 rounded-[1.5rem] p-4 no-underline">
                                        <img src="{{ asset('storage/images/cover/' . $highlightedProduct->slug . '.webp') }}" alt="{{ $highlightedProduct->name }}" class="h-28 w-20 rounded-[1rem] object-cover" />
                                        <div>
                                            <div class="text-sm text-[var(--store-text-soft)]">Buku pilihan Kak Kay</div>
                                            <div class="mt-1 text-lg font-semibold text-[var(--store-text)]">{{ $highlightedProduct->name }}</div>
                                            <div class="mt-2 text-sm text-[var(--store-rose)]">Dapatkan di sini →</div>
                                        </div>
                                    </a>
                                @endif
                            </div>
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