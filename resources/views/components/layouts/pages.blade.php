<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>34 Teknik Bercinta — Long-Form Sales Page</title>
    <meta name="description" content="Hidupkan semula rasa cinta dengan 34 teknik bercinta yang mudah, murah dan ikhlas oleh Kamalia Kamal. Long-form sales page dengan CTA" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Optimized font loading: preload critical fonts, defer non-critical --}}
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet"></noscript>
    {{-- Defer decorative fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    @vite('resources/css/pages.css')
    @filamentStyles
</head>
<body class="font-sans text-slate-800 bg-white selection:bg-blush/50 selection:text-maroon">
    {{ $slot }}
    
    @livewire('notifications')
    @filamentScripts
</body>
</html>
