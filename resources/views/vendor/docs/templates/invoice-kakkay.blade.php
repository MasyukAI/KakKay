<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invois {{ $document->document_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .gradient-header {
            background: linear-gradient(135deg, #ec4899 0%, #f43f5e 50%, #a855f7 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="mx-auto max-w-4xl bg-white p-8 shadow-lg">
        <!-- Header with Gradient -->
        <div class="gradient-header mb-8 rounded-xl p-6 text-white">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-4xl font-bold">INVOIS</h1>
                    <p class="mt-2 text-sm opacity-90">{{ $document->document_number }}</p>
                </div>
                @if($document->company_data)
                <div class="text-right text-sm">
                    <p class="text-lg font-semibold">{{ $document->company_data['name'] ?? '' }}</p>
                    @if(!empty($document->company_data['address']))
                    <p class="mt-1 opacity-90">{{ $document->company_data['address'] ?? '' }}</p>
                    @endif
                    @if(!empty($document->company_data['city']))
                    <p class="opacity-90">
                        {{ $document->company_data['city'] ?? '' }}
                        @if(!empty($document->company_data['state']))
                        , {{ $document->company_data['state'] }}
                        @endif
                        @if(!empty($document->company_data['postal_code']))
                        {{ $document->company_data['postal_code'] }}
                        @endif
                    </p>
                    @endif
                    @if(!empty($document->company_data['email']))
                    <p class="opacity-90">{{ $document->company_data['email'] }}</p>
                    @endif
                    @if(!empty($document->company_data['phone']))
                    <p class="opacity-90">{{ $document->company_data['phone'] }}</p>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- Invoice Details Section -->
        <div class="mb-8 grid grid-cols-2 gap-8">
            <!-- Bill To -->
            @if($document->customer_data)
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-600">Ditagih Kepada</h2>
                <div class="text-sm">
                    <p class="font-semibold text-gray-900">{{ $document->customer_data['name'] ?? '' }}</p>
                    @if(!empty($document->customer_data['email']))
                    <p class="mt-1 text-gray-600">{{ $document->customer_data['email'] }}</p>
                    @endif
                    @if(!empty($document->customer_data['address']))
                    <p class="mt-2 text-gray-600">{{ $document->customer_data['address'] }}</p>
                    @endif
                    @if(!empty($document->customer_data['city']))
                    <p class="text-gray-600">
                        {{ $document->customer_data['city'] }}
                        @if(!empty($document->customer_data['state']))
                        , {{ $document->customer_data['state'] }}
                        @endif
                        @if(!empty($document->customer_data['postal_code']))
                        {{ $document->customer_data['postal_code'] }}
                        @endif
                    </p>
                    @endif
                    @if(!empty($document->customer_data['phone']))
                    <p class="mt-1 text-gray-600">{{ $document->customer_data['phone'] }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Invoice Info -->
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-600">Maklumat Invois</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tarikh Dikeluarkan:</span>
                        <span class="font-medium text-gray-900">{{ $document->issue_date->format('d M Y') }}</span>
                    </div>
                    @if($document->due_date)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tarikh Matang:</span>
                        <span class="font-medium text-gray-900">{{ $document->due_date->format('d M Y') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold
                            @if($document->status->value === 'paid') bg-green-100 text-green-800
                            @elseif($document->status->value === 'pending' || $document->status->value === 'sent') bg-blue-100 text-blue-800
                            @elseif($document->status->value === 'overdue') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $document->status->label() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        @if($document->items && count($document->items) > 0)
        <div class="mb-8">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-300">
                        <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Perkara</th>
                        <th class="pb-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">Kuantiti</th>
                        <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Harga</th>
                        <th class="pb-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($document->items as $item)
                    <tr>
                        <td class="py-4 text-sm text-gray-900">
                            <div class="font-medium">{{ $item['description'] ?? '' }}</div>
                            @if(!empty($item['notes']))
                            <div class="mt-1 text-xs text-gray-500">{{ $item['notes'] }}</div>
                            @endif
                        </td>
                        <td class="py-4 text-center text-sm text-gray-600">{{ $item['quantity'] ?? 1 }}</td>
                        <td class="py-4 text-right text-sm text-gray-600">RM {{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                        <td class="py-4 text-right text-sm font-medium text-gray-900">RM {{ number_format($item['amount'] ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Totals Section -->
        <div class="mb-8 flex justify-end">
            <div class="w-80 space-y-3">
                <div class="flex justify-between border-b border-gray-200 pb-2 text-sm">
                    <span class="text-gray-600">Subjumlah:</span>
                    <span class="font-medium text-gray-900">RM {{ number_format($document->subtotal, 2) }}</span>
                </div>
                
                @if($document->discount_amount > 0)
                <div class="flex justify-between border-b border-gray-200 pb-2 text-sm">
                    <span class="text-gray-600">Diskaun:</span>
                    <span class="font-medium text-green-600">-RM {{ number_format($document->discount_amount, 2) }}</span>
                </div>
                @endif
                
                @if($document->tax_amount > 0)
                <div class="flex justify-between border-b border-gray-200 pb-2 text-sm">
                    <span class="text-gray-600">Cukai:</span>
                    <span class="font-medium text-gray-900">RM {{ number_format($document->tax_amount, 2) }}</span>
                </div>
                @endif
                
                <div class="flex justify-between rounded-lg bg-gradient-to-r from-pink-50 to-purple-50 p-3">
                    <span class="text-base font-semibold text-gray-900">JUMLAH:</span>
                    <span class="text-xl font-bold text-pink-600">RM {{ number_format($document->total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Notes & Terms -->
        @if($document->notes || $document->terms)
        <div class="space-y-6 border-t border-gray-200 pt-8">
            @if($document->notes)
            <div>
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-600">Nota</h3>
                <p class="text-sm leading-relaxed text-gray-700">{{ $document->notes }}</p>
            </div>
            @endif
            
            @if($document->terms)
            <div>
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-600">Terma & Syarat</h3>
                <p class="text-sm leading-relaxed text-gray-700">{{ $document->terms }}</p>
            </div>
            @endif
        </div>
        @endif

        <!-- Footer -->
        <div class="mt-12 border-t border-gray-200 pt-6 text-center">
            <p class="text-xs text-gray-500">
                Terima kasih atas pembelian anda! 💕
            </p>
            @if($document->company_data && !empty($document->company_data['website']))
            <p class="mt-1 text-xs text-gray-500">
                {{ $document->company_data['website'] }}
            </p>
            @endif
        </div>
    </div>
</body>
</html>
