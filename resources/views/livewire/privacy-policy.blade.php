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
        <p class="text-sm uppercase tracking-[0.28em] text-white/60">Berkuat kuasa: 3 September 2025</p>
        <p class="text-base leading-relaxed text-white/80">
            Dasar privasi ini menerangkan bagaimana Kak Kay mengumpul, menggunakan, dan melindungi maklumat peribadi anda semasa anda melawat laman web atau membeli produk kami.
        </p>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">1. Maklumat Yang Kami Kumpul</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Maklumat peribadi seperti nama, e-mel, nombor telefon, dan alamat penghantaran.</li>
                <li>Butiran pembayaran yang diproses dengan selamat melalui gerbang pembayaran pihak ketiga.</li>
                <li>Data automatik seperti alamat IP, jenis pelayar, peranti digunakan, dan halaman yang dilawati.</li>
                <li>Data pilihan yang diberikan secara sukarela seperti testimoni atau maklum balas.</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">2. Bagaimana Kami Menggunakan Maklumat Anda</h2>
        <div class="text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Memproses pesanan, penghantaran, dan akses kepada kandungan digital.</li>
                <li>Menyediakan sokongan pelanggan dan menjawab pertanyaan.</li>
                <li>Membina pengalaman yang diperibadikan dan menambah baik laman.</li>
                <li>Menghantar makluman produk atau promosi apabila anda memberikan kebenaran.</li>
                <li>Mematuhi keperluan undang-undang serta pencegahan penipuan.</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">3. Perkongsian Maklumat</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Kami tidak menjual atau menyewa data pelanggan.</li>
                <li>Data hanya dikongsi dengan penyedia perkhidmatan yang membantu pembayaran, penghantaran, atau sokongan teknologi.</li>
                <li>Kami akan mendedahkan maklumat apabila diwajibkan oleh undang-undang atau untuk melindungi hak kami.</li>
                <li>Perkongsian tambahan hanya berlaku dengan kebenaran anda.</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">4. Keselamatan Data</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Semua transaksi disalurkan melalui sambungan SSL yang disulitkan.</li>
                <li>Akses dalaman dihadkan kepada kakitangan yang memerlukan maklumat tersebut.</li>
                <li>Sistem kami dipantau dan dikemas kini secara berkala untuk mencegah pencerobohan.</li>
                <li>Kami membuat sandaran data dan semakan keselamatan secara berkala.</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">5. Hak Anda</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Meminta salinan maklumat peribadi yang kami simpan.</li>
                <li>Memperbetulkan data yang tidak tepat atau lapuk.</li>
                <li>Memohon pemadaman maklumat tertentu selagi tidak bercanggah dengan undang-undang.</li>
                <li>Menarik diri daripada komunikasi pemasaran pada bila-bila masa.</li>
                <li>Memfailkan aduan dengan pihak berkuasa perlindungan data jika perlu.</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">6. Cookies &amp; Pilihan Anda</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Cookies membantu kami mengingati pilihan anda dan menilai prestasi laman.</li>
                <li>Anda boleh menolak atau memadam cookies melalui tetapan pelayar.</li>
                <li>Menonaktifkan cookies mungkin menghadkan sebahagian fungsi laman.</li>
            </ul>
        </div>
    </div>

    <div class="policy-card space-y-4">
        <h2 class="font-display text-3xl text-white">7. Kemas Kini Dasar &amp; Sokongan</h2>
        <div class="space-y-4 text-base leading-relaxed text-white/80">
            <ul class="list-disc list-inside space-y-2 text-sm text-white/80">
                <li>Kami akan mengemas kini dasar ini apabila diperlukan dan memaklumkannya di laman ini.</li>
                {{-- <li>Untuk pertanyaan privasi, hubungi <a href="mailto:support@kakkay.com" class="text-pink-200 underline">support@kakkay.com</a> atau WhatsApp <a href="https://wa.me/60138846594" class="text-pink-200 underline">+60 13-884 6594</a>.</li>
                <li>Kami berusaha membalas setiap pertanyaan dalam tempoh 24 jam bekerja.</li> --}}
            </ul>
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
