<?php

declare(strict_types=1);

namespace MasyukAI\Invoice\Facades;

use Illuminate\Support\Facades\Facade;
use MasyukAI\Invoice\DataObjects\InvoiceData;
use MasyukAI\Invoice\Enums\InvoiceStatus;
use MasyukAI\Invoice\Models\Invoice;

/**
 * @method static string generateInvoiceNumber()
 * @method static Invoice createInvoice(InvoiceData $data)
 * @method static string generatePdf(Invoice $invoice, bool $save = true)
 * @method static string downloadPdf(Invoice $invoice)
 * @method static void emailInvoice(Invoice $invoice, string $email)
 * @method static void updateInvoiceStatus(Invoice $invoice, InvoiceStatus $status, ?string $notes = null)
 *
 * @see \MasyukAI\Invoice\Services\InvoiceService
 */
class Invoice extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'invoice';
    }
}
