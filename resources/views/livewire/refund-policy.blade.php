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
                <h2 class="font-display text-3xl text-white">Jaminan Kepuasan 100%</h2>
                <p class="text-base leading-relaxed text-white/80">
                    Kami mahu buku dan modul Kak Kay benar-benar membantu rumah tangga anda. Jika anda tidak merasai perubahan selepas mencuba, kami sedia memulangkan wang dan mendengar pengalaman anda.
                </p>
            </div>

            <div class="policy-card space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="font-display text-3xl text-white">📱 Produk Digital — Jaminan 7 Hari</h2>
                    <span class="policy-chip">Tanpa Risiko</span>
                </div>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Cuba sekurang-kurangnya <strong>dua teknik</strong> dalam tempoh 7 hari selepas pembelian.</li>
                    <li>Jika langsung tidak membantu, hubungi kami untuk pemulangan penuh.</li>
                    <li>Pemulangan wang diproses dalam <strong>3 – 5 hari bekerja</strong>.</li>
                </ul>
            </div>

            <div class="policy-card space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="font-display text-3xl text-white">📦 Produk Fizikal — Jaminan 14 Hari</h2>
                    <span class="policy-chip">Pertukaran Mudah</span>
                </div>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Permohonan dibuat dalam <strong>14 hari</strong> selepas menerima pesanan.</li>
                    <li>Produk mestilah dalam keadaan asal dan tidak digunakan.</li>
                    <li>Kos pos pemulangan ditanggung pelanggan kecuali rosak semasa penghantaran.</li>
                    <li>Pemulangan wang diproses selepas pemeriksaan (<strong>5 – 7 hari bekerja</strong>).</li>
                </ul>
            </div>

            <div class="policy-card space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="font-display text-3xl text-white">📝 Cara Memohon Pemulangan</h2>
                    <span class="policy-chip">3 Langkah Mudah</span>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="policy-mini-card text-center">
                        <div class="text-2xl mb-2">📧</div>
                        <p class="font-semibold text-white">Hubungi Kami</p>
                        <p class="text-sm text-white/75">E-mel ke <a href="mailto:kakkaylovesme@gmail.com" class="underline text-pink-200">kakkaylovesme@gmail.com</a> bersama nombor pesanan.</p>
                    </div>
                    <div class="policy-mini-card text-center">
                        <div class="text-2xl mb-2">📝</div>
                        <p class="font-semibold text-white">Cerita Ringkas</p>
                        <p class="text-sm text-white/75">Kongsi pengalaman anda supaya kami boleh tambah baik.</p>
                    </div>
                    <div class="policy-mini-card text-center">
                        <div class="text-2xl mb-2">💰</div>
                        <p class="font-semibold text-white">Terima Refund</p>
                        <p class="text-sm text-white/75">Wang dipulangkan ke kaedah pembayaran asal.</p>
                    </div>
                </div>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">Maklumat Yang Kami Perlukan</h2>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Nombor pesanan atau e-mel pembelian.</li>
                    <li>Sebab pemulangan (supaya kami boleh tambah baik).</li>
                    <li>Untuk produk fizikal: lampirkan gambar keadaan produk.</li>
                    <li>Maklumat akaun jika pemulangan wang perlu dibuat secara manual.</li>
                </ul>
            </div>

            <div class="policy-card space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="font-display text-3xl text-white">Tempoh Pemprosesan</h2>
                    <span class="policy-chip">Telus &amp; Pantas</span>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="policy-mini-card">
                        <h3 class="text-white font-semibold">Produk Digital</h3>
                        <ul class="space-y-1 text-sm text-white/75">
                            <li>Respons awal dalam 24 jam</li>
                            <li>Pengesahan pemulangan 1 – 2 hari bekerja</li>
                            <li>Pemulangan wang 3 – 5 hari bekerja</li>
                        </ul>
                    </div>
                    <div class="policy-mini-card">
                        <h3 class="text-white font-semibold">Produk Fizikal</h3>
                        <ul class="space-y-1 text-sm text-white/75">
                            <li>Respons awal dalam 24 jam</li>
                            <li>Pemeriksaan produk sebaik diterima semula</li>
                            <li>Pemulangan wang 5 – 7 hari bekerja</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">Pengecualian</h2>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Permohonan selepas tempoh jaminan.</li>
                    <li>Produk rosak kerana penggunaan tidak wajar.</li>
                    <li>Produk digital yang telah dikongsi atau diedarkan.</li>
                    <li>Permohonan tanpa sebab yang munasabah.</li>
                </ul>
            </div>

            <div class="policy-card space-y-6">
                <h2 class="font-display text-3xl text-white">Komitmen Kami</h2>
                <p class="text-base leading-relaxed text-white/80">Kami bukan sekadar menjual buku; kami mahu rumah tangga anda rasa lebih tenang dan penuh kasih. Setiap maklum balas membantu kami menulis bab baharu untuk pelanggan lain.</p>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>💡 Kami dengar pengalaman anda sebelum memproses pemulangan.</li>
                    <li>🎁 Kami akan cadangkan solusi alternatif jika itu lebih membantu.</li>
                    <li>🤝 Kami sedia membantu walaupun selepas tempoh jaminan — hubungi kami dan kita bincang.</li>
                </ul>
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
