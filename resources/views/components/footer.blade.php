<footer id="contact" class="pb-10 pt-8">
    <div class="storefront-card overflow-hidden rounded-[2.2rem]">
        <div class="grid gap-10 px-6 py-8 lg:grid-cols-[1.2fr_repeat(4,minmax(0,1fr))] lg:px-10 lg:py-10">
            <div class="space-y-5">
                <div>
                    <div class="font-brand text-4xl font-black tracking-[-0.05em] text-[var(--store-rose-dark)]">Kak Kay</div>
                    <p class="mt-3 max-w-sm text-sm text-[var(--store-text-soft)]">
                        Ruang selamat untuk wanita yang mahu pulih, kenal diri, dan membina cinta yang lebih tenang dengan ilmu dan bimbingan yang lembut.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="https://instagram.com/kamaliakamal" target="_blank" rel="noopener" class="storefront-icon-button !h-10 !w-10">
                        <span class="text-xs font-semibold">IG</span>
                    </a>
                    <a href="https://facebook.com/kamaliakamal" target="_blank" rel="noopener" class="storefront-icon-button !h-10 !w-10">
                        <span class="text-xs font-semibold">FB</span>
                    </a>
                    <a href="https://tiktok.com/@kakkayloveme" target="_blank" rel="noopener" class="storefront-icon-button !h-10 !w-10">
                        <span class="text-xs font-semibold">TT</span>
                    </a>
                    <a href="mailto:hello@kakkay.my" class="storefront-icon-button !h-10 !w-10">
                        <span class="text-xs font-semibold">✉</span>
                    </a>
                </div>

                <div class="rounded-[1.5rem] border border-[color:var(--store-border)] bg-white/70 p-4">
                    <div class="text-sm font-semibold text-[var(--store-text)]">Terima inspirasi dari Kak Kay</div>
                    <p class="mt-1 text-sm text-[var(--store-text-soft)]">Masukkan e-mel anda untuk nota hati, tips refleksi, dan info buku terbaru.</p>
                    <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                        <input type="email" placeholder="Masukkan e-mel anda" class="storefront-input min-w-0 flex-1 px-4 py-3 text-sm" />
                        <a href="mailto:hello@kakkay.my?subject=Langgan%20Inspirasi%20Kak%20Kay" class="storefront-button-primary whitespace-nowrap px-5 py-3 text-sm">Langgan</a>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-[0.24em] text-[var(--store-text)]">Pautan</h3>
                <ul class="mt-4 space-y-3 text-sm text-[var(--store-text-soft)]">
                    <li><a href="{{ route('home') }}" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Utama</a></li>
                    <li><a href="{{ route('kkdi') }}" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Ujian KKDI</a></li>
                    <li><a href="{{ route('books') }}" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Buku</a></li>
                    <li><a href="{{ route('consultation') }}" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Konsultasi</a></li>
                    <li><a href="{{ route('home') }}#tentang" class="transition hover:text-[var(--store-rose)]">Tentang</a></li>
                    <li><a href="{{ route('home') }}#sumber" class="transition hover:text-[var(--store-rose)]">Sumber</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-[0.24em] text-[var(--store-text)]">Buku Popular</h3>
                <ul class="mt-4 space-y-3 text-sm text-[var(--store-text-soft)]">
                    <li><a href="/cara-bercinta" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Macam Ni Rupanya Cara Nak Bercinta</a></li>
                    <li><a href="/diari-healing" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Diari Healing</a></li>
                    <li><a href="/kasihi-puteri" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Kasihi Puteri</a></li>
                    <li><a href="/kitab-kkdi" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Kitab KKDI</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-[0.24em] text-[var(--store-text)]">Bantuan</h3>
                <ul class="mt-4 space-y-3 text-sm text-[var(--store-text-soft)]">
                    <li><a href="{{ route('consultation') }}#soalan-lazim" class="transition hover:text-[var(--store-rose)]">Soalan Lazim</a></li>
                    <li><a href="/shipping-policy" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Penghantaran</a></li>
                    <li><a href="/refund-policy" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Pemulangan</a></li>
                    <li><a href="/terms-of-service" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Terma &amp; Syarat</a></li>
                    <li><a href="/privacy-policy" wire:navigate.hover class="transition hover:text-[var(--store-rose)]">Dasar Privasi</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-[0.24em] text-[var(--store-text)]">Hubungi</h3>
                <ul class="mt-4 space-y-3 text-sm text-[var(--store-text-soft)]">
                    <li><a href="mailto:hello@kakkay.my" class="transition hover:text-[var(--store-rose)]">hello@kakkay.my</a></li>
                    <li><a href="https://wa.me/60138846594" class="transition hover:text-[var(--store-rose)]">+60 13-884 6594</a></li>
                    <li>Cheras, Selangor</li>
                    <li>Isnin — Jumaat (10am — 6pm)</li>
                </ul>
            </div>
        </div>

        <div class="border-t border-[color:var(--store-border)] px-6 py-4 text-xs text-[var(--store-text-soft)] lg:px-10">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p>© 2026 Kak Kay. Hak cipta terpelihara.</p>
                <p>Dibina dengan hati untuk ruang yang lebih tenang, jelas, dan bermakna.</p>
            </div>
        </div>
    </div>
</footer>

