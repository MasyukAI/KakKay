<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Exceptions;

class InvalidCartItemException extends CartException
{
    public function __construct(string $message = 'Invalid cart item')
    {
        parent::__construct($message);
    }
}
