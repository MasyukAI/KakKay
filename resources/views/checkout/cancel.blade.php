<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Dibatalkan - Kak Kay</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-pink-900">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8 text-center">
            <div class="mb-6">
                <div class="mx-auto w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2" style="font-family: 'Caveat Brush', cursive;">
                    Pembayaran <span class="text-orange-400">Dibatalkan</span>
                </h1>
                <p class="text-gray-300">
                    Anda telah membatalkan pembayaran. Keranjang anda masih disimpan.
                </p>
            </div>

            <div class="space-y-4">
                <div class="bg-white/5 rounded-lg p-4">
                    <p class="text-sm text-gray-400 mb-1">Status</p>
                    <p class="text-white">Pembayaran dibatalkan oleh pengguna</p>
                </div>

                <div class="space-y-3">
                          <a href="{{ route('checkout') }}" wire:navigate
                       class="block w-full bg-pink-500 hover:bg-pink-600 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                        Cuba Lagi
                    </a>
                    
                          <a href="{{ route('cart') }}" wire:navigate
                       class="block w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                        Kembali ke Keranjang
                    </a>
                    
                          <a href="{{ route('home') }}" wire:navigate
                       class="block w-full text-gray-400 hover:text-white py-2 transition-colors">
                        Kembali ke Laman Utama
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
