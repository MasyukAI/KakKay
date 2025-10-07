<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Berjaya - Kak Kay</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen overflow-hidden bg-slate-950 text-white">
    <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_top,rgba(236,72,153,0.35),transparent_55%)]"></div>
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_bottom_left,rgba(96,165,250,0.2),transparent_60%)]"></div>
    <div class="absolute inset-0 -z-30 bg-[url('data:image/svg+xml,%3Csvg width=\'100\' height=\'100\' viewBox=\'0 0 100 100\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23ffffff14\'%3E%3Ccircle cx=\'3\' cy=\'3\' r=\'1\'/%3E%3Ccircle cx=\'53\' cy=\'23\' r=\'1\'/%3E%3Ccircle cx=\'13\' cy=\'63\' r=\'1\'/%3E%3Ccircle cx=\'83\' cy=\'43\' r=\'1\'/%3E%3Ccircle cx=\'73\' cy=\'83\' r=\'1\'/%3E%3Ccircle cx=\'33\' cy=\'93\' r=\'1\'/%3E%3C/g%3E%3C/svg%3E')] bg-repeat opacity-70"></div>

    <div class="relative min-h-screen flex items-center justify-center px-4 py-16">
        <div class="absolute -top-40 right-[-6rem] h-72 w-72 rounded-full bg-pink-500/40 blur-3xl"></div>
        <div class="absolute bottom-[-12rem] left-[-4rem] h-80 w-80 rounded-full bg-purple-500/30 blur-3xl"></div>

        <div class="relative w-full max-w-3xl">
            <div class="absolute inset-0 rounded-[32px] bg-gradient-to-br from-pink-500/40 via-purple-500/35 to-indigo-500/35 blur-2xl opacity-70"></div>
            <div class="relative rounded-[28px] border border-white/15 bg-white/10 backdrop-blur-2xl shadow-2xl">
                <div class="absolute top-6 right-6 hidden sm:flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-medium uppercase tracking-[0.25em] text-white/70">
                    <span class="animate-pulse h-2 w-2 rounded-full bg-emerald-400"></span>
                    Pembayaran Berjaya
                </div>

                <div class="px-6 py-10 sm:px-12 sm:py-14">
                    @php
                        $address = $order?->address;
                        $customerData = $order?->checkout_form_data ?? ($customerSnapshot ?? []);
                        $orderItems = $order?->orderItems ?? collect();
                        $hasOrderItems = $orderItems->count() > 0;
                        $fallbackItems = collect(data_get($cartSnapshot ?? [], 'items', []));
                        $displayItems = $hasOrderItems ? $orderItems : $fallbackItems;
                        $deliveryMethod = $order?->delivery_method ?? data_get($customerData, 'delivery_method');
                    @endphp

                    <div class="flex flex-col items-center text-center">
                        <div class="relative mb-8">
                            <div class="absolute inset-0 animate-ping rounded-full bg-emerald-400/40"></div>
                            <div class="relative mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-500 shadow-lg shadow-emerald-500/30">
                                <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">
                            Pembayaran <span class="bg-gradient-to-r from-pink-300 via-purple-300 to-sky-300 bg-clip-text text-transparent">Berjaya</span>!
                        </h1>
                        <p class="mt-4 max-w-2xl text-base text-white/70 sm:text-lg">
                            Terima kasih kerana memilih Kak Kay. Pesanan anda telah diterima dan sedang disiapkan untuk penghantaran. Anda akan menerima email pengesahan sebentar lagi.
                        </p>
                    </div>

                    <div class="mt-10 grid gap-6 lg:grid-cols-3">
                        @if(isset($order) && $order)
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-inner shadow-white/10 lg:col-span-1">
                                <div class="flex items-center justify-between text-sm text-white/60">
                                    <span>Status Pesanan</span>
                                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-200">
                                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-white/50">Nombor Pesanan</p>
                                        <p class="mt-1 font-mono text-lg text-white">
                                            {{ $order->order_number }}
                                        </p>
                                    </div>
                                    @if(isset($payment) && $payment)
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <p class="text-xs uppercase tracking-wide text-white/50">Jumlah Dibayar</p>
                                                <p class="mt-1 text-xl font-semibold text-white">
                                                    {{ $order->formatted_total }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs uppercase tracking-wide text-white/50">Kaedah Pembayaran</p>
                                                <p class="mt-1 text-sm font-medium capitalize text-white">
                                                    {{ $payment->method }}
                                                </p>
                                            </div>
                                        </div>
                                        @if($deliveryMethod)
                                            <div>
                                                <p class="text-xs uppercase tracking-wide text-white/50">Kaedah Penghantaran</p>
                                                <p class="mt-1 text-sm font-medium capitalize text-white">
                                                    {{ str_replace('-', ' ', $deliveryMethod) }}
                                                </p>
                                            </div>
                                        @endif
                                        <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                                            <p class="text-xs uppercase tracking-wide text-white/50">ID Pembayaran CHIP</p>
                                            <p class="mt-1 font-mono text-sm text-white/80">
                                                {{ $payment->gateway_payment_id }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 lg:col-span-1">
                                <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-white/60">Ringkasan Pesanan</h2>
                                @if($displayItems->count())
                                    <ul class="mt-5 space-y-4 text-left">
                                        @foreach($displayItems as $item)
                                            @php
                                                $isModel = $item instanceof \App\Models\OrderItem;
                                                $name = $isModel ? ($item->product->name ?? 'Item Tanpa Nama') : ($item['name'] ?? 'Item Tanpa Nama');
                                                $quantity = $isModel ? $item->quantity : ($item['quantity'] ?? 1);
                                                $unitPriceCents = $isModel ? $item->unit_price : ($item['price'] ?? 0);
                                                $unitPrice = 'RM '.number_format($unitPriceCents / 100, 2);
                                                $totalPriceCents = $isModel ? $item->total_price : ($item['price'] ?? 0) * $quantity;
                                                $totalPrice = 'RM '.number_format($totalPriceCents / 100, 2);
                                            @endphp
                                            <li class="flex items-start justify-between gap-4 rounded-xl border border-white/5 bg-black/20 p-4">
                                                <div>
                                                    <p class="font-semibold text-white">{{ $name }}</p>
                                                    <p class="text-xs text-white/50">{ Qty: {{ $quantity }} @ {{ $unitPrice }} }</p>
                                                </div>
                                                <p class="font-semibold text-white">{{ $totalPrice }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="mt-4 text-sm text-white/60">Maklumat item tidak tersedia buat masa ini.</p>
                                @endif
                            </div>

                            <div class="relative rounded-2xl border border-white/10 bg-gradient-to-br from-white/10 via-white/5 to-white/10 p-6 lg:col-span-1">
                                <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-white/60">Maklumat Penghantaran</h2>
                                <div class="mt-5 space-y-4 text-left text-sm text-white/70">
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-white/50">Penerima</p>
                                        <p class="mt-1 font-medium text-white">
                                            {{ $address->name ?? $customerData['name'] ?? 'Pelanggan Kak Kay' }}
                                        </p>
                                        <p>{{ $address->phone ?? ($customerData['phone'] ?? '-') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-white/50">Alamat Penghantaran</p>
                                        <p class="mt-1 leading-relaxed">
                                            {{ $address->street1 ?? ($customerData['street1'] ?? '-') }}<br>
                                            @if($address?->street2 ?? ($customerData['street2'] ?? null))
                                                {{ $address->street2 ?? $customerData['street2'] }}<br>
                                            @endif
                                            {{ $address->postcode ?? ($customerData['postcode'] ?? '-') }} {{ $address->city ?? ($customerData['city'] ?? '') }}<br>
                                            {{ $address->state ?? ($customerData['state'] ?? '') }}, {{ $address->country ?? ($customerData['country'] ?? 'Malaysia') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-white/50">Emel Pengesahan</p>
                                        <p class="mt-1">{{ $customerData['email'] ?? $order->user->email ?? 'Tidak tersedia' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="relative rounded-2xl border border-white/10 bg-gradient-to-br from-white/10 via-white/5 to-white/10 p-6 lg:col-span-3">
                                <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-white/60">Langkah Seterusnya</h2>
                                <ul class="mt-6 grid gap-5 text-left md:grid-cols-3">
                                    <li class="flex items-start gap-3">
                                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                                        <div>
                                            <p class="font-semibold text-white">Pembayaran Telah Disahkan</p>
                                            <p class="text-sm text-white/60">Kami telah menerima pembayaran anda dan sistem kami telah mengeluarkan resit digital secara automatik.</p>
                                        </div>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-sky-400"></span>
                                        <div>
                                            <p class="font-semibold text-white">Pesanan Dalam Proses</p>
                                            <p class="text-sm text-white/60">Pasukan gudang kami sedang menyediakan pesanan anda. Kami akan menghantar notifikasi apabila pesanan dihantar.</p>
                                        </div>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-pink-400"></span>
                                        <div>
                                            <p class="font-semibold text-white">Sokongan Pelanggan</p>
                                            <p class="text-sm text-white/60">Perlu bantuan? Hubungi kami di <a href="mailto:support@kakkay.my" class="text-pink-200 underline underline-offset-4">support@kakkay.my</a> dan kami sedia membantu anda.</p>
                                        </div>
                                    </li>
                                </ul>
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

                    <div class="mt-12 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-left text-xs text-white/50">
                            <p>Resit rasmi akan dihantar ke email anda dalam beberapa minit.</p>
                            <p class="mt-1">Terima kasih kerana menyokong perniagaan tempatan.</p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <a href="{{ route('home') }}" class="group inline-flex items-center justify-center rounded-full bg-gradient-to-r from-pink-500 to-purple-500 px-6 py-3 text-sm font-semibold transition hover:shadow-lg hover:shadow-pink-500/40">
                                <span>Kembali ke Laman Utama</span>
                                <svg class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            <a href="mailto:support@kakkay.my" class="inline-flex items-center justify-center rounded-full border border-white/20 px-6 py-3 text-sm font-semibold text-white/80 transition hover:bg-white/10">
                                Hubungi Sokongan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
