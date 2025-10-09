<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Exceptions;

use Throwable;

/**
 * Exception thrown when validation fails for J&T operations.
 *
 * This exception includes detailed information about which fields
 * failed validation and why.
 */
class JntValidationException extends JntException
{
    public function __construct(
        string $message,
        public readonly array $errors = [],
        public readonly ?string $field = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, null, $errors, $code, $previous);
    }

    /**
     * Create exception for field validation failure.
     */
    public static function fieldValidationFailed(string $field, string $reason, array $errors = []): self
    {
        return new self(
            message: "Validation failed for field '{$field}': {$reason}",
            errors: $errors,
            field: $field
        );
    }

    /**
     * Create exception for missing required field.
     */
    public static function requiredFieldMissing(string $field): self
    {
        return new self(
            message: "Required field '{$field}' is missing",
            errors: [$field => ['The field is required']],
            field: $field
        );
    }

    /**
     * Create exception for invalid field value.
     */
    public static function invalidFieldValue(string $field, mixed $value, string $expected): self
    {
        $valueStr = is_scalar($value) ? (string) $value : gettype($value);

        return new self(
            message: "Invalid value for field '{$field}': expected {$expected}, got {$valueStr}",
            errors: [$field => ["Expected {$expected}"]],
            field: $field
        );
    }

    /**
     * Create exception for field length violation.
     */
    public static function fieldTooLong(string $field, int $maxLength, int $actualLength): self
    {
        return new self(
            message: "Field '{$field}' exceeds maximum length of {$maxLength} characters (got {$actualLength})",
            errors: [$field => ["Maximum length is {$maxLength} characters"]],
            field: $field
        );
    }

    /**
     * Create exception for field length violation (too short).
     */
    public static function fieldTooShort(string $field, int $minLength, int $actualLength): self
    {
        return new self(
            message: "Field '{$field}' is below minimum length of {$minLength} characters (got {$actualLength})",
            errors: [$field => ["Minimum length is {$minLength} characters"]],
            field: $field
        );
    }

    /**
     * Create exception for numeric range violation.
     */
    public static function valueOutOfRange(string $field, float $min, float $max, float $actual): self
    {
        return new self(
            message: "Field '{$field}' value {$actual} is outside valid range ({$min}-{$max})",
            errors: [$field => ["Value must be between {$min} and {$max}"]],
            field: $field
        );
    }

    /**
     * Create exception for invalid format.
     */
    public static function invalidFormat(string $field, string $expectedFormat, mixed $value = null): self
    {
        $message = "Field '{$field}' has invalid format: expected {$expectedFormat}";
        if ($value !== null) {
            $valueStr = is_scalar($value) ? (string) $value : gettype($value);
            $message .= ", got '{$valueStr}'";
        }

        return new self(
            message: $message,
            errors: [$field => ["Expected format: {$expectedFormat}"]],
            field: $field
        );
    }

    /**
     * Create exception for multiple validation errors.
     */
    public static function multiple(array $errors): self
    {
        $fieldCount = count($errors);
        $fields = implode(', ', array_keys($errors));

        return new self(
            message: "Validation failed for {$fieldCount} field(s): {$fields}",
            errors: $errors
        );
    }
}
