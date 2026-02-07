<!DOCTYPE html>
    <html lang="en" class="h-full">
    <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <title>Kak Kay - Counsellor • Therapist • KKDI Creator</title>

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
