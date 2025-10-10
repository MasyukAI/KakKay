<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document {{ $document->document_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white">
    <div class="mx-auto max-w-4xl p-8">
        <!-- Header -->
        <div class="mb-8 flex items-start justify-between">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">DOCUMENT</h1>
                <p class="mt-2 text-sm text-gray-600">{{ $document->document_number }}</p>
            </div>
            @if($document->company_data)
            <div class="text-right">
                <p class="font-semibold text-gray-900">{{ $document->company_data['name'] ?? '' }}</p>
                @if(!empty($document->company_data['address']))
                <p class="text-sm text-gray-600">{{ $document->company_data['address'] ?? '' }}</p>
                @endif
                @if(!empty($document->company_data['city']))
                <p class="text-sm text-gray-600">
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
                <p class="text-sm text-gray-600">{{ $document->company_data['email'] }}</p>
                @endif
                @if(!empty($document->company_data['phone']))
                <p class="text-sm text-gray-600">{{ $document->company_data['phone'] }}</p>
                @endif
            </div>
            @endif
        </div>

        <!-- Invoice Details -->
        <div class="mb-8 grid grid-cols-2 gap-8">
            <!-- Bill To -->
            @if($document->customer_data)
            <div>
                <h2 class="mb-2 text-sm font-semibold uppercase text-gray-600">Bill To</h2>
                <div class="text-sm">
                    <p class="font-semibold text-gray-900">{{ $document->customer_data['name'] ?? '' }}</p>
                    @if(!empty($document->customer_data['email']))
                    <p class="text-gray-600">{{ $document->customer_data['email'] }}</p>
                    @endif
                    @if(!empty($document->customer_data['address']))
                    <p class="text-gray-600">{{ $document->customer_data['address'] }}</p>
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
                    <p class="text-gray-600">{{ $document->customer_data['phone'] }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Invoice Info -->
            <div>
                <h2 class="mb-2 text-sm font-semibold uppercase text-gray-600">Invoice Details</h2>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Issue Date:</span>
                        <span class="font-medium text-gray-900">{{ $document->issue_date->format('M d, Y') }}</span>
                    </div>
                    @if($document->due_date)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Due Date:</span>
                        <span class="font-medium text-gray-900">{{ $document->due_date->format('M d, Y') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="rounded-full px-2 py-1 text-xs font-semibold
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
        <div class="mb-8">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-gray-900">
                        <th class="pb-3 text-left text-sm font-semibold uppercase text-gray-900">Description</th>
                        <th class="pb-3 text-right text-sm font-semibold uppercase text-gray-900">Qty</th>
                        <th class="pb-3 text-right text-sm font-semibold uppercase text-gray-900">Unit Price</th>
                        <th class="pb-3 text-right text-sm font-semibold uppercase text-gray-900">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($document->items as $item)
                    <tr class="border-b border-gray-200">
                        <td class="py-3 text-sm text-gray-900">
                            <div class="font-medium">{{ $item['name'] ?? $item['description'] ?? '' }}</div>
                            @if(!empty($item['description']) && isset($item['name']))
                            <div class="text-gray-600">{{ $item['description'] }}</div>
                            @endif
                        </td>
                        <td class="py-3 text-right text-sm text-gray-900">{{ $item['quantity'] ?? 1 }}</td>
                        <td class="py-3 text-right text-sm text-gray-900">{{ $document->currency }} {{ number_format($item['price'] ?? 0, 2) }}</td>
                        <td class="py-3 text-right text-sm font-medium text-gray-900">{{ $document->currency }} {{ number_format(($item['quantity'] ?? 1) * ($item['price'] ?? 0), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="mb-8 flex justify-end">
            <div class="w-80">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="text-gray-900">{{ $document->currency }} {{ number_format($document->subtotal, 2) }}</span>
                    </div>
                    @if($document->tax_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax:</span>
                        <span class="text-gray-900">{{ $document->currency }} {{ number_format($document->tax_amount, 2) }}</span>
                    </div>
                    @endif
                    @if($document->discount_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Discount:</span>
                        <span class="text-gray-900">-{{ $document->currency }} {{ number_format($document->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between border-t-2 border-gray-900 pt-2 text-lg font-bold">
                        <span class="text-gray-900">Total:</span>
                        <span class="text-gray-900">{{ $document->currency }} {{ number_format($document->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($document->notes)
        <div class="mb-8">
            <h3 class="mb-2 text-sm font-semibold uppercase text-gray-600">Notes</h3>
            <p class="text-sm text-gray-600">{{ $document->notes }}</p>
        </div>
        @endif

        <!-- Terms -->
        @if($document->terms)
        <div class="mb-8">
            <h3 class="mb-2 text-sm font-semibold uppercase text-gray-600">Terms & Conditions</h3>
            <p class="text-sm text-gray-600">{{ $document->terms }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="mt-12 border-t border-gray-200 pt-4 text-center text-sm text-gray-500">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>
