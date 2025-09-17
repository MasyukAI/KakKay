<?php

use Livewire\Attributes\{Layout, Title};
use Livewire\Volt\Component;

new
#[Layout('components.layouts.policy')]
#[Title('Dasar Privasi - Kak Kay')]
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
        <x-policy-hero eyebrow="Dasar Privasi" chip="Berkuat kuasa 3 September 2025" title="Privasi Anda, Amanah Kami" subtitle="Kami melindungi setiap maklumat yang anda kongsikan dengan Kak Kay. Ketahui bagaimana data anda digunakan, disimpan dan dijaga.">
            <div class="policy-highlight-card">
                <span class="text-xl">🔒</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Keselamatan</p>
                    <p class="text-white/85 text-sm">Enkripsi SSL & pemantauan berterusan</p>
                </div>
            </div>
            <div class="policy-highlight-card">
                <span class="text-xl">👀</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Transparensi</p>
                    <p class="text-white/85 text-sm">Maklumat dikumpul hanya untuk servis anda</p>
                </div>
            </div>
            <div class="policy-highlight-card">
                <span class="text-xl">🧑‍⚖️</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-white/70">Hak Milik Anda</p>
                    <p class="text-white/85 text-sm">Anda boleh akses, ubah atau padam data</p>
                </div>
            </div>
        </x-policy-hero>

        <section class="space-y-10">
            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">Maklumat Yang Kami Kumpul</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="policy-mini-card">
                        <h3 class="text-white font-semibold">Maklumat Peribadi</h3>
                        <ul class="space-y-1 text-sm text-white/75">
                            <li>Nama penuh</li>
                            <li>Alamat e-mel & nombor telefon</li>
                            <li>Alamat penghantaran & pengebilan</li>
                            <li>Maklumat pembayaran (diproses secara selamat)</li>
                        </ul>
                    </div>
                    <div class="policy-mini-card">
                        <h3 class="text-white font-semibold">Maklumat Automatik</h3>
                        <ul class="space-y-1 text-sm text-white/75">
                            <li>Alamat IP & jenis pelayar</li>
                            <li>Peranti yang digunakan</li>
                            <li>Halaman yang dilawati & tempoh lawatan</li>
                            <li>Cookies untuk memperibadikan pengalaman</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">Bagaimana Kami Menggunakan Data Anda</h2>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Memproses dan menghantar pesanan yang anda buat.</li>
                    <li>Menghantar produk digital (pautan muat turun) dan fizikal.</li>
                    <li>Berhubung mengenai pesanan, sokongan dan jaminan.</li>
                    <li>Menyampaikan kemas kini produk dan promosi (jika anda bersetuju).</li>
                    <li>Menambah baik laman web dan pengalaman pembelian.</li>
                    <li>Mematuhi kewajipan undang-undang yang berkenaan.</li>
                </ul>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">Perkongsian Maklumat</h2>
                <p class="text-base leading-relaxed text-white/80">Kami <strong>tidak</strong> menjual atau menyewa data anda. Maklumat hanya dikongsi apabila diperlukan bersama:</p>
                <ul class="space-y-2 text-sm text-white/80">
                    <li><strong>Penyedia Perkhidmatan:</strong> Rakan logistik, gateway pembayaran dan pembekal sistem.</li>
                    <li><strong>Keperluan Undang-undang:</strong> Apabila diwajibkan oleh undang-undang atau untuk melindungi hak kami.</li>
                    <li><strong>Kebenaran Anda:</strong> Hanya dengan kebenaran eksplisit anda.</li>
                </ul>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">Keselamatan Data</h2>
                <p class="text-base leading-relaxed text-white/80">Kami melabur dalam teknologi dan prosedur keselamatan untuk menjaga maklumat anda.</p>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Transaksi dilindungi oleh <strong>enkripsi SSL</strong>.</li>
                    <li>Akses data staf kami adalah terhad & dikawal.</li>
                    <li>Sistem kami sentiasa dikemas kini dan dipantau.</li>
                    <li>Pengesanan awal untuk aktiviti mencurigakan.</li>
                </ul>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">Hak Anda</h2>
                <p class="text-base leading-relaxed text-white/80">Anda sentiasa mengawal maklumat peribadi anda. Hubungi kami untuk:</p>
                <ul class="space-y-2 text-sm text-white/80">
                    <li>Mengakses data peribadi yang kami simpan.</li>
                    <li>Memperbetulkan maklumat yang tidak tepat.</li>
                    <li>Meminta data dipadam (selagi tidak bertentangan dengan undang-undang).</li>
                    <li>Menarik diri daripada komunikasi pemasaran.</li>
                    <li>Memfailkan aduan dengan pihak berkuasa perlindungan data.</li>
                </ul>
            </div>

            <div class="policy-card space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="font-display text-3xl text-white">Cookies & Analitik</h2>
                    <span class="policy-chip">Pilihan Anda</span>
                </div>
                <p class="text-base leading-relaxed text-white/80">Cookies membantu kami menjejak prestasi laman dan menyesuaikan kandungan. Anda boleh menyahaktifkan cookies melalui tetapan pelayar, tetapi beberapa fungsi mungkin terhad.</p>
            </div>

            <div class="policy-card space-y-4">
                <h2 class="font-display text-3xl text-white">Kemas Kini Dasar</h2>
                <p class="text-base leading-relaxed text-white/80">Kami mungkin mengemas kini dasar privasi dari semasa ke semasa. Sebarang perubahan akan diumumkan di laman ini dengan tarikh "Berkuat kuasa" yang baharu.</p>
            </div>
        </section>
        </div>
    </main>

        <div class="container pb-12">
            <x-footer />
        </div>
    </main>
    </div>
</div>
