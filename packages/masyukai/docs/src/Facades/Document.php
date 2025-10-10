<?php

declare(strict_types=1);

namespace MasyukAI\Docs\Facades;

use Illuminate\Support\Facades\Facade;
use MasyukAI\Docs\DataObjects\DocumentData;
use MasyukAI\Docs\Enums\DocumentStatus;

/**
 * @method static string generateDocumentNumber(string $documentType = 'invoice')
 * @method static \MasyukAI\Docs\Models\Document createDocument(DocumentData $data)
 * @method static string generatePdf(\MasyukAI\Docs\Models\Document $document, bool $save = true)
 * @method static string downloadPdf(\MasyukAI\Docs\Models\Document $document)
 * @method static void emailDocument(\MasyukAI\Docs\Models\Document $document, string $email)
 * @method static void updateDocumentStatus(\MasyukAI\Docs\Models\Document $document, DocumentStatus $status, ?string $notes = null)
 *
 * @see \MasyukAI\Docs\Services\DocumentService
 */
class Document extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'document';
    }
}
