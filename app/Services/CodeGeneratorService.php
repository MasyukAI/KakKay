<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use Exception;
use Illuminate\Database\QueryException;

class CodeGeneratorService
{
    /**
     * Generate order code (optimized - relies on database constraint)
     */
    public static function generateOrderCode(): string
    {
        return 'ORD'.now()->format('y').'-'.mb_strtoupper(mb_substr(uniqid(), -6));
    }

    /**
     * Generate order code with database uniqueness guarantee
     * Use this when you need to ensure uniqueness before insert
     */
    public static function generateUniqueOrderCode(): string
    {
        $maxRetries = 10;
        $retries = 0;

        do {
            $code = self::generateOrderCode();

            try {
                // Quick check - only if we've had collisions
                if ($retries > 0 && Order::where('order_number', $code)->exists()) {
                    continue;
                }

                return $code;
            } catch (QueryException $e) {
                if (++$retries >= $maxRetries) {
                    throw new Exception("Unable to generate unique order code after {$maxRetries} attempts");
                }
            }
        } while ($retries < $maxRetries);

        throw new Exception('Unable to generate unique order code');
    }

    /**
     * Generate invoice code (assumes invoices are stored in orders table or separate table)
     */
    public static function generateInvoiceCode(): string
    {
        return 'INV'.now()->format('y').'-'.mb_strtoupper(mb_substr(uniqid(), -6));
    }

    /**
     * Generate payment code (optimized - relies on database constraint)
     */
    public static function generatePaymentCode(): string
    {
        return 'PMT'.now()->format('y').'-'.mb_strtoupper(mb_substr(uniqid(), -6));
    }

    /**
     * Generate refund code (assumes refunds are stored in payments table or separate table)
     */
    public static function generateRefundCode(): string
    {
        return 'RFD'.now()->format('y').'-'.mb_strtoupper(mb_substr(uniqid(), -6));
    }

    /**
     * Generate shipment code (optimized - relies on database constraint)
     */
    public static function generateShipmentCode(): string
    {
        return 'SHP'.now()->format('y').'-'.mb_strtoupper(mb_substr(uniqid(), -6));
    }

    /**
     * Generate a generic code with custom prefix
     * Note: This doesn't check database uniqueness - use specific methods above for guaranteed uniqueness
     */
    public static function generateCode(string $prefix): string
    {
        return mb_strtoupper($prefix).now()->format('y').'-'.mb_strtoupper(mb_substr(uniqid(), -6));
    }

    /**
     * Validate code format
     */
    public static function isValidCodeFormat(string $code, ?string $prefix = null): bool
    {
        // Basic pattern: 3 letters, 2 digits, dash, 6 alphanumeric characters
        $pattern = '/^[A-Z]{3}\d{2}-[A-Z0-9]{6}$/';

        if (! preg_match($pattern, $code)) {
            return false;
        }

        // If prefix is provided, check it matches
        if ($prefix && ! str_starts_with($code, mb_strtoupper($prefix))) {
            return false;
        }

        return true;
    }
}
