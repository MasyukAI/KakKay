<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.policy')]
#[Title('Terma & Syarat - Kak Kay')]
class extends Component {
    //
} ?>

<div class="relative isolate overflow-hidden bg-[#0f0218] text-white">
    <div class="pointer-events-none absolute -top-48 -left-36 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-pink-500/35 via-purple-500/25 to-rose-500/35 blur-3xl"></div>
    <div class="pointer-events-none absolute top-1/3 -right-32 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-fuchsia-500/30 via-rose-500/20 to-orange-400/30 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-[-260px] left-1/2 h-[520px] w-[520px] -translate-x-1/2 rounded-full bg-gradient-to-br from-purple-600/30 via-indigo-500/20 to-pink-400/35 blur-3xl"></div>

    <div class="relative z-10">
    <x-brand-header />

    <main class="pb-24">
        <div class="mx-auto max-w-7xl space-y-24 px-6 sm:px-8">
        <x-policy-hero eyebrow="Terma & Syarat" chip="Disemak 3 September 2025" title="Terma Perkhidmatan Kak Kay" subtitle="Terma ini wujud untuk melindungi anda dan komuniti Kak Kay. Dengan menggunakan laman dan produk kami, anda bersetuju untuk mematuhi garis panduan ini.">
            <div class="policy-highlight-card">
                <span class="text-xl">🤝</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Amanah</p>
                    <p class="text-white/85 text-sm">Kami menghormati hak & pengalaman anda</p>
                </div>
            </div>
            <div class="policy-highlight-card">
                <span class="text-xl">📚</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Penggunaan</p>
                    <p class="text-white/85 text-sm">Sumber untuk kegunaan peribadi & pasangan</p>
                </div>
            </div>
            <div class="policy-highlight-card">
                <span class="text-xl">🛡️</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Perlindungan</p>
                    <p class="text-white/85 text-sm">Harta intelek & komuniti Kak Kay dijaga</p>
                </div>
            </div>
        </x-policy-hero>

        <section class="space-y-10">
            <div class="policy-card space-y-4">
                <p class="text-sm uppercase tracking-[0.28em] text-white/60">Berkuat kuasa: 3 September 2025</p>
                <p class="text-base leading-relaxed text-white/80">
                    Terma & Syarat ini mengatur bagaimana anda menggunakan laman web, kandungan dan produk yang ditawarkan oleh <strong>Kamalia Kamal Resources International (Kak Kay)</strong>. Dengan membuat pembelian atau mengakses laman kami, anda bersetuju dengan terma-terma berikut.
                </p>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">1. Penerimaan Terma</h2>
                <p class="text-base leading-relaxed text-white/80">Dengan menggunakan laman web atau membeli produk daripada Kak Kay, anda bersetuju untuk mematuhi terma ini. Jika anda tidak bersetuju, sila hentikan penggunaan perkhidmatan kami.</p>
                <div class="policy-mini-card">
                    <p class="text-sm text-white/75">Terma mungkin dikemas kini dari semasa ke semasa. Perubahan akan diumumkan melalui laman ini.</p>
                </div>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">2. Penggunaan Produk</h2>
                <p class="text-base leading-relaxed text-white/80">Produk kami — digital atau fizikal — adalah untuk kegunaan peribadi anda dan pasangan sahaja. Semua kandungan dilindungi hak cipta.</p>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Dilarang mengedar, menyalin, atau menjual semula kandungan tanpa kebenaran bertulis.</li>
                    <li>Anda dibenarkan menyimpan salinan digital untuk rujukan sendiri.</li>
                    <li>Kami berhak menamatkan akses jika terdapat penyalahgunaan.</li>
                </ul>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">3. Pembelian &amp; Pembayaran</h2>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Harga adalah dalam Ringgit Malaysia (RM) dan tertakluk kepada promosi semasa.</li>
                    <li>Pembayaran diproses melalui gateway yang selamat (FPX, kad kredit/debit, e-wallet).</li>
                    <li>Anda bertanggungjawab memastikan maklumat pembayaran yang tepat.</li>
                    <li>Resit dan bukti pembelian dihantar ke e-mel anda secara automatik.</li>
                </ul>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">4. Dasar Pemulangan</h2>
                <p class="text-base leading-relaxed text-white/80">Rujuk <a href="/refund-policy" class="text-pink-200 underline">Dasar Pemulangan</a> untuk maklumat lengkap. Secara ringkas:</p>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Produk digital: jaminan 7 hari selepas mencuba teknik yang disyorkan.</li>
                    <li>Produk fizikal: pemulangan atau pertukaran dalam masa 14 hari.</li>
                    <li>Kami mungkin meminta bukti pembelian atau bukti kerosakan.</li>
                </ul>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">5. Hak Harta Intelek</h2>
                <p class="text-base leading-relaxed text-white/80">Segala kandungan, termasuk teks, grafik, modul latihan, dan video adalah hak milik Kak Kay.</p>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Kandungan hanya untuk kegunaan peribadi.</li>
                    <li>Pengedaran tanpa kebenaran boleh dikenakan tindakan.</li>
                    <li>Sebarang idea atau maklum balas yang diberikan menjadi hak kami untuk ditambah baik.</li>
                </ul>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">6. Perkongsian Komuniti</h2>
                <p class="text-base leading-relaxed text-white/80">Apabila berkongsi pengalaman atau testimoni, anda memberi kami kebenaran untuk memaparkannya (dengan identiti dirahsiakan jika diminta).</p>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Kami tidak akan berkongsi maklumat sensitif tanpa kebenaran.</li>
                    <li>Anda boleh menarik balik kebenaran pada bila-bila masa.</li>
                </ul>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">7. Had Tanggungjawab</h2>
                <p class="text-base leading-relaxed text-white/80">Produk Kak Kay dibina untuk membantu, namun hasil mungkin berbeza bagi setiap individu. Kami tidak bertanggungjawab atas kerugian tidak langsung yang berlaku akibat penggunaan produk.</p>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">8. Pindaan Terma</h2>
                <p class="text-base leading-relaxed text-white/80">Kami mungkin mengemas kini terma ini dari semasa ke semasa. Perubahan akan dimuat naik ke laman ini dengan tarikh berkuat kuasa yang baharu.</p>
            </div>
        </section>
        </div>
    </main>

        <div class="container">
            <x-footer />
        </div>
    </main>
    </div>
</div>
