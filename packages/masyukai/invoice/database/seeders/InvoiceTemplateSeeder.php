<?php

declare(strict_types=1);

namespace MasyukAI\Invoice\Database\Seeders;

use Illuminate\Database\Seeder;
use MasyukAI\Invoice\Models\InvoiceTemplate;

class InvoiceTemplateSeeder extends Seeder
{
    public function run(): void
    {
        InvoiceTemplate::create([
            'name' => 'Default Template',
            'slug' => 'default',
            'description' => 'Clean and professional default invoice template with Tailwind CSS',
            'view_name' => 'default',
            'is_default' => true,
            'settings' => [
                'show_logo' => false,
                'primary_color' => '#1f2937',
                'accent_color' => '#3b82f6',
            ],
        ]);
    }
}
