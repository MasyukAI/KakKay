<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Exceptions;

class UnknownModelException extends CartException
{
    public function __construct(string $message = 'Unknown model class', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
