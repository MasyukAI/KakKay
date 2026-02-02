<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Component;

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
        <x-policy-hero eyebrow="Terma & Syarat" chip="Disemak 3 September 2025" title="Kesepakatan Adil, Hubungan Harmoni" subtitle="Terma ini wujud untuk melindungi anda dan komuniti Kak Kay. Dengan menggunakan laman dan produk kami, anda bersetuju untuk mematuhi garis panduan ini.">
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
            Terma & Syarat ini mengatur penggunaan laman web dan pembelian produk daripada <strong>Kamalia Kamal Resources International (Kak Kay)</strong>. Dengan menggunakan perkhidmatan kami, anda bersetuju untuk mematuhi terma-terma berikut.
        </p>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">1. Penerimaan Terma</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <p>Dengan mengakses dan menggunakan laman web ini, anda bersetuju untuk terikat dengan Terma & Syarat ini. Jika anda tidak bersetuju dengan mana-mana bahagian terma ini, sila jangan gunakan perkhidmatan kami.</p>
            <div class="policy-mini-card">
                <p class="text-sm text-white/75"><strong>Nota:</strong> Terma ini boleh dikemas kini dari semasa ke semasa. Sila semak secara berkala untuk perubahan terkini.</p>
            </div>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">2. Maklumat Syarikat</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="policy-mini-card space-y-2">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.28em] text-white/70">Butiran Perniagaan</h3>
                    <div class="text-sm text-white/75 space-y-1">
                        <p><strong>Nama:</strong> Kamalia Kamal Resources International</p>
                        <p><strong>Jenis:</strong> Sendirian Berhad</p>
                        <p><strong>SSM:</strong> 202101019097 (1419397-X)</p>
                    </div>
                </div>
                <div class="policy-mini-card space-y-2">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.28em] text-white/70">Alamat Perniagaan</h3>
                    <div class="text-sm text-white/75">
                        <p>24, Jalan Pakis 1,</p>
                        <p>Taman Fern Grove,</p>
                        <p>43200 Cheras, Selangor</p>
                        <p>Malaysia</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">3. Produk & Perkhidmatan</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <p><strong>Produk yang Ditawarkan:</strong></p>
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>E-book dan buku bercetak mengenai pembangunan diri dan perhubungan</li>
                <li>Khidmat kaunseling dan terapi (jika berkenaan)</li>
                <li>Program pembangunan diri</li>
                <li>Kandungan digital dan bahan pembelajaran</li>
            </ul>
            <div class="policy-mini-card">
                <p class="text-sm text-white/75"><strong>Penafian:</strong> Produk kami adalah untuk tujuan pendidikan dan pembangunan diri. Ia bukan pengganti khidmat profesional seperti terapi psikologi klinikal atau kaunseling perubatan.</p>
            </div>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">4. Pembelian & Pembayaran</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <h3 class="text-sm font-semibold uppercase tracking-[0.28em] text-white/70">Proses Pembelian:</h3>
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Semua harga dinyatakan dalam Ringgit Malaysia (RM)</li>
                <li>Harga termasuk GST (jika berkenaan)</li>
                <li>Pembayaran mesti dibuat sepenuhnya sebelum produk dihantar</li>
            </ul>
            <h3 class="text-sm font-semibold uppercase tracking-[0.28em] text-white/70">Kaedah Pembayaran:</h3>
            <div class="grid grid-cols-2 gap-2 text-sm text-white/80 md:grid-cols-4">
                <div class="policy-mini-card py-3 text-center">FPX</div>
                <div class="policy-mini-card py-3 text-center">Visa</div>
                <div class="policy-mini-card py-3 text-center">Mastercard</div>
                <div class="policy-mini-card py-3 text-center">eWallet</div>
            </div>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">5. Hak Cipta & Harta Intelek</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <p><strong>Semua kandungan di laman web ini adalah hak cipta Kak Kay.</strong></p>
            <h3 class="text-sm font-semibold uppercase tracking-[0.28em] text-white/70">Yang Tidak Dibenarkan:</h3>
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Menyalin, mengedar, atau menjual semula produk digital tanpa kebenaran</li>
                <li>Berkongsi akaun atau maklumat login</li>
                <li>Menggunakan kandungan untuk tujuan komersial tanpa lesen</li>
                <li>Mengedit atau mengubah kandungan asal</li>
            </ul>
            <h3 class="text-sm font-semibold uppercase tracking-[0.28em] text-white/70">Yang Dibenarkan:</h3>
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Penggunaan peribadi untuk pembelajaran dan pembangunan diri</li>
                <li>Berkongsi testimoni dan ulasan yang jujur</li>
                <li>Merujuk kandungan dengan petikan yang sesuai</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">6. Tingkah Laku Pengguna</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <p><strong>Anda bersetuju untuk TIDAK:</strong></p>
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Menggunakan perkhidmatan untuk aktiviti yang menyalahi undang-undang</li>
                <li>Mengganggu atau merosakkan operasi laman web</li>
                <li>Menghantar spam atau kandungan yang tidak diingini</li>
                <li>Menyamar sebagai orang lain atau entiti lain</li>
                <li>Melanggar hak orang lain</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">7. Penafian & Had Liabiliti</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <div class="policy-mini-card space-y-2">
                <h3 class="text-sm font-semibold uppercase tracking-[0.28em] text-white/70">Penafian Penting</h3>
                <ul class="space-y-1 text-sm text-white/75">
                    <li>• Produk kami adalah untuk tujuan pendidikan dan motivasi sahaja</li>
                    <li>• Hasil individu mungkin berbeza-beza</li>
                    <li>• Tiada jaminan hasil atau kejayaan yang spesifik</li>
                    <li>• Bukan pengganti nasihat profesional dalam kesihatan mental</li>
                </ul>
            </div>
            <p>Kami tidak bertanggungjawab terhadap sebarang kerugian atau kerosakan yang timbul daripada penggunaan produk atau perkhidmatan kami, kecuali yang dikehendaki oleh undang-undang.</p>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">8. Penamatan</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <p>Kami berhak untuk menamatkan atau menggantung akses anda kepada perkhidmatan kami jika:</p>
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Anda melanggar Terma & Syarat ini</li>
                <li>Kami mengesyaki aktiviti penipuan atau menyalahi undang-undang</li>
                <li>Atas budi bicara kami dengan notis yang munasabah</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">9. Undang-undang yang Berkenaan</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <p>Terma & Syarat ini dikawal oleh undang-undang Malaysia. Sebarang pertikaian akan diselesaikan di mahkamah Malaysia yang mempunyai bidang kuasa.</p>
            <div class="policy-mini-card">
                <p class="text-sm text-white/75"><strong>Penyelesaian Pertikaian:</strong> Kami menggalakkan penyelesaian secara aman melalui perbincangan sebelum tindakan undang-undang.</p>
            </div>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">10. Perubahan Terma</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <p>Kami berhak untuk mengubah Terma & Syarat ini pada bila-bila masa. Perubahan akan berkuat kuasa serta-merta selepas disiarkan di laman web ini.</p>
            <p>Penggunaan berterusan perkhidmatan kami selepas perubahan menandakan penerimaan anda terhadap terma yang telah dikemas kini.</p>
        </div>
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
