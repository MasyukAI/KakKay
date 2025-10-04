<?php

use MasyukAI\Chip\Exceptions\ChipApiException;
use MasyukAI\Chip\Exceptions\ChipValidationException;
use MasyukAI\Chip\Exceptions\WebhookVerificationException;

describe('ChipApiException', function () {
    it('creates exception with message and status code', function () {
        $exception = new ChipApiException('API request failed', 400);

        expect($exception->getMessage())->toBe('API request failed');
        expect($exception->getStatusCode())->toBe(400);
        expect($exception->getErrorDetails())->toBe([]);
    });

    it('stores error details from API response', function () {
        $errorDetails = [
            'field' => 'amount_in_cents',
            'message' => 'Must be greater than 0',
        ];

        $exception = new ChipApiException('Validation failed', 422, $errorDetails);

        expect($exception->getErrorDetails())->toBe($errorDetails);
        expect($exception->hasErrorDetails())->toBeTrue();
    });

    it('handles empty error details', function () {
        $exception = new ChipApiException('Server error', 500);

        expect($exception->getErrorDetails())->toBe([]);
        expect($exception->hasErrorDetails())->toBeFalse();
    });

    it('formats error message with details', function () {
        $errorDetails = [
            'errors' => [
                'amount_in_cents' => ['Must be greater than 0'],
                'currency' => ['Invalid currency code'],
            ],
        ];

        $exception = new ChipApiException('Validation failed', 422, $errorDetails);

        expect($exception->getFormattedMessage())->toContain('Validation failed');
        expect($exception->getFormattedMessage())->toContain('amount_in_cents');
        expect($exception->getFormattedMessage())->toContain('currency');
    });

    it('creates exception from HTTP response', function () {
        $responseData = [
            'error' => 'Insufficient funds',
            'code' => 'INSUFFICIENT_FUNDS',
            'details' => ['available_balance' => 5000],
        ];

        $exception = ChipApiException::fromResponse($responseData, 402);

        expect($exception->getMessage())->toBe('Insufficient funds');
        expect($exception->getStatusCode())->toBe(402);
        expect($exception->getErrorDetails())->toBe([
            'code' => 'INSUFFICIENT_FUNDS',
            'details' => ['available_balance' => 5000],
        ]);
    });

    it('handles missing error message in response', function () {
        $responseData = [
            'code' => 'UNKNOWN_ERROR',
            'details' => ['timestamp' => '2024-01-01T12:00:00Z'],
        ];

        $exception = ChipApiException::fromResponse($responseData, 500);

        expect($exception->getMessage())->toBe('Unknown API error');
        expect($exception->getErrorDetails())->toBe($responseData);
    });
});

describe('ChipValidationException', function () {
    it('creates validation exception with field errors', function () {
        $errors = [
            'amount_in_cents' => ['Required field'],
            'currency' => ['Invalid currency code'],
        ];

        $exception = new ChipValidationException('Validation failed', $errors);

        expect($exception->getMessage())->toBe('Validation failed');
        expect($exception->getValidationErrors())->toBe($errors);
    });

    it('checks if specific field has error', function () {
        $errors = [
            'amount_in_cents' => ['Required field'],
            'currency' => ['Invalid currency code'],
        ];

        $exception = new ChipValidationException('Validation failed', $errors);

        expect($exception->hasError('amount_in_cents'))->toBeTrue();
        expect($exception->hasError('reference'))->toBeFalse();
    });

    it('gets errors for specific field', function () {
        $errors = [
            'amount_in_cents' => ['Required field', 'Must be positive'],
            'currency' => ['Invalid currency code'],
        ];

        $exception = new ChipValidationException('Validation failed', $errors);

        expect($exception->getFieldErrors('amount_in_cents'))->toBe(['Required field', 'Must be positive']);
        expect($exception->getFieldErrors('unknown_field'))->toBe([]);
    });

    it('formats all validation errors as string', function () {
        $errors = [
            'amount_in_cents' => ['Required field'],
            'currency' => ['Invalid currency code'],
        ];

        $exception = new ChipValidationException('Validation failed', $errors);
        $formatted = $exception->getFormattedErrors();

        expect($formatted)->toContain('amount_in_cents: Required field');
        expect($formatted)->toContain('currency: Invalid currency code');
    });

    it('creates exception from Laravel validator', function () {
        $validator = validator([
            'amount_in_cents' => null,
            'currency' => 'INVALID',
        ], [
            'amount_in_cents' => 'required|integer|min:1',
            'currency' => 'required|in:MYR,USD,SGD',
        ]);

        // Check if validation fails without throwing exception
        expect($validator->fails())->toBeTrue();

        $exception = ChipValidationException::fromValidator($validator);

        expect($exception)->toBeInstanceOf(ChipValidationException::class);
        expect($exception->hasError('amount_in_cents'))->toBeTrue();
        expect($exception->hasError('currency'))->toBeTrue();
    });
});

describe('WebhookVerificationException', function () {
    it('creates exception with verification failure message', function () {
        $exception = new WebhookVerificationException('Invalid signature');

        expect($exception->getMessage())->toBe('Invalid signature');
        expect($exception->getCode())->toBe(0);
    });

    it('creates exception for missing signature', function () {
        $exception = WebhookVerificationException::missingSignature();

        expect($exception->getMessage())->toContain('missing');
        expect($exception->getMessage())->toContain('signature');
    });

    it('creates exception for invalid signature format', function () {
        $exception = WebhookVerificationException::invalidSignatureFormat();

        expect($exception->getMessage())->toContain('invalid');
        expect($exception->getMessage())->toContain('format');
    });

    it('creates exception for signature verification failure', function () {
        $exception = WebhookVerificationException::verificationFailed();

        expect($exception->getMessage())->toContain('verification');
        expect($exception->getMessage())->toContain('failed');
    });

    it('creates exception for invalid payload', function () {
        $exception = WebhookVerificationException::invalidPayload('Malformed JSON');

        expect($exception->getMessage())->toContain('Invalid payload');
        expect($exception->getMessage())->toContain('Malformed JSON');
    });

    it('creates exception for missing public key', function () {
        $exception = WebhookVerificationException::missingPublicKey();

        expect($exception->getMessage())->toContain('public key');
        expect($exception->getMessage())->toContain('configured');
    });
});

describe('Exception Error Context', function () {
    it('preserves original exception context', function () {
        $previous = new \Exception('Original error');
        $exception = new ChipApiException('API error', 500, [], $previous);

        expect($exception->getPrevious())->toBe($previous);
    });

    it('maintains error context through exception chain', function () {
        $httpException = new \Exception('HTTP connection failed');
        $apiException = new ChipApiException('Request failed', 500, [], $httpException);

        expect($apiException->getPrevious())->toBe($httpException);
        expect($apiException->getPrevious()->getMessage())->toBe('HTTP connection failed');
    });
});
