<?php

declare(strict_types=1);

namespace MasyukAI\Docs\Facades;

use Illuminate\Support\Facades\Facade;
use MasyukAI\Docs\DataObjects\InvoiceData;
use MasyukAI\Docs\Enums\InvoiceStatus;

/**
 * @method static string generateInvoiceNumber()
 * @method static \MasyukAI\Invoice\Models\Invoice createInvoice(InvoiceData $data)
 * @method static string generatePdf(\MasyukAI\Invoice\Models\Invoice $invoice, bool $save = true)
 * @method static string downloadPdf(\MasyukAI\Invoice\Models\Invoice $invoice)
 * @method static void emailInvoice(\MasyukAI\Invoice\Models\Invoice $invoice, string $email)
 * @method static void updateInvoiceStatus(\MasyukAI\Invoice\Models\Invoice $invoice, InvoiceStatus $status, ?string $notes = null)
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
