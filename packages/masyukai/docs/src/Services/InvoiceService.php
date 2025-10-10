<?php

declare(strict_types=1);

namespace MasyukAI\Docs\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MasyukAI\Docs\DataObjects\InvoiceData;
use MasyukAI\Docs\Enums\InvoiceStatus;
use MasyukAI\Docs\Models\Invoice;
use MasyukAI\Docs\Models\InvoiceTemplate;
use Spatie\LaravelPdf\Facades\Pdf;

/**
 * Backward compatibility wrapper for DocumentService
 * @deprecated Use DocumentService instead
 */
class InvoiceService
{
    protected DocumentService $documentService;

    public function __construct(?DocumentService $documentService = null)
    {
        $this->documentService = $documentService ?? new DocumentService;
    }

    public function generateInvoiceNumber(): string
    {
        return $this->documentService->generateDocumentNumber('invoice');
    }

    public function createInvoice(InvoiceData $data): Invoice
    {
        $documentData = $data->toDocumentData();
        $document = $this->documentService->createDocument($documentData);
        
        // Return as Invoice instance for backward compatibility
        return Invoice::find($document->id);
    }

    public function generatePdf(Invoice $invoice, bool $save = true): string
    {
        return $this->documentService->generatePdf($invoice, $save);
    }

    public function downloadPdf(Invoice $invoice): string
    {
        return $this->documentService->downloadPdf($invoice);
    }

    public function emailInvoice(Invoice $invoice, string $email): void
    {
        $this->documentService->emailDocument($invoice, $email);
    }

    public function updateInvoiceStatus(Invoice $invoice, InvoiceStatus $status, ?string $notes = null): void
    {
        $documentStatus = \MasyukAI\Docs\Enums\DocumentStatus::from($status->value);
        $this->documentService->updateDocumentStatus($invoice, $documentStatus, $notes);
    }
}
