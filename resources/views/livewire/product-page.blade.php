<div class="relative isolate overflow-hidden bg-[#0f0218] text-white min-h-screen">
  <div class="pointer-events-none absolute -top-40 -left-32 h-[480px] w-[480px] rounded-full bg-gradient-to-br from-pink-500/40 via-purple-500/30 to-rose-500/40 blur-2xl"></div>
  <div class="pointer-events-none absolute top-1/3 -right-24 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-fuchsia-500/30 via-rose-500/20 to-orange-400/30 blur-2xl"></div>
  <div class="pointer-events-none absolute bottom-[-240px] left-1/2 h-[460px] w-[460px] -translate-x-1/2 rounded-full bg-gradient-to-br from-purple-600/30 via-indigo-500/20 to-pink-400/40 blur-2xl"></div>

  @php
      $cartQuantity = \AIArmada\Cart\Facades\Cart::getTotalQuantity();
  @endphp

  <div class="relative z-10">
    <x-brand-header :cart-quantity="$cartQuantity" />

    <main class="relative">
      <!-- HERO -->
      <section class="pt-20 pb-24 lg:pb-32">
        <div class="max-w-7xl mx-auto px-4">
          <div class="grid items-center gap-14 lg:grid-cols-[minmax(0,1fr)_420px]">
            <div class="space-y-6">
              <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-5 py-2 text-sm font-semibold text-white/90 shadow-[0_0_40px_rgba(244,114,182,0.35)]">
                <span class="h-2 w-2 rounded-full bg-gradient-to-r from-rose-400 to-fuchsia-400 shadow-[0_0_16px_rgba(244,114,182,0.7)]"></span>
                {{ $product->categories->first()?->name ?? 'Buku Kak Kay' }}
              </div>
              <h1 class="font-display text-4xl leading-[0.95] tracking-tight text-white sm:text-5xl lg:text-6xl">
                {{ $product->name }}
              </h1>
              <p class="max-w-2xl text-lg leading-relaxed text-white/90 sm:text-xl">
                {{ $product->description ?? 'Panduan praktikal dan penuh hikmah dari Kak Kay.' }}
              </p>
              <div class="flex flex-wrap items-center gap-4">
                <button wire:click="addToCart" class="rounded-full bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 px-8 py-4 text-base font-semibold shadow-[0_8px_20px_rgba(236,72,153,0.35)] transition-all duration-300 hover:scale-[1.02] hover:shadow-[0_16px_36px_rgba(236,72,153,0.45)]">
                  <span wire:loading.remove wire:target="addToCart">Beli Hari Ini — RM{{ number_format($product->price / 100, 2) }}</span>
                  <span wire:loading wire:target="addToCart">Menambah...</span>
                </button>
                <a href="{{ route('home') }}" wire:navigate.hover class="rounded-full border border-white/30 px-8 py-4 text-base font-semibold backdrop-blur-sm transition-all duration-300 hover:border-white/60 hover:bg-white/10">
                  Lihat Semua Buku
                </a>
              </div>
              <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ([
                  ['text' => 'Mudah & Praktikal', 'icon' => '🌸'],
                  ['text' => 'Mesra Syariah', 'icon' => '🕌'],
                  ['text' => 'Oleh Kak Kay', 'icon' => '💝'],
                  ['text' => 'Ready Stock', 'icon' => '📖'],
                ] as $feature)
                  <div class="relative overflow-hidden rounded-2xl border border-white/15 bg-white/5 px-4 py-5 shadow-[0_8px_20px_rgba(7,5,15,0.25)] transition-all duration-300 hover:-translate-y-1 hover:bg-white/10 hover:shadow-[0_14px_28px_rgba(236,72,153,0.28)]">
                    <div class="absolute -top-10 right-2 h-20 w-20 rounded-full bg-gradient-to-br from-pink-400/20 to-purple-400/20 blur-lg"></div>
                    <div class="relative flex flex-col gap-2">
                      <span class="text-2xl">{{ $feature['icon'] }}</span>
                      <span class="text-sm font-medium text-white/85">{{ $feature['text'] }}</span>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
            <div class="relative flex justify-center lg:justify-end">
              <div class="group relative">
                <div class="absolute -inset-6 rounded-[36px] bg-gradient-to-br from-pink-400/40 via-fuchsia-500/30 to-purple-500/40 opacity-75 blur-2xl transition-all duration-300 group-hover:opacity-100"></div>
                <div class="relative overflow-hidden rounded-[32px] border border-white/15 bg-white/10 p-6 backdrop-blur-sm shadow-[0_18px_48px_rgba(15,3,37,0.45)] transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-[0_22px_64px_rgba(236,72,153,0.35)]">
                  <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white/80">
                    <span class="h-1.5 w-1.5 rounded-full bg-pink-300"></span>
                    Buku Fizikal
                  </div>
                  <div class="relative mx-auto flex justify-center">
                    <div class="absolute inset-0 rounded-[28px] border border-white/20"></div>
                    @if($product->getFirstMediaUrl('cover'))
                      <img src="{{ $product->getFirstMediaUrl('cover') }}" alt="{{ $product->name }}" class="relative w-full max-w-xs rounded-[26px] object-cover shadow-[0_16px_36px_rgba(17,0,34,0.55)]">
                    @else
                      <img src="{{ asset('storage/images/cover/' . $product->slug . '.webp') }}" alt="{{ $product->name }}" class="relative w-full max-w-xs rounded-[26px] object-cover shadow-[0_16px_36px_rgba(17,0,34,0.55)]">
                    @endif
                  </div>
                  <div class="mt-6 flex flex-col gap-3 rounded-2xl bg-white/10 p-4 text-sm text-white/80">
                    <div class="flex items-center gap-3">
                      <span class="rounded-full bg-pink-400/20 px-3 py-1 text-xs font-semibold text-pink-100">Harga</span>
                      RM{{ number_format($product->price / 100, 2) }}
                    </div>
                    <div class="flex items-center gap-3">
                      <span class="rounded-full bg-purple-400/20 px-3 py-1 text-xs font-semibold text-purple-100">Format</span>
                      Fizikal (Cetak premium)
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ABOUT THE BOOK -->
      <section class="relative pb-20">
        <div class="max-w-6xl mx-auto px-4">
          <div class="overflow-hidden rounded-[36px] border border-white/10 bg-white/5 p-10 backdrop-blur-sm shadow-[0_18px_48px_rgba(12,5,24,0.4)]">
            <div class="space-y-6">
              <h2 class="font-display text-3xl sm:text-4xl text-white">Tentang Buku Ini</h2>
              <p class="text-base leading-relaxed text-white/85 sm:text-lg">
                {{ $product->description ?? 'Buku ini ditulis oleh Kak Kay untuk membantu anda dalam perjalanan hidup yang lebih bermakna.' }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section class="relative pb-24">
        <div class="max-w-4xl mx-auto px-4">
          <div class="flex flex-col items-center gap-3">
            <button wire:click="addToCart" class="rounded-full bg-gradient-to-r from-pink-500 via-rose-500 to-purple-500 px-10 py-4 text-base font-semibold shadow-[0_16px_36px_rgba(236,72,153,0.35)] transition-transform duration-300 hover:scale-[1.02]">
              <span wire:loading.remove wire:target="addToCart">Tambah Ke Troli — RM{{ number_format($product->price / 100, 2) }}</span>
              <span wire:loading wire:target="addToCart">Menambah...</span>
            </button>
            <p class="text-xs uppercase tracking-[0.3em] text-white/50">Penghantaran seluruh Malaysia</p>
          </div>
        </div>
      </section>
    </main>
  </div>
</div>
