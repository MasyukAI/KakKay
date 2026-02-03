<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Berjaya - Kak Kay</title>
    @vite(['resources/css/app.css'])
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(3deg); }
        }
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        @keyframes glow-pulse {
            0%, 100% { opacity: 0.9; box-shadow: 0 0 24px rgba(236, 72, 153, 0.3), 0 0 48px rgba(139, 92, 246, 0.2); }
            50% { opacity: 0.75; box-shadow: 0 0 32px rgba(236, 72, 153, 0.4), 0 0 64px rgba(139, 92, 246, 0.3); }
        }
        @keyframes slide-up {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .animate-float { animation: float 8s ease-in-out infinite; }
        .animate-shimmer { animation: shimmer 6s linear infinite; }
        .animate-glow-pulse { animation: glow-pulse 5s ease-in-out infinite; }
        .animate-slide-up { animation: slide-up 0.5s ease-out forwards; }
        .glass-morphism {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0.05) 100%);
            backdrop-filter: blur(10px) saturate(120%);
            -webkit-backdrop-filter: blur(10px) saturate(120%);
        }
        .text-shimmer {
            background: linear-gradient(90deg, #fbbf24 0%, #f472b6 25%, #a78bfa 50%, #60a5fa 75%, #fbbf24 100%);
            background-size: 200% auto;
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 3s linear infinite;
        }
        .slide-up {
            animation: slide-up 0.5s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        .bg-size-200 { background-size: 200% auto; }
        .bg-pos-0 { background-position: 0% center; }
        .bg-pos-100 { background-position: 100% center; }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden bg-[#0f0218] text-white">
    <!-- Enhanced Background Effects -->
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-48 -left-32 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-pink-500/40 via-purple-500/25 to-rose-500/35 blur-2xl"></div>
        <div class="absolute top-1/4 -right-36 h-[540px] w-[540px] rounded-full bg-gradient-to-br from-fuchsia-500/25 via-rose-500/25 to-orange-400/35 blur-2xl"></div>
        <div class="absolute bottom-0 left-1/2 h-[320px] w-[520px] -translate-x-1/2 rounded-full bg-gradient-to-br from-purple-600/30 via-indigo-500/20 to-pink-400/30 blur-2xl"></div>
    </div>
    <div class="fixed inset-0 -z-40 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zM36 6V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20"></div>
    
    <div class="relative min-h-screen flex items-center justify-center px-4 py-16 overflow-y-auto">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-24 h-72 w-72 rounded-full bg-gradient-to-br from-pink-500/35 via-fuchsia-500/20 to-purple-500/30 blur-2xl"></div>
            <div class="absolute -bottom-48 -left-16 h-80 w-80 rounded-full bg-gradient-to-br from-purple-500/25 via-indigo-500/20 to-pink-500/20 blur-2xl"></div>
        </div>

        <div class="relative w-full max-w-6xl animate-slide-up px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-[36px] border border-white/15 bg-white/10 shadow-[0_18px_48px_rgba(12,5,24,0.45)] backdrop-blur-sm">
                <!-- Success Badge (Top Right) -->
                <div class="absolute top-6 right-6 hidden sm:flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.25em] text-white/70 backdrop-blur-sm">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-pulse absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400 shadow-lg shadow-emerald-400/40"></span>
                    </span>
                    Pembayaran Berjaya
                </div>

                <div class="px-8 py-12 sm:px-14 sm:py-16 lg:px-16">
                    @php
                        $address = $order?->address;
                        $customerData = $order?->checkout_form_data ?? ($customerSnapshot ?? []);
                        $orderItems = $order?->orderItems ?? collect();
                        $hasOrderItems = $orderItems->count() > 0;
                        $fallbackItems = collect(data_get($cartSnapshot ?? [], 'items', []));
                        $displayItems = $hasOrderItems ? $orderItems : $fallbackItems;
                        $deliveryMethod = $order?->delivery_method ?? data_get($customerData, 'delivery_method');
                        $shipments = $order?->shipments ?? collect();
                        $latestShipment = $shipments instanceof \Illuminate\Support\Collection
                            ? $shipments->sortByDesc(fn ($shipment) => $shipment->shipped_at ?? $shipment->created_at)->first()
                            : null;
                        $trackingNumber = $latestShipment?->tracking_number;
                        $trackingCarrier = $latestShipment?->carrier;
                        $trackingService = $latestShipment?->service;
                        $trackingStatusLabel = $latestShipment?->status
                            ? ucwords(str_replace(['_', '-'], ' ', (string) $latestShipment->status))
                            : null;
                    @endphp

                    <div class="flex flex-col items-center text-center">
                        <!-- Enhanced Success Icon -->
                        <div class="relative mb-10">
                            <!-- Outer Ring Pulse -->
                            <div class="absolute inset-0 rounded-full bg-gradient-to-r from-emerald-400/40 to-green-400/40 animate-pulse"></div>
                            <div class="absolute inset-0 rounded-full bg-gradient-to-r from-emerald-400/20 to-green-400/20 animate-none"></div>
                            
                            <!-- Main Icon Container -->
                            <div class="relative mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 via-green-400 to-emerald-500 shadow-2xl shadow-emerald-500/50 ring-4 ring-emerald-400/30">
                                <!-- Inner Glow -->
                                <div class="absolute inset-2 rounded-full bg-gradient-to-br from-white/20 to-transparent"></div>
                                
                                <!-- Check Icon -->
                                <svg class="relative h-12 w-12 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            
                            <!-- Sparkle Effects -->
                            <div class="absolute -top-2 -right-2 h-3 w-3 rounded-full bg-yellow-300 animate-none"></div>
                            <div class="absolute -bottom-1 -left-2 h-2 w-2 rounded-full bg-pink-300 animate-none"></div>
                            <div class="absolute top-0 left-0 h-2 w-2 rounded-full bg-blue-300 animate-none"></div>
                        </div>
                        
                        <!-- Enhanced Title with Shimmer Effect -->
                        <h1 class="text-4xl font-black tracking-tight sm:text-5xl lg:text-6xl mb-2">
                            Pembayaran <span class="text-shimmer">Berjaya</span>!
                        </h1>
                        
                        <!-- Subtitle with Better Typography -->
                        <p class="mt-6 max-w-2xl text-base sm:text-lg leading-relaxed text-white/80 font-light">
                            🎉 Terima kasih kerana memilih <span class="font-semibold text-white">Kak Kay</span>. Pesanan anda telah diterima dan sedang disiapkan dengan penuh kasih sayang untuk penghantaran.
                        </p>
                        <p class="mt-2 text-sm text-white/60">
                            ✨ Anda akan menerima email pengesahan sebentar lagi
                        </p>
                    </div>

                    <div class="mt-12 grid gap-6 items-start lg:items-start lg:[grid-template-columns:minmax(0,1.05fr)_minmax(0,1fr)]">
                        @if(isset($order) && $order)
                            <!-- Order Status Card - Enhanced -->
                            <div class="group flex h-full flex-col rounded-[28px] border border-white/12 bg-white/10 p-6 shadow-[0_16px_40px_rgba(12,5,24,0.45)] backdrop-blur-sm transition-all duration-300 hover:scale-[1.02] hover:shadow-[0_14px_32px_rgba(236,72,153,0.25)]">
                                <div class="mb-6 flex items-center justify-between text-sm text-white/60">
                                    <span class="font-medium">Status Pesanan</span>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-white/70 backdrop-blur">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-pulse absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                                        </span>
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <div class="space-y-5">
                                    <div>
                                        <p class="text-xs uppercase tracking-wider text-white/50 mb-1.5 flex items-center gap-2">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            Nombor Pesanan
                                        </p>
                                        <p class="font-mono text-xl font-bold text-white bg-white/5 rounded-lg px-3 py-2 border border-white/10">
                                            {{ $order->order_number }}
                                        </p>
                                    </div>
                                    @if(isset($payment) && $payment)
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div class="rounded-2xl border border-white/12 bg-white/10 p-4 backdrop-blur">
                                                <p class="text-xs uppercase tracking-wider text-pink-200/80 mb-1.5 flex items-center gap-2">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Jumlah Dibayar
                                                </p>
                                                <p class="text-2xl font-black bg-gradient-to-r from-pink-200 to-purple-200 bg-clip-text text-transparent">
                                                    {{ $order->formatted_total }}
                                                </p>
                                            </div>
                                            <div class="rounded-2xl border border-white/12 bg-white/10 p-4 backdrop-blur">
                                                <p class="text-xs uppercase tracking-wider text-blue-200/80 mb-1.5 flex items-center gap-2">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                    </svg>
                                                    Kaedah Pembayaran
                                                </p>
                                                <p class="text-base font-bold capitalize text-white">
                                                    {{ str($payment->gateway)->headline() }}
                                                </p>
                                            </div>
                                        </div>
                                        @if($deliveryMethod)
                                            <div class="rounded-2xl border border-white/12 bg-white/10 p-4 backdrop-blur">
                                                <p class="text-xs uppercase tracking-wider text-violet-200/80 mb-1.5 flex items-center gap-2">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                                    </svg>
                                                    Kaedah Penghantaran
                                                </p>
                                                <p class="text-base font-bold capitalize text-white">
                                                    {{ str_replace('-', ' ', $deliveryMethod) }}
                                                </p>
                                            </div>
                                        @endif
                                        <div class="rounded-2xl border border-white/12 bg-white/10 p-4 backdrop-blur">
                                            <p class="text-xs uppercase tracking-wider text-emerald-200/80 mb-2 flex items-center gap-2">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                ID Pembayaran CHIP
                                            </p>
                                            <p class="font-mono text-sm text-white/80 break-all rounded-lg border border-white/12 bg-white/5 px-3 py-2">
                                                {{ $payment->transaction_id }}
                                            </p>
                                        </div>
                                        @if($trackingNumber)
                                            <div class="rounded-2xl border border-white/12 bg-white/10 p-4 backdrop-blur">
                                                <p class="text-xs uppercase tracking-wider text-amber-200/80 mb-2 flex items-center gap-2">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                    </svg>
                                                    Nombor Penjejakan
                                                </p>
                                                <div class="space-y-1.5">
                                                    <p class="font-mono text-base font-semibold text-white/90 break-all rounded-lg border border-white/12 bg-white/5 px-3 py-2">
                                                        {{ $trackingNumber }}
                                                    </p>
                                                    <p class="text-xs text-white/60 flex flex-wrap items-center gap-2">
                                                        @if($trackingCarrier)
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2 py-1 font-medium text-white/70">
                                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v14" />
                                                                </svg>
                                                                {{ $trackingCarrier }}
                                                            </span>
                                                        @endif
                                                        @if($trackingService)
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2 py-1 font-medium text-white/70">
                                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                {{ $trackingService }}
                                                            </span>
                                                        @endif
                                                        @if($trackingStatusLabel)
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2 py-1 font-medium capitalize text-white/70">
                                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                                {{ $trackingStatusLabel }}
                                                            </span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Shipping Information Card - Enhanced -->
                            <div class="group relative flex h-full flex-col rounded-[28px] border border-white/12 bg-white/10 p-6 shadow-[0_16px_40px_rgba(12,5,24,0.45)] backdrop-blur-sm transition-all duration-300 hover:scale-[1.02] hover:shadow-[0_14px_32px_rgba(236,72,153,0.25)]">
                                <h2 class="text-sm font-bold uppercase tracking-[0.25em] text-white/70 mb-6 flex items-center gap-2">
                                    <svg class="h-4 w-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Maklumat Penghantaran
                                </h2>
                                <div class="flex-1 space-y-5 text-left">
                                    <div class="rounded-xl bg-gradient-to-br from-white/5 to-transparent border border-white/10 p-4">
                                        <p class="text-xs uppercase tracking-wider text-white/50 mb-2 flex items-center gap-2">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Penerima
                                        </p>
                                        <p class="font-bold text-white text-lg">
                                            {{ $address->name ?? $customerData['name'] ?? 'Pelanggan Kak Kay' }}
                                        </p>
                                        <p class="text-white/70 mt-1 flex items-center gap-2">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            {{ $address->phone ?? ($customerData['phone'] ?? '-') }}
                                        </p>
                                    </div>
                                    <div class="rounded-xl bg-gradient-to-br from-white/5 to-transparent border border-white/10 p-4">
                                        <p class="text-xs uppercase tracking-wider text-white/50 mb-2 flex items-center gap-2">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                            </svg>
                                            Alamat Penghantaran
                                        </p>
                                        <p class="leading-relaxed text-white/80">
                                            {{ $address->street1 ?? ($customerData['street1'] ?? '-') }}<br>
                                            @if($address?->street2 ?? ($customerData['street2'] ?? null))
                                                {{ $address->street2 ?? $customerData['street2'] }}<br>
                                            @endif
                                            {{ $address->postcode ?? ($customerData['postcode'] ?? '-') }} {{ $address->city ?? ($customerData['city'] ?? '') }}<br>
                                            {{ $address->state ?? ($customerData['state'] ?? '') }}, {{ $address->country ?? ($customerData['country'] ?? 'Malaysia') }}
                                        </p>
                                    </div>
                                    <div class="rounded-xl bg-gradient-to-br from-white/5 to-transparent border border-white/10 p-4">
                                        <p class="text-xs uppercase tracking-wider text-white/50 mb-2 flex items-center gap-2">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            Emel Pengesahan
                                        </p>
                                        <p class="text-white/80 break-all">{{ $customerData['email'] ?? $order->user->email ?? 'Tidak tersedia' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Summary Card - Enhanced -->
                            <div class="group flex h-full flex-col rounded-[28px] border border-white/12 bg-white/10 p-6 shadow-[0_16px_40px_rgba(12,5,24,0.45)] backdrop-blur-sm transition-all duration-300 hover:scale-[1.02] hover:shadow-[0_14px_32px_rgba(96,165,250,0.25)] lg:col-span-2">
                                <h2 class="text-sm font-bold uppercase tracking-[0.25em] text-white/70 mb-6 flex items-center gap-2">
                                    <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    Ringkasan Pesanan
                                </h2>
                                <div class="flex-1">
                                    @if($displayItems->count())
                                        <ul class="space-y-3 text-left">
                                            @foreach($displayItems as $item)
                                                @php
                                                    $isModel = $item instanceof \AIArmada\Orders\Models\OrderItem || $item instanceof \App\Models\OrderItem;
                                                    $name = $isModel ? ($item->name ?? 'Item Tanpa Nama') : ($item['name'] ?? 'Item Tanpa Nama');
                                                    $quantity = $isModel ? $item->quantity : ($item['quantity'] ?? 1);
                                                    $unitPriceCents = $isModel ? $item->unit_price : ($item['price'] ?? 0);
                                                    $unitPrice = 'RM '.number_format($unitPriceCents / 100, 2);
                                                    $totalPriceCents = $isModel ? $item->total : (($item['price'] ?? 0) * $quantity);
                                                    $totalPrice = 'RM '.number_format($totalPriceCents / 100, 2);
                                                @endphp
                                                <li class="group/item relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-4 transition-all duration-300 hover:border-white/20 hover:shadow-[0_14px_32px_rgba(236,72,153,0.2)]">
                                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/0 via-pink-500/5 to-purple-500/0 opacity-0 transition-opacity duration-300 group-hover/item:opacity-100"></div>

                                                    <div class="relative flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                        <div class="flex-1">
                                                            <p class="font-bold text-white text-base mb-1">{{ $name }}</p>
                                                            <p class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2 py-1 font-mono text-xs text-white/70">
                                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                                </svg>
                                                                Qty: <span class="font-bold text-white">{{ $quantity }}</span> @ {{ $unitPrice }}
                                                            </p>
                                                        </div>
                                                        <div class="text-left sm:text-right">
                                                            <p class="text-lg font-black bg-gradient-to-r from-pink-200 to-purple-200 bg-clip-text text-transparent">{{ $totalPrice }}</p>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="mt-4 text-sm text-white/60">Maklumat item tidak tersedia buat masa ini.</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Next Steps Card - Enhanced -->
                            <div class="relative rounded-[32px] border border-white/12 bg-white/10 p-8 shadow-[0_16px_40px_rgba(12,5,24,0.45)] backdrop-blur-sm lg:col-span-2">
                                <div class="absolute inset-0 rounded-[32px] bg-gradient-to-br from-violet-500/5 via-transparent to-fuchsia-500/5"></div>
                                <div class="relative">
                                    <h2 class="text-sm font-bold uppercase tracking-[0.25em] text-white/70 mb-8 flex items-center gap-2">
                                        <svg class="h-5 w-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                        Langkah Seterusnya
                                    </h2>
                                    <ul class="grid gap-6 text-left md:grid-cols-3">
                                        <li class="group relative overflow-hidden rounded-xl border border-emerald-500/20 bg-gradient-to-br from-emerald-500/10 to-green-500/5 p-5 transition-all duration-300 hover:scale-[1.05] hover:border-emerald-500/40 hover:shadow-xl hover:shadow-emerald-500/20">
                                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-400/0 to-green-400/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                            <div class="relative flex items-start gap-3">
                                                <div class="flex-shrink-0 rounded-full bg-emerald-400/15 p-2 ring-2 ring-emerald-400/30">
                                                    <svg class="h-5 w-5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-bold text-white mb-2 text-lg">Pembayaran Telah Disahkan</p>
                                                    <p class="text-sm text-white/70 leading-relaxed">Kami telah menerima pembayaran anda dan sistem kami telah mengeluarkan resit digital secara automatik. ✨</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/10 p-5 transition-all duration-300 hover:scale-[1.03] hover:border-white/20 hover:shadow-[0_16px_36px_rgba(96,165,250,0.2)]">
                                            <div class="absolute inset-0 bg-gradient-to-br from-blue-400/0 via-blue-400/5 to-blue-400/0 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                            <div class="relative flex items-start gap-3">
                                                <div class="flex-shrink-0 rounded-full bg-blue-400/15 p-2 ring-2 ring-blue-400/30">
                                                    <svg class="h-5 w-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-bold text-white mb-2 text-lg">Pesanan Dalam Proses</p>
                                                    <p class="text-sm text-white/70 leading-relaxed">Pasukan gudang kami sedang menyediakan pesanan anda. Kami akan menghantar notifikasi apabila pesanan dihantar. 📦</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/10 p-5 transition-all duration-300 hover:scale-[1.03] hover:border-white/20 hover:shadow-[0_16px_36px_rgba(244,114,182,0.2)]">
                                            <div class="absolute inset-0 bg-gradient-to-br from-pink-400/0 via-pink-400/5 to-pink-400/0 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                            <div class="relative flex items-start gap-3">
                                                <div class="flex-shrink-0 rounded-full bg-pink-400/15 p-2 ring-2 ring-pink-400/30">
                                                    <svg class="h-5 w-5 text-pink-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-bold text-white mb-2 text-lg">Sokongan Pelanggan</p>
                                                    <p class="text-sm text-white/70 leading-relaxed">Perlu bantuan? Hubungi kami di <a href="mailto:support@kakkay.my" class="font-semibold text-pink-200 underline decoration-pink-200/40 decoration-2 underline-offset-2 hover:decoration-pink-200 transition-colors">support@kakkay.my</a> 💬</p>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-center sm:col-span-2">
                                <p class="text-sm uppercase tracking-[0.25em] text-white/50">ID Transaksi</p>
                                <p class="mt-3 font-mono text-2xl text-white">
                                    {{ $purchaseId ?? 'N/A' }}
                                </p>
                                <p class="mt-4 text-sm text-white/60">
                                    Kami sedang menjejaki maklumat pesanan anda. Sila semak email anda dalam masa terdekat untuk pengesahan.
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Footer Section - Enhanced -->
                    <div class="mt-16 flex flex-col gap-8 sm:flex-row sm:items-center sm:justify-between">
                        <div class="slide-up flex flex-col gap-3 sm:flex-row sm:items-center" style="animation-delay: 0.1s;">
                            <a href="{{ route('home') }}" wire:navigate class="cart-button-primary flex items-center gap-3 rounded-full px-8 py-3 text-base font-semibold"
                               style="transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease; transform: translateY(0px) scale(1); filter: brightness(1);"
                               onmouseover="this.style.transform='translateY(-3px) scale(1.05)'; this.style.boxShadow='0 15px 35px rgba(255,105,180,0.4), 0 5px 15px rgba(0,0,0,0.3)'; this.style.filter='brightness(1.1)';"
                               onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow=''; this.style.filter='brightness(1)';">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span>Kembali ke Laman Utama</span>
                                <flux:icon.arrow-up-right class="h-4 w-4" />
                            </a>
                            <a href="mailto:support@kakkay.my" class="btn ghost inline-flex items-center justify-center gap-2 rounded-full border border-white/30 px-8 py-3 text-base font-semibold text-white/80 backdrop-blur-sm transition"
                               style="transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease; transform: translateY(0px);"
                               onmouseover="this.style.transform='translateY(-2px)'; this.style.backgroundColor='rgba(255,255,255,0.08)'; this.style.borderColor='rgba(255,255,255,0.5)'; this.style.color='#ffffff';"
                               onmouseout="this.style.transform='translateY(0)'; this.style.backgroundColor=''; this.style.borderColor=''; this.style.color='';">
                                <svg class="h-5 w-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>Hubungi Sokongan</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Site Tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-XXXXXXXXXX');
    </script>
</body>
</html>
