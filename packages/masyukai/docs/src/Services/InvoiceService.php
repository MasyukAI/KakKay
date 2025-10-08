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

class InvoiceService
{
    public function generateInvoiceNumber(): string
    {
        $config = config('docs.number_format');
        $prefix = $config['prefix'] ?? 'INV';
        $yearFormat = $config['year_format'] ?? 'y';
        $separator = $config['separator'] ?? '-';
        $suffixLength = $config['suffix_length'] ?? 6;

        $year = now()->format($yearFormat);
        $suffix = mb_strtoupper(mb_substr(uniqid(), -$suffixLength));

        return "{$prefix}{$year}{$separator}{$suffix}";
    }

    public function createInvoice(InvoiceData $data): Invoice
    {
        // Generate invoice number if not provided
        $invoiceNumber = $data->invoiceNumber ?? $this->generateInvoiceNumber();

        // Get template
        $template = null;
        if ($data->templateId) {
            $template = InvoiceTemplate::find($data->templateId);
        } elseif ($data->templateSlug) {
            $template = InvoiceTemplate::where('slug', $data->templateSlug)->first();
        }

        if (! $template) {
            $template = InvoiceTemplate::where('is_default', true)->first();
        }

        // Calculate totals
        $subtotal = $this->calculateSubtotal($data->items);
        $taxAmount = $data->taxAmount ?? ($subtotal * ($data->taxRate ?? 0));
        $discountAmount = $data->discountAmount ?? 0;
        $total = $subtotal + $taxAmount - $discountAmount;

        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'invoice_template_id' => $template?->id,
            'invoiceable_type' => $data->invoiceableType,
            'invoiceable_id' => $data->invoiceableId,
            'status' => $data->status ?? InvoiceStatus::DRAFT,
            'issue_date' => $data->issueDate ?? now(),
            'due_date' => $data->dueDate ?? now()->addDays(config('docs.defaults.due_days', 30)),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'currency' => $data->currency ?? config('docs.defaults.currency', 'MYR'),
            'notes' => $data->notes,
            'terms' => $data->terms,
            'customer_data' => $data->customerData,
            'company_data' => $data->companyData ?? config('docs.company'),
            'items' => $data->items,
            'metadata' => $data->metadata,
        ]);

        // Generate PDF if requested
        if ($data->generatePdf ?? false) {
            $this->generatePdf($invoice);
        }

        return $invoice;
    }

    public function generatePdf(Invoice $invoice, bool $save = true): string
    {
        $template = $invoice->template ?? InvoiceTemplate::where('is_default', true)->first();
        $viewName = $template?->view_name ?? config('docs.default_template', 'default');

        $pdf = Pdf::view("docs::templates.{$viewName}", [
            'invoice' => $invoice,
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
            $path = $this->generatePdfPath($invoice);
            $disk = config('docs.storage.disk', 'local');

            Storage::disk($disk)->put($path, $pdf->string());

            $invoice->update(['pdf_path' => $path]);

            return Storage::disk($disk)->url($path);
        }

        return $pdf->string();
    }

    public function downloadPdf(Invoice $invoice): string
    {
        if ($invoice->pdf_path && Storage::disk(config('docs.storage.disk', 'local'))->exists($invoice->pdf_path)) {
            return Storage::disk(config('docs.storage.disk', 'local'))->url($invoice->pdf_path);
        }

        return $this->generatePdf($invoice);
    }

    public function emailInvoice(Invoice $invoice, string $email): void
    {
        // This would integrate with your mail system
        // Implementation depends on your mail setup
        $invoice->markAsSent();
    }

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

    protected function generatePdfPath(Invoice $invoice): string
    {
        $basePath = config('docs.storage.path', 'invoices');
        $filename = Str::slug($invoice->invoice_number).'.pdf';

        return "{$basePath}/{$filename}";
    }

    public function updateInvoiceStatus(Invoice $invoice, InvoiceStatus $status, ?string $notes = null): void
    {
        $oldStatus = $invoice->status;

        $invoice->update(['status' => $status]);

        // Record status change
        $invoice->statusHistories()->create([
            'status' => $status,
            'notes' => $notes ?? "Status changed from {$oldStatus->label()} to {$status->label()}",
        ]);
    }
}
