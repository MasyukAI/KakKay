<!DOCTYPE html>
    <html lang="en" class="h-full">
    <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <title>Kak Kay - Counsellor • Therapist • KKDI Creator</title>

      @vite('resources/css/policy.css')
    </head>
    <body class="h-full bg-[#1a0d1a] bg-[image:var(--grad-hero)] text-white font-serif leading-[1.5] antialiased">
        {{ $slot }}
    </body>
</html>
