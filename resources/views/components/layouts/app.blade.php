<!DOCTYPE html>
    <html lang="en" class="h-full">
    <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <title>Kak Kay - Counsellor • Therapist • KKDI Creator</title>

      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      {{-- Optimized font loading: preload critical fonts, defer non-critical --}}
      <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Montserrat:wght@700;800;900&display=swap">
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
      <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet"></noscript>
      {{-- Defer decorative fonts --}}
      <link href="https://fonts.googleapis.com/css2?family=Caveat+Brush&family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Shadows+Into+Light&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
      @vite('resources/css/cart.css')
      {{-- Filament core CSS for standalone schema/form usage --}}
      <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
      <link rel="stylesheet" href="{{ asset('css/ysfkaya/filament-phone-input/filament-phone-input.css') }}">
      @filamentStyles
    </head>
    <body class="h-full bg-[#1a0d1a] bg-[image:var(--grad-hero)] text-white font-serif leading-[1.5] antialiased">
        <div id="app-shell" class="min-h-full">
            {{ $slot }}
            @livewire('notifications')
        </div>
        {{-- Filament scripts (includes all required schema/form assets) --}}
        @filamentScripts
    </body>
</html>
