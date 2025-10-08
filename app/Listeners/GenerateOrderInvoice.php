<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderPaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class GenerateOrderInvoice implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 60; // 1 minute

    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        Log::info('Generating invoice for order', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
        ]);

        try {
            // TODO: Implement invoice generation logic
            // This could use a package like Laravel Daily/Invoices
            // or generate PDF invoices using libraries like TCPDF, DomPDF, etc.

            // For now, we'll just log that invoice generation would happen
            // and create a placeholder invoice record

            $invoicePath = "invoices/{$event->order->order_number}.pdf";

            // Simulate invoice generation
            // In a real implementation, this would generate an actual PDF
            Storage::disk('public')->put($invoicePath, 'Invoice placeholder content');

            // Update order with invoice path
            $event->order->update([
                'invoice_path' => $invoicePath,
                'invoice_generated_at' => now(),
            ]);

            Log::info('Invoice generated successfully', [
                'order_id' => $event->order->id,
                'order_number' => $event->order->order_number,
                'invoice_path' => $invoicePath,
            ]);
        } catch (Throwable $throwable) {
            Log::error('Failed to generate invoice', [
                'order_id' => $event->order->id,
                'order_number' => $event->order->order_number,
                'error' => $throwable->getMessage(),
            ]);

            throw $throwable; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderPaid $event, Throwable $exception): void
    {
        Log::critical('Invoice generation failed permanently after retries', [
            'order_id' => $event->order->id,
            'order_number' => $event->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
