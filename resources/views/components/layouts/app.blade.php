<!DOCTYPE html>
<html lang="ms" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Kak Kay — Counsellor • Penulis • Pencipta KKDI' }}</title>

    @vite('resources/css/storefront.css')
    <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ysfkaya/filament-phone-input/filament-phone-input.css') }}">
    @filamentStyles
</head>
<body class="storefront-body h-full">
    <div id="app-shell" class="min-h-full">
        {{ $slot }}
        @livewire('notifications')
    </div>

    @filamentScripts
</body>
</html>
