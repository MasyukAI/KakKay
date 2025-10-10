<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MasyukAI\Jnt\Data\TrackingData;

class TrackingUpdatedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public readonly TrackingData $tracking) {}

    public function getOrderId(): ?string
    {
        return $this->tracking->orderId;
    }

    public function getTrackingNumber(): string
    {
        return $this->tracking->trackingNumber;
    }

    public function getLatestStatus(): ?string
    {
        $details = $this->tracking->details;
        if ($details === []) {
            return null;
        }

        $latest = end($details);

        return $latest !== false && $latest instanceof \MasyukAI\Jnt\Data\TrackingDetailData
            ? $latest->scanType
            : null;
    }

    public function getLatestDescription(): ?string
    {
        $details = $this->tracking->details;
        if ($details === []) {
            return null;
        }

        $latest = end($details);

        return $latest !== false && $latest instanceof \MasyukAI\Jnt\Data\TrackingDetailData
            ? $latest->description
            : null;
    }

    public function getLatestLocation(): ?string
    {
        $details = $this->tracking->details;
        if ($details === []) {
            return null;
        }

        $latest = end($details);

        if ($latest === false || ! ($latest instanceof \MasyukAI\Jnt\Data\TrackingDetailData)) {
            return null;
        }

        $parts = array_filter([
            $latest->scanNetworkCity ?? null,
            $latest->scanNetworkProvince ?? null,
        ]);

        return $parts === [] ? null : implode(', ', $parts);
    }

    public function isDelivered(): bool
    {
        return array_any($this->tracking->details, fn ($detail): bool => $detail instanceof \MasyukAI\Jnt\Data\TrackingDetailData && in_array($detail->scanType, ['DELIVER', 'SIGNED'], true));
    }

    public function isInTransit(): bool
    {
        return array_any($this->tracking->details, fn ($detail): bool => $detail instanceof \MasyukAI\Jnt\Data\TrackingDetailData && in_array($detail->scanType, ['TRANSFER', 'ARRIVAL'], true));
    }

    public function hasProblems(): bool
    {
        return array_any($this->tracking->details, fn ($detail): bool => $detail instanceof \MasyukAI\Jnt\Data\TrackingDetailData && in_array($detail->scanType, ['RETURN', 'REJECT', 'PROBLEM'], true));
    }

    public function isCollected(): bool
    {
        return array_any($this->tracking->details, fn ($detail): bool => $detail instanceof \MasyukAI\Jnt\Data\TrackingDetailData && $detail->scanType === 'COLLECT');
    }

    /**
     * @return array<int, \MasyukAI\Jnt\Data\TrackingDetailData>
     */
    public function getDetails(): array
    {
        return $this->tracking->details;
    }

    public function getDetailCount(): int
    {
        return count($this->tracking->details);
    }
}
