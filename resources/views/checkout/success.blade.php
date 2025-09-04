<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Berjaya - Kak Kay</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-purple-900 to-pink-900">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8 text-center">
            <div class="mb-6">
                <div class="mx-auto w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2" style="font-family: 'Caveat Brush', cursive;">
                    Pembayaran <span class="text-pink-400">Berjaya!</span>
                </h1>
                <p class="text-gray-300">
                    Terima kasih! Pesanan anda telah diterima dan pembayaran berjaya diproses.
                </p>
            </div>

            <div class="space-y-4">
                @if(isset($order) && $order)
                <div class="bg-white/5 rounded-lg p-4 space-y-2">
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Nombor Pesanan</p>
                        <p class="text-white font-mono">{{ $order->order_number }}</p>
                    </div>
                    
                    @if(isset($payment) && $payment)
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Jumlah Dibayar</p>
                        <p class="text-white font-semibold">{{ $order->formatted_total }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Kaedah Pembayaran</p>
                        <p class="text-white capitalize">{{ $payment->method }}</p>
                    </div>
                    @endif
                </div>
                @else
                <div class="bg-white/5 rounded-lg p-4">
                    <p class="text-sm text-gray-400 mb-1">ID Transaksi</p>
                    <p class="text-white font-mono">{{ $purchaseId ?? 'N/A' }}</p>
                </div>
                @endif

                <div class="space-y-3">
                    <a href="{{ route('home') }}" 
                       class="block w-full bg-pink-500 hover:bg-pink-600 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                        Kembali ke Laman Utama
                    </a>
                    
                    <p class="text-xs text-gray-400">
                        Resit pembayaran akan dihantar ke email anda tidak lama lagi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
