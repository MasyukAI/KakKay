<?php

declare(strict_types=1);

namespace MasyukAI\Shipping\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Shipping\Contracts\TrackingServiceInterface;
use MasyukAI\Shipping\Models\Shipment;

class UpdateShipmentTracking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $trackingNumber
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TrackingServiceInterface $trackingService): void
    {
        try {
            $trackingService->updateTracking($this->trackingNumber);
        } catch (\Exception $e) {
            logger()->error("Failed to update tracking for {$this->trackingNumber}: " . $e->getMessage());
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        logger()->error("Permanently failed to update tracking for {$this->trackingNumber}: " . $exception->getMessage());
        
        // Optionally mark shipment as having tracking issues
        $shipment = Shipment::where('tracking_number', $this->trackingNumber)->first();
        if ($shipment) {
            $shipment->update([
                'metadata' => array_merge($shipment->metadata ?? [], [
                    'tracking_error' => $exception->getMessage(),
                    'tracking_error_at' => now()->toISOString(),
                ])
            ]);
        }
    }
}