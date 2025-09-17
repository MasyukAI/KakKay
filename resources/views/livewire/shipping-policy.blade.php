<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.policy')]
#[Title('Dasar Penghantaran & Pengembalian - Kak Kay')]
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
        <x-policy-hero eyebrow="Dasar Penghantaran" chip="Kemaskini 2025" title="Penghantaran & Pengembalian Kak Kay" subtitle="Kami pastikan setiap buku, modul, dan hadiah digital sampai dengan selamat — terus ke pintu rumah anda.">
            <div class="policy-highlight-card">
                <span class="text-xl">🚚</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Penghantaran</p>
                    <p class="text-white/85 text-sm">3–5 hari bekerja (Semenanjung)</p>
                </div>
            </div>
            <div class="policy-highlight-card">
                <span class="text-xl">📦</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Pembungkusan</p>
                    <p class="text-white/85 text-sm">Setiap buku dibalut kalis air</p>
                </div>
            </div>
            <div class="policy-highlight-card">
                <span class="text-xl">💬</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Sokongan</p>
                    <p class="text-white/85 text-sm">WhatsApp dalam 24 jam</p>
                </div>
            </div>
        </x-policy-hero>

        <section class="space-y-10">
            <div class="policy-card space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="font-display text-3xl text-white">📱 Produk Digital (E-Book &amp; Modul)</h2>
                    <span class="policy-chip">Akses Serta-merta</span>
                </div>
                <p class="text-base leading-relaxed text-white/80">
                    Semua produk digital dihantar automatik ke e-mel dan akaun anda sebaik sahaja pembayaran disahkan. Jika pautan muat turun tamat tempoh, kami akan bantu hantar semula dalam masa 24 jam bekerja.
                </p>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="policy-mini-card">
                        <h3 class="text-white font-semibold">✅ Kelebihan Digital</h3>
                        <ul class="space-y-1 text-sm text-white/75">
                            <li>Akses segera selepas pembayaran</li>
                            <li>Tiada kos penghantaran</li>
                            <li>Boleh dibaca di telefon, tablet atau komputer</li>
                            <li>Backup sepanjang hayat</li>
                        </ul>
                    </div>
                    <div class="policy-mini-card">
                        <h3 class="text-white font-semibold">📧 Cara Menerima</h3>
                        <ul class="space-y-1 text-sm text-white/75">
                            <li>Pautan muat turun dihantar melalui e-mel</li>
                            <li>Pautan aktif selama 30 hari</li>
                            <li>Sokongan pelanggan tersedia jika pautan tamat tempoh</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="policy-card space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="font-display text-3xl text-white">📦 Produk Fizikal (Buku &amp; Kit)</h2>
                    <span class="policy-chip">Penghantaran Seluruh Malaysia</span>
                </div>
                <p class="text-base leading-relaxed text-white/80">
                    Pesanan diproses dalam 24 jam bekerja. Buku dibungkus kalis air dan dihantar menggunakan rakan logistik yang dipercayai untuk melindungi kandungan daripada kerosakan.
                </p>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="policy-mini-card">
                        <h3 class="text-white font-semibold">⏱️ Tempoh Penghantaran</h3>
                        <ul class="space-y-1 text-sm text-white/75">
                            <li>Semenanjung Malaysia: 3 – 5 hari bekerja</li>
                            <li>Sabah &amp; Sarawak: 5 – 7 hari bekerja</li>
                            <li>Singapura &amp; Brunei: 7 – 10 hari bekerja</li>
                        </ul>
                    </div>
                    <div class="policy-mini-card">
                        <h3 class="text-white font-semibold">💰 Kos Penghantaran</h3>
                        <ul class="space-y-1 text-sm text-white/75">
                            <li>RM9.90 (Semenanjung Malaysia)</li>
                            <li>RM15.90 (Sabah &amp; Sarawak)</li>
                            <li>RM25.00 (Singapura &amp; Brunei)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="policy-card space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="font-display text-3xl text-white">🚚 Aliran Penghantaran Kami</h2>
                    <span class="policy-chip">Langkah Demi Langkah</span>
                </div>
                <ol class="list-decimal space-y-3 pl-5 text-base leading-relaxed text-white/75">
                    <li><strong>Pengesahan Pesanan:</strong> Pesanan diproses dalam masa 24 jam selepas pembayaran disahkan.</li>
                    <li><strong>Pembungkusan Premium:</strong> Setiap buku dibalut menggunakan bahan kalis air dan penyerap hentakan.</li>
                    <li><strong>Nombor Penjejakan:</strong> Kami hantar nombor tracking melalui e-mel atau WhatsApp selepas parcel diposkan.</li>
                    <li><strong>Penghantaran:</strong> Penghantaran dilakukan pada hari bekerja (Isnin - Jumaat, kecuali cuti umum).</li>
                </ol>
            </div>

            <div class="policy-card space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="font-display text-3xl text-white">🔁 Pengembalian &amp; Pertukaran</h2>
                    <span class="policy-chip">Kami Jaga Kepuasan Anda</span>
                </div>
                <p class="text-base leading-relaxed text-white/80">
                    Jika parcel anda tiba dalam keadaan rosak atau cacat, hubungi kami segera. Kami akan bantu dengan penggantian baharu atau pengembalian bergantung kepada situasi.
                </p>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="policy-mini-card">
                        <h3 class="text-white font-semibold">📝 Syarat Pengembalian</h3>
                        <ul class="space-y-1 text-sm text-white/75">
                            <li>Permohonan dibuat dalam 3 hari bekerja selepas penerimaan</li>
                            <li>Sertakan foto/video sebagai bukti kerosakan</li>
                            <li>Item perlu berada dalam keadaan asal</li>
                        </ul>
                    </div>
                    <div class="policy-mini-card">
                        <h3 class="text-white font-semibold">📦 Cara Memohon</h3>
                        <ol class="list-decimal space-y-1 pl-5 text-sm text-white/75">
                            <li>Hubungi kami melalui WhatsApp @kakkay.official</li>
                            <li>Sertakan nombor pesanan dan bukti kerosakan</li>
                            <li>Kami sediakan label penghantaran balikan jika diperlukan</li>
                        </ol>
                    </div>
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
