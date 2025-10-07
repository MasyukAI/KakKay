<?php

declare(strict_types=1);

namespace MasyukAI\FilamentChip\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

final class ChipSendInstruction extends ChipModel
{
    public $timestamps = false;

    public function amountNumeric(): Attribute
    {
        return Attribute::get(fn () => (float) $this->amount);
    }

    public function stateLabel(): Attribute
    {
        return Attribute::get(fn () => (string) str($this->state)->headline());
    }

    public function stateColor(): string
    {
        return match ($this->state) {
            'completed', 'processed' => 'success',
            'received', 'queued', 'verifying' => 'warning',
            'failed', 'cancelled', 'rejected' => 'danger',
            default => 'gray',
        };
    }

    protected static function tableSuffix(): string
    {
        return 'send_instructions';
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
