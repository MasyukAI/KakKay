<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Document Types Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for different document types supported by the package.
    | Currently supports: invoices and receipts (more can be added).
    |
    */

    'types' => [
        'invoice' => [
            'default_template' => 'invoice-kakkay',
            'number_format' => [
                'prefix' => 'INV',
                'year_format' => 'y',
                'separator' => '-',
                'suffix_length' => 6,
            ],
            'storage' => [
                'disk' => env('DOCS_STORAGE_DISK', 'local'),
                'path' => env('DOCS_STORAGE_PATH', 'docs/invoices'),
            ],
            'defaults' => [
                'currency' => env('DOCS_CURRENCY', 'MYR'),
                'tax_rate' => env('DOCS_TAX_RATE', 0),
                'due_days' => env('DOCS_DUE_DAYS', 30),
            ],
        ],
        'receipt' => [
            'default_template' => 'doc-default',
            'number_format' => [
                'prefix' => 'RCP',
                'year_format' => 'y',
                'separator' => '-',
                'suffix_length' => 6,
            ],
            'storage' => [
                'disk' => env('DOCS_STORAGE_DISK', 'local'),
                'path' => env('DOCS_STORAGE_PATH', 'docs/receipts'),
            ],
            'defaults' => [
                'currency' => env('DOCS_CURRENCY', 'MYR'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PDF generation via Spatie Laravel PDF
    |
    */
    'pdf' => [
        'format' => 'a4',
        'orientation' => 'portrait',
        'margin' => [
            'top' => 10,
            'right' => 10,
            'bottom' => 10,
            'left' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    |
    | Default company information to display on documents. This can be
    | overridden when creating a document.
    |
    */
    'company' => [
        'name' => env('DOCS_COMPANY_NAME', 'Kak Kay'),
        'address' => env('DOCS_COMPANY_ADDRESS', 'Kuala Lumpur'),
        'city' => env('DOCS_COMPANY_CITY', 'Kuala Lumpur'),
        'state' => env('DOCS_COMPANY_STATE', 'Wilayah Persekutuan'),
        'postal_code' => env('DOCS_COMPANY_POSTAL_CODE', '50000'),
        'country' => env('DOCS_COMPANY_COUNTRY', 'Malaysia'),
        'phone' => env('DOCS_COMPANY_PHONE', '+60-XXX-XXXXXX'),
        'email' => env('DOCS_COMPANY_EMAIL', 'hello@kakkay.my'),
        'website' => env('DOCS_COMPANY_WEBSITE', 'https://kakkay.my'),
        'tax_id' => env('DOCS_COMPANY_TAX_ID'),
    ],
];
