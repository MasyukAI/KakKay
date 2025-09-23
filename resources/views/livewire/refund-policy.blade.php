<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.policy')]
#[Title('Dasar Pemulangan - Kak Kay')]
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
        <x-policy-hero eyebrow="Dasar Pemulangan" chip="Tanpa Risiko" title="Kepuasan Anda Adalah Keutamaan Kami" subtitle="Kami yakin dengan setiap produk Kak Kay. Jika ia tidak membantu anda, kami akan temani proses pemulangan tanpa banyak soal.">
            <div class="policy-highlight-card">
                <span class="text-xl">🔁</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Produk Digital</p>
                    <p class="text-white/85 text-sm">Jaminan pemulangan 7 hari</p>
                </div>
            </div>
            <div class="policy-highlight-card">
                <span class="text-xl">📦</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Produk Fizikal</p>
                    <p class="text-white/85 text-sm">Pertukaran sehingga 14 hari</p>
                </div>
            </div>
            <div class="policy-highlight-card">
                <span class="text-xl">💌</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Sokongan</p>
                    <p class="text-white/85 text-sm">Whatsapp & e-mel responsif</p>
                </div>
            </div>
        </x-policy-hero>



<section class="space-y-10">
    <div class="policy-card space-y-4">
        <p class="text-sm uppercase tracking-[0.28em] text-white/60">Berkuat kuasa: 3 September 2025</p>
        <p class="text-base leading-relaxed text-white/80">
            Dasar pemulangan ini direka supaya anda boleh membeli dengan yakin. Jika produk tidak memenuhi jangkaan, hubungi kami dan kami akan bantu mencari penyelesaian terbaik.
        </p>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">1. Produk Digital</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Pemulangan dibenarkan dalam tempoh <strong>7 hari</strong> selepas pembelian.</li>
                <li>Kami menggalakkan anda mencuba sekurang-kurangnya dua teknik atau modul yang disediakan.</li>
                <li>Jika masih tidak membantu, kami akan memproses pemulangan dalam 3-5 hari bekerja.</li>
                <li>Masalah akses atau pautan rosak akan diganti dengan fail baharu terlebih dahulu.</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">2. Produk Fizikal</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Pemulangan tersedia dalam tempoh <strong>14 hari</strong> selepas produk diterima.</li>
                <li>Produk mesti dalam keadaan asal dan bebas daripada kerosakan pengguna.</li>
                <li>Kos penghantaran pemulangan ditanggung pelanggan kecuali jika produk rosak atau salah.</li>
                <li>Pemulangan wang diproses selepas pemeriksaan produk (5-7 hari bekerja).</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">3. Cara Memohon Pemulangan</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ol class="list-decimal list-inside space-y-2 text-sm text-white/80">
                <li>Hubungi kami melalui e-mel <a href="mailto:kakkaylovesme@gmail.com" class="text-pink-200 underline">kakkaylovesme@gmail.com</a> atau WhatsApp <a href="https://wa.me/60138846594" class="text-pink-200 underline">+60 13-884 6594</a>.</li>
                <li>Sertakan nombor pesanan, nama penuh, dan sebab pemulangan.</li>
                <li>Lampirkan bukti seperti resit atau gambar produk (jika berkaitan).</li>
                <li>Kami akan memberi arahan lanjut dan mengesahkan kelayakan anda.</li>
            </ol>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">4. Maklumat Yang Diperlukan</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Nombor pesanan atau e-mel pembelian.</li>
                <li>Sebab pemulangan untuk membantu kami menambah baik.</li>
                <li>Gambar produk dan pembungkusan (untuk produk fizikal).</li>
                <li>Maklumat akaun atau rujukan pembayaran bagi pemulangan wang manual.</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">5. Tempoh Pemprosesan</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Respons awal dihantar dalam 24 jam bekerja.</li>
                <li>Pemulangan produk digital diselesaikan dalam 1-2 hari bekerja.</li>
                <li>Pemulangan produk fizikal diproses selepas produk diterima semula (5-7 hari bekerja).</li>
                <li>Pemulangan wang dibuat melalui kaedah pembayaran asal.</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">6. Penghantaran Rosak atau Salah</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <p>Jika parcel anda tiba dalam keadaan rosak atau item yang diterima tidak tepat, sila lakukan perkara berikut:</p>
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Hubungi kami dalam tempoh 3 hari bekerja selepas pesanan diterima.</li>
                <li>Sertakan foto/video yang menunjukkan kerosakan, nombor pesanan, dan butiran item.</li>
                <li>Simpan pembungkusan asal sehingga proses semakan selesai.</li>
            </ul>
            <p>Kami akan menilai laporan anda dalam masa 24 jam bekerja dan menyediakan pilihan penggantian baharu atau pemulangan wang mengikut keadaan. Jika pemulangan diperlukan, kami akan mengeluarkan label penghantaran balikan dan menanggung kos tersebut.</p>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">7. Pengecualian</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Permintaan selepas tempoh jaminan tamat.</li>
                <li>Produk fizikal yang rosak akibat penggunaan tidak wajar.</li>
                <li>Produk digital yang telah diedarkan kepada pihak lain.</li>
                <li>Permohonan tanpa bukti sokongan yang mencukupi.</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">8. Komitmen Kami</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Kami mendengar pengalaman anda sebelum memutuskan tindakan seterusnya.</li>
                <li>Kami mungkin mencadangkan panduan tambahan jika itu lebih membantu.</li>
                <li>Kami sentiasa bersedia berbincang walaupun di luar tempoh jaminan.</li>
            </ul>
            <p class="text-sm text-white/75">Hubungi kami pada bila-bila masa — kami mahu perjalanan anda bersama Kak Kay penuh makna.</p>
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
