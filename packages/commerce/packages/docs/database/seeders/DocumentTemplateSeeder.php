<?php

declare(strict_types=1);

namespace MasyukAI\Docs\Database\Seeders;

use Illuminate\Database\Seeder;
use MasyukAI\Docs\Models\DocumentTemplate;

class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DocumentTemplate::create([
            'name' => 'Default Document Template',
            'slug' => 'document-default',
            'description' => 'Clean and professional default document template with Tailwind CSS',
            'view_name' => 'document-default',
            'document_type' => 'invoice',
            'is_default' => true,
            'settings' => [
                'show_logo' => false,
                'primary_color' => '#1f2937',
                'accent_color' => '#3b82f6',
            ],
        ]);
    }
}
