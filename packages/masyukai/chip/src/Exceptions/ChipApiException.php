<?php

declare(strict_types=1);

namespace Masyukai\Chip\Exceptions;

use Exception;

class ChipApiException extends Exception
{
    public function __construct(
        string $message = '',
        protected int $statusCode = 0,
        protected array $errorData = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorData(): array
    {
        return $this->errorData;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorData['code'] ?? null;
    }

    public function getErrorMessage(): string
    {
        return $this->errorData['message'] ?? $this->getMessage();
    }

    public function getErrorDetails(): array
    {
        return $this->errorData;
    }

    public function hasErrorDetails(): bool
    {
        return !empty($this->errorData);
    }

    public function getFormattedMessage(): string
    {
        $message = $this->getMessage();
        
        if (!empty($this->errorData)) {
            $details = [];
            foreach ($this->errorData as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $details[] = "{$key}: {$value}";
            }
            
            if (!empty($details)) {
                $message .= ' - ' . implode(', ', $details);
            }
        }
        
        return $message;
    }

    public static function fromResponse(array $responseData, int $statusCode): self
    {
        $message = $responseData['error'] ?? $responseData['message'] ?? 'Unknown API error';
        
        // Extract the error details, excluding the message
        $errorDetails = $responseData;
        unset($errorDetails['error'], $errorDetails['message']);
        
        return new self($message, $statusCode, $errorDetails);
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'status_code' => $this->statusCode,
            'error_data' => $this->errorData,
        ];
    }
}
