<?php

declare(strict_types=1);

namespace MasyukAI\Docs\Enums;

/**
 * Backward compatibility alias for DocumentStatus
 * @deprecated Use DocumentStatus instead
 */
enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case SENT = 'sent';
    case PAID = 'paid';
    case PARTIALLY_PAID = 'partially_paid';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return DocumentStatus::from($this->value)->label();
    }

    public function color(): string
    {
        return DocumentStatus::from($this->value)->color();
    }

    public function isPaid(): bool
    {
        return DocumentStatus::from($this->value)->isPaid();
    }

    public function isPayable(): bool
    {
        return DocumentStatus::from($this->value)->isPayable();
    }
}
