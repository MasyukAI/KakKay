<?php

use MasyukAI\Shipping\Models\Shipment;
use function Livewire\Volt\{state, computed, mount};

state(['trackingNumber']);

mount(function (string $trackingNumber) {
    $this->trackingNumber = $trackingNumber;
});

$shipment = computed(function () {
    return Shipment::where('tracking_number', $this->trackingNumber)->first();
});

$trackingEvents = computed(function () {
    return $this->shipment?->trackingEvents ?? collect();
});

?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Track Your Shipment</h1>
                <p class="text-sm text-gray-600 mt-1">Tracking Number: {{ $trackingNumber }}</p>
            </div>

            @if($this->shipment)
                <div class="p-6">
                    <!-- Shipment Status -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Current Status</h2>
                                <p class="text-sm text-gray-600">Last updated: {{ $this->shipment->updated_at->format('M j, Y g:i A') }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($this->shipment->status === 'delivered') bg-green-100 text-green-800
                                    @elseif($this->shipment->status === 'in_transit') bg-blue-100 text-blue-800
                                    @elseif($this->shipment->status === 'dispatched') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $this->shipment->status)) }}
                                </span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                            @php
                                $progress = match($this->shipment->status) {
                                    'created' => 25,
                                    'dispatched' => 50,
                                    'in_transit' => 75,
                                    'delivered' => 100,
                                    default => 10
                                };
                            @endphp
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    <!-- Shipment Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Shipment Information</h3>
                            <dl class="space-y-1">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Provider:</dt>
                                    <dd class="text-sm text-gray-900">{{ ucfirst($this->shipment->provider) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Method:</dt>
                                    <dd class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $this->shipment->method)) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Cost:</dt>
                                    <dd class="text-sm text-gray-900">RM {{ $this->shipment->formatted_cost }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Destination</h3>
                            <div class="text-sm text-gray-600">
                                @if($this->shipment->destination_address)
                                    <p>{{ $this->shipment->destination_address['name'] ?? '' }}</p>
                                    <p>{{ $this->shipment->destination_address['line1'] ?? '' }}</p>
                                    @if(!empty($this->shipment->destination_address['line2']))
                                        <p>{{ $this->shipment->destination_address['line2'] }}</p>
                                    @endif
                                    <p>{{ $this->shipment->destination_address['city'] ?? '' }}, {{ $this->shipment->destination_address['state'] ?? '' }} {{ $this->shipment->destination_address['postal_code'] ?? '' }}</p>
                                    <p>{{ $this->shipment->destination_address['country'] ?? '' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Tracking Events -->
                    @if($this->trackingEvents->count() > 0)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tracking History</h3>
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    @foreach($this->trackingEvents as $index => $event)
                                        <li>
                                            <div class="relative pb-8">
                                                @if(!$loop->last)
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full 
                                                            @if($event->status === 'delivered') bg-green-500
                                                            @elseif($event->status === 'in_transit') bg-blue-500
                                                            @elseif($event->status === 'dispatched') bg-yellow-500
                                                            @else bg-gray-500
                                                            @endif
                                                            flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-900">{{ $event->description }}</p>
                                                            @if($event->location)
                                                                <p class="text-sm text-gray-500">{{ $event->location }}</p>
                                                            @endif
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            <time datetime="{{ $event->event_date->toISOString() }}">
                                                                {{ $event->event_date->format('M j, Y') }}<br>
                                                                {{ $event->event_date->format('g:i A') }}
                                                            </time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="p-6 text-center">
                    <div class="w-12 h-12 mx-auto mb-4 text-gray-400">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.5-.773-6.217-2.083C5.783 12.917 6 12.477 6 12c0-.477-.217-.917-.217-.917C7.5 10.227 9.66 9.5 12 9.5s4.5.727 6.217 1.583c0 0-.217.44-.217.917 0 .477.217.917.217.917A7.962 7.962 0 0112 15z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Shipment Not Found</h3>
                    <p class="text-gray-600 mb-4">We couldn't find any shipment with tracking number: <strong>{{ $trackingNumber }}</strong></p>
                    <p class="text-sm text-gray-500">Please check your tracking number and try again.</p>
                </div>
            @endif
        </div>
    </div>
</div>