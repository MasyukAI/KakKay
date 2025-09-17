@props([
    'eyebrow' => null,
    'chip' => null,
    'title',
    'subtitle' => null,
])

<section {{ $attributes->merge(['class' => 'relative pt-20']) }}>
    <div class="pointer-events-none absolute -top-10 left-1/4 h-48 w-48 rounded-full bg-gradient-to-br from-pink-400/25 via-rose-500/20 to-purple-500/20 blur-3xl"></div>
    <div class="pointer-events-none absolute top-12 right-1/4 h-60 w-60 rounded-full bg-gradient-to-br from-fuchsia-400/25 via-purple-500/20 to-indigo-400/20 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-6 sm:px-8">
        <div class="mx-auto max-w-4xl text-center space-y-6">
            @if($eyebrow)
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-5 py-2 text-xs font-semibold uppercase tracking-[0.32em] text-white/70">
                    {{ $eyebrow }}
                </span>
            @endif

            <h1 class="font-display text-4xl leading-[0.95] tracking-tight sm:text-5xl lg:text-6xl">
                {{ $title }}
            </h1>

            @if($subtitle)
                <p class="mx-auto max-w-3xl text-base leading-relaxed text-white/80 sm:text-lg">
                    {{ $subtitle }}
                </p>
            @endif

            @if($chip)
                <div class="flex justify-center">
                    <span class="policy-chip">{{ $chip }}</span>
                </div>
            @endif
        </div>

        @if(isset($slot) && method_exists($slot, 'isEmpty') && ! $slot->isEmpty())
            <div class="mt-6 flex flex-wrap justify-center gap-4">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
