<?php

declare(strict_types=1);

namespace MasyukAI\Invoice\DataObjects;

use DateTimeInterface;
use MasyukAI\Invoice\Enums\InvoiceStatus;

class InvoiceData
{
    public function __construct(
        public readonly ?string $invoiceNumber = null,
        public readonly ?string $templateId = null,
        public readonly ?string $templateSlug = null,
        public readonly ?string $invoiceableType = null,
        public readonly ?string $invoiceableId = null,
        public readonly ?InvoiceStatus $status = null,
        public readonly ?DateTimeInterface $issueDate = null,
        public readonly ?DateTimeInterface $dueDate = null,
        public readonly array $items = [],
        public readonly ?float $taxRate = null,
        public readonly ?float $taxAmount = null,
        public readonly ?float $discountAmount = null,
        public readonly ?string $currency = null,
        public readonly ?string $notes = null,
        public readonly ?string $terms = null,
        public readonly ?array $customerData = null,
        public readonly ?array $companyData = null,
        public readonly ?array $metadata = null,
        public readonly ?bool $generatePdf = false,
    ) {
    }

    public static function from(array $data): self
    {
        return new self(
            invoiceNumber: $data['invoice_number'] ?? null,
            templateId: $data['template_id'] ?? null,
            templateSlug: $data['template_slug'] ?? null,
            invoiceableType: $data['invoiceable_type'] ?? null,
            invoiceableId: $data['invoiceable_id'] ?? null,
            status: isset($data['status']) ? (is_string($data['status']) ? InvoiceStatus::from($data['status']) : $data['status']) : null,
            issueDate: $data['issue_date'] ?? null,
            dueDate: $data['due_date'] ?? null,
            items: $data['items'] ?? [],
            taxRate: $data['tax_rate'] ?? null,
            taxAmount: $data['tax_amount'] ?? null,
            discountAmount: $data['discount_amount'] ?? null,
            currency: $data['currency'] ?? null,
            notes: $data['notes'] ?? null,
            terms: $data['terms'] ?? null,
            customerData: $data['customer_data'] ?? null,
            companyData: $data['company_data'] ?? null,
            metadata: $data['metadata'] ?? null,
            generatePdf: $data['generate_pdf'] ?? false,
        );
    }
}
