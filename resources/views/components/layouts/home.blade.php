<!DOCTYPE html>
<html lang="ms" class="h-full">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Kak Kay — Counsellor • Penulis • Pencipta KKDI' }}</title>

  @vite(['resources/css/storefront.css', 'resources/js/app.js'])
  @livewireStyles
</head>
<body class="storefront-body h-full">
  {{ $slot }}

  @livewireScripts
</body>
</html>
