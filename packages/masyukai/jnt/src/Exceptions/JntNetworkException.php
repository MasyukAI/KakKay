<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Exceptions;

use Throwable;

/**
 * Exception thrown when network/connection errors occur.
 *
 * This exception handles HTTP-level errors such as timeouts,
 * connection failures, and server errors.
 */
class JntNetworkException extends JntException
{
    public function __construct(
        string $message,
        public readonly ?string $endpoint = null,
        public readonly ?int $httpStatus = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, null, null, $code, $previous);
    }

    /**
     * Create exception for connection failure.
     */
    public static function connectionFailed(string $endpoint, Throwable $previous): self
    {
        return new self(
            message: "Failed to connect to J&T API endpoint: {$endpoint}",
            endpoint: $endpoint,
            previous: $previous
        );
    }

    /**
     * Create exception for request timeout.
     */
    public static function timeout(string $endpoint, int $seconds): self
    {
        return new self(
            message: "Request to {$endpoint} timed out after {$seconds} seconds",
            endpoint: $endpoint
        );
    }

    /**
     * Create exception for server error (5xx).
     */
    public static function serverError(string $endpoint, int $httpStatus, mixed $response = null): self
    {
        return new self(
            message: "J&T API server error (HTTP {$httpStatus}) at {$endpoint}",
            endpoint: $endpoint,
            httpStatus: $httpStatus
        );
    }

    /**
     * Create exception for client error (4xx).
     */
    public static function clientError(string $endpoint, int $httpStatus, mixed $response = null): self
    {
        return new self(
            message: "J&T API client error (HTTP {$httpStatus}) at {$endpoint}",
            endpoint: $endpoint,
            httpStatus: $httpStatus
        );
    }

    /**
     * Create exception for DNS resolution failure.
     */
    public static function dnsResolutionFailed(string $host): self
    {
        return new self(
            message: "Failed to resolve DNS for J&T API host: {$host}",
            endpoint: $host
        );
    }

    /**
     * Create exception for SSL/TLS errors.
     */
    public static function sslError(string $endpoint, string $reason): self
    {
        return new self(
            message: "SSL/TLS error connecting to {$endpoint}: {$reason}",
            endpoint: $endpoint
        );
    }

    /**
     * Create exception for proxy errors.
     */
    public static function proxyError(string $endpoint, string $reason): self
    {
        return new self(
            message: "Proxy error connecting to {$endpoint}: {$reason}",
            endpoint: $endpoint
        );
    }

    /**
     * Create exception for too many redirects.
     */
    public static function tooManyRedirects(string $endpoint, int $count): self
    {
        return new self(
            message: "Too many redirects ({$count}) when connecting to {$endpoint}",
            endpoint: $endpoint
        );
    }
}
