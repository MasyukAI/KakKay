<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Kak Kay — Counsellor • Penulis • Pencipta KKDI' }}</title>
    <meta name="description" content="Ruang selamat untuk buku, konsultasi, dan panduan refleksi bersama Kak Kay." />

    @vite('resources/css/storefront.css')
    @filamentStyles
</head>
<body class="storefront-body selection:bg-[#f0d3ca] selection:text-[#7b444a]">
    {{ $slot }}

    @livewire('notifications')
    @filamentScripts
</body>
</html>
