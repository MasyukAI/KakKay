<?php

declare(strict_types=1);

namespace AIArmada\Docs\Facades;

use AIArmada\Docs\DataObjects\DocumentData;
use AIArmada\Docs\Enums\DocumentStatus;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string generateDocumentNumber(string $documentType = 'invoice')
 * @method static \AIArmada\Docs\Models\Document createDocument(DocumentData $data)
 * @method static string generatePdf(\AIArmada\Docs\Models\Document $document, bool $save = true)
 * @method static string downloadPdf(\AIArmada\Docs\Models\Document $document)
 * @method static void emailDocument(\AIArmada\Docs\Models\Document $document, string $email)
 * @method static void updateDocumentStatus(\AIArmada\Docs\Models\Document $document, DocumentStatus $status, ?string $notes = null)
 *
 * @see \AIArmada\Docs\Services\DocumentService
 */
class Document extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'document';
    }
}
