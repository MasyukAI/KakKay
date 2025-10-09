<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Enums;

enum GoodsType: string
{
    case DOCUMENT = 'ITN2';
    case PACKAGE = 'ITN8';

    public function label(): string
    {
        return match ($this) {
            self::DOCUMENT => 'Document',
            self::PACKAGE => 'Package',
        };
    }
}
