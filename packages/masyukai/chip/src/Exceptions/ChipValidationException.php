<?php

declare(strict_types=1);

namespace MasyukAI\Chip\Exceptions;

use Illuminate\Contracts\Validation\Validator;

class ChipValidationException extends ChipApiException
{
    /**
     * @param array<string, mixed> $fieldErrors
     */
    public function __construct(
        string $message = 'Validation failed',
        array $fieldErrors = [],
        int $statusCode = 422,
        ?\Throwable $previous = null
    ) {
        $errorData = ['validation_errors' => $fieldErrors];
        parent::__construct($message, $statusCode, $errorData, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function getValidationErrors(): array
    {
        return $this->errorData['validation_errors'] ?? [];
    }

    public function hasValidationErrors(): bool
    {
        return ! empty($this->getValidationErrors());
    }

    public function hasFieldError(string $field): bool
    {
        return array_key_exists($field, $this->getValidationErrors());
    }

    public function hasError(string $field): bool
    {
        return $this->hasFieldError($field);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFieldErrors(string $field): array
    {
        return $this->getValidationErrors()[$field] ?? [];
    }

    public function formatValidationErrors(): string
    {
        $errors = [];
        foreach ($this->getValidationErrors() as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $errors[] = "{$field}: {$error}";
            }
        }

        return implode(', ', $errors);
    }

    public function getFormattedErrors(): string
    {
        return $this->formatValidationErrors();
    }

    public static function fromValidator(Validator $validator): self
    {
        return new self(
            'Validation failed',
            $validator->errors()->toArray()
        );
    }
}
