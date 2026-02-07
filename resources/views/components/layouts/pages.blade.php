<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>34 Teknik Bercinta — Long-Form Sales Page</title>
    <meta name="description" content="Hidupkan semula rasa cinta dengan 34 teknik bercinta yang mudah, murah dan ikhlas oleh Kamalia Kamal. Long-form sales page dengan CTA" />
    @vite('resources/css/pages.css')
    @filamentStyles
</head>
<body class="font-sans text-slate-800 bg-white selection:bg-blush/50 selection:text-maroon">
    {{ $slot }}
    
    @livewire('notifications')
    @filamentScripts
</body>
</html>
