<?php

declare(strict_types=1);

namespace AIArmada\Docs\Services;

use AIArmada\Docs\DataObjects\DocumentData;
use AIArmada\Docs\Enums\DocumentStatus;
use AIArmada\Docs\Models\Document;
use AIArmada\Docs\Models\DocumentTemplate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;

class DocumentService
{
    public function generateDocumentNumber(string $documentType = 'invoice'): string
    {
        $config = config("docs.types.{$documentType}.number_format");
        $prefix = $config['prefix'] ?? 'DOC';
        $yearFormat = $config['year_format'] ?? 'y';
        $separator = $config['separator'] ?? '-';
        $suffixLength = $config['suffix_length'] ?? 6;

        $year = now()->format($yearFormat);
        $suffix = mb_strtoupper(mb_substr(uniqid(), -$suffixLength));

        return "{$prefix}{$year}{$separator}{$suffix}";
    }

    public function createDocument(DocumentData $data): Document
    {
        $documentType = $data->documentType ?? 'invoice';

        // Generate document number if not provided
        $documentNumber = $data->documentNumber ?? $this->generateDocumentNumber($documentType);

        // Get template
        $template = null;
        if ($data->templateId) {
            $template = DocumentTemplate::find($data->templateId);
        } elseif ($data->templateSlug) {
            $template = DocumentTemplate::where('slug', $data->templateSlug)->first();
        }

        if (! $template) {
            $template = DocumentTemplate::where('is_default', true)
                ->where('document_type', $documentType)
                ->first();
        }

        // Calculate totals
        $subtotal = $this->calculateSubtotal($data->items);
        $taxAmount = $data->taxAmount ?? ($subtotal * ($data->taxRate ?? 0));
        $discountAmount = $data->discountAmount ?? 0;
        $total = $subtotal + $taxAmount - $discountAmount;

        // Create document
        $document = Document::create([
            'document_number' => $documentNumber,
            'document_type' => $documentType,
            'document_template_id' => $template?->id,
            'documentable_type' => $data->documentableType,
            'documentable_id' => $data->documentableId,
            'status' => $data->status ?? DocumentStatus::DRAFT,
            'issue_date' => $data->issueDate ?? now(),
            'due_date' => $data->dueDate ?? now()->addDays(config("docs.types.{$documentType}.defaults.due_days", 30)),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'currency' => $data->currency ?? config("docs.types.{$documentType}.defaults.currency", 'MYR'),
            'notes' => $data->notes,
            'terms' => $data->terms,
            'customer_data' => $data->customerData,
            'company_data' => $data->companyData ?? config('docs.company'),
            'items' => $data->items,
            'metadata' => $data->metadata,
        ]);

        // Generate PDF if requested
        if ($data->generatePdf ?? false) {
            $this->generatePdf($document);
        }

        return $document;
    }

    public function generatePdf(Document $document, bool $save = true): string
    {
        // Load the polymorphic relationship to access ticket/order data
        $document->loadMissing('documentable');

        $documentType = $document->document_type ?? 'invoice';
        $template = $document->template ?? DocumentTemplate::where('is_default', true)
            ->where('document_type', $documentType)
            ->first();
        $viewName = $template->view_name ?? config("docs.types.{$documentType}.default_template", "{$documentType}-default");

        $pdf = Pdf::view("docs::templates.{$viewName}", [
            'document' => $document,
            // Keep backward compatibility
            'invoice' => $document,
        ])
            ->format(config('docs.pdf.format', 'a4'))
            ->orientation(config('docs.pdf.orientation', 'portrait'))
            ->margins(
                config('docs.pdf.margin.top', 10),
                config('docs.pdf.margin.right', 10),
                config('docs.pdf.margin.bottom', 10),
                config('docs.pdf.margin.left', 10)
            );

        if ($save) {
            $path = $this->generatePdfPath($document);
            $disk = config("docs.types.{$documentType}.storage.disk", 'local');

            Storage::disk($disk)->put($path, $pdf->getBrowsershot()->pdf());

            $document->update(['pdf_path' => $path]);

            return Storage::disk($disk)->url($path);
        }

        return $pdf->getBrowsershot()->pdf();
    }

    public function downloadPdf(Document $document): string
    {
        $documentType = $document->document_type ?? 'invoice';

        if ($document->pdf_path && Storage::disk(config("docs.types.{$documentType}.storage.disk", 'local'))->exists($document->pdf_path)) {
            return Storage::disk(config("docs.types.{$documentType}.storage.disk", 'local'))->url($document->pdf_path);
        }

        return $this->generatePdf($document);
    }

    public function emailDocument(Document $document, string $email): void
    {
        // This would integrate with your mail system
        // Implementation depends on your mail setup
        $document->markAsSent();
    }

    public function updateDocumentStatus(Document $document, DocumentStatus $status, ?string $notes = null): void
    {
        $oldStatus = $document->status;

        $document->update(['status' => $status]);

        // Record status change
        $document->statusHistories()->create([
            'status' => $status,
            'notes' => $notes ?? "Status changed from {$oldStatus->label()} to {$status->label()}",
        ]);
    }

    /**
     * Calculate subtotal from items
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function calculateSubtotal(array $items): float
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $subtotal += $quantity * $price;
        }

        return $subtotal;
    }

    protected function generatePdfPath(Document $document): string
    {
        $documentType = $document->document_type ?? 'invoice';
        $basePath = config("docs.types.{$documentType}.storage.path", 'documents');
        $filename = Str::slug($document->document_number).'.pdf';

        return "{$basePath}/{$filename}";
    }
}
