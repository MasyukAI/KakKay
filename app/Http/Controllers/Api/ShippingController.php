<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MasyukAI\Shipping\Facades\Shipping;
use MasyukAI\Shipping\Models\Shipment;

class ShippingController extends Controller
{
    /**
     * Get shipping quotes for given items and destination.
     */
    public function getQuotes(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.weight' => 'integer|min:0',
            'destination' => 'array',
            'destination.city' => 'string',
            'destination.state' => 'string',
            'destination.country' => 'string',
        ]);

        $quotes = Shipping::getQuotes(
            $request->input('items'),
            $request->input('destination', [])
        );

        return response()->json([
            'success' => true,
            'quotes' => $quotes,
        ]);
    }

    /**
     * Get available shipping methods.
     */
    public function getMethods(): JsonResponse
    {
        $methods = Shipping::getShippingMethods();

        return response()->json([
            'success' => true,
            'methods' => $methods,
        ]);
    }

    /**
     * Calculate shipping cost for specific method.
     */
    public function calculateCost(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'method' => 'required|string',
            'destination' => 'array',
        ]);

        $cost = Shipping::calculateCost(
            $request->input('items'),
            $request->input('method'),
            $request->input('destination', [])
        );

        return response()->json([
            'success' => true,
            'cost' => $cost,
            'formatted_cost' => 'RM ' . number_format($cost / 100, 2),
        ]);
    }

    /**
     * Track a shipment by tracking number.
     */
    public function track(string $trackingNumber): JsonResponse
    {
        $shipment = Shipment::where('tracking_number', $trackingNumber)->first();

        if (! $shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found',
            ], 404);
        }

        $trackingInfo = Shipping::getTrackingInfo($trackingNumber, $shipment->provider);

        return response()->json([
            'success' => true,
            'shipment' => [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'status' => $shipment->status,
                'provider' => $shipment->provider,
                'method' => $shipment->method,
                'cost' => $shipment->cost,
                'formatted_cost' => $shipment->formatted_cost,
                'created_at' => $shipment->created_at,
                'delivered_at' => $shipment->delivered_at,
            ],
            'tracking' => $trackingInfo,
            'events' => $shipment->trackingEvents->map(function ($event) {
                return [
                    'status' => $event->status,
                    'description' => $event->description,
                    'location' => $event->location,
                    'date' => $event->event_date,
                ];
            }),
        ]);
    }

    /**
     * Create a shipment for an order.
     */
    public function createShipment(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'method' => 'required|string',
            'provider' => 'string',
        ]);

        if (! $order->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Order must be paid before creating shipment',
            ], 400);
        }

        if (! $order->requiresShipping()) {
            return response()->json([
                'success' => false,
                'message' => 'Order does not require shipping',
            ], 400);
        }

        if ($order->activeShipment()) {
            return response()->json([
                'success' => false,
                'message' => 'Order already has an active shipment',
            ], 400);
        }

        try {
            $shipment = $order->ship(
                $request->input('method'),
                $request->input('provider', 'local')
            );

            return response()->json([
                'success' => true,
                'shipment' => [
                    'id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'status' => $shipment->status,
                    'provider' => $shipment->provider,
                    'method' => $shipment->method,
                    'cost' => $shipment->cost,
                    'formatted_cost' => $shipment->formatted_cost,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create shipment: ' . $e->getMessage(),
            ], 500);
        }
    }
}