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
            'default_template' => 'invoice-default',
            'number_format' => [
                'prefix' => 'INV',
                'year_format' => 'y',
                'separator' => '-',
                'suffix_length' => 6,
            ],
            'storage' => [
                'disk' => env('INVOICE_STORAGE_DISK', 'local'),
                'path' => env('INVOICE_STORAGE_PATH', 'invoices'),
            ],
            'defaults' => [
                'currency' => env('INVOICE_CURRENCY', 'MYR'),
                'tax_rate' => env('INVOICE_TAX_RATE', 0),
                'due_days' => env('INVOICE_DUE_DAYS', 30),
            ],
        ],
        'receipt' => [
            'default_template' => 'receipt-default',
            'number_format' => [
                'prefix' => 'RCP',
                'year_format' => 'y',
                'separator' => '-',
                'suffix_length' => 6,
            ],
            'storage' => [
                'disk' => env('RECEIPT_STORAGE_DISK', 'local'),
                'path' => env('RECEIPT_STORAGE_PATH', 'receipts'),
            ],
            'defaults' => [
                'currency' => env('RECEIPT_CURRENCY', 'MYR'),
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
        'name' => env('DOCS_COMPANY_NAME', config('app.name')),
        'address' => env('DOCS_COMPANY_ADDRESS'),
        'city' => env('DOCS_COMPANY_CITY'),
        'state' => env('DOCS_COMPANY_STATE'),
        'postal_code' => env('DOCS_COMPANY_POSTAL_CODE'),
        'country' => env('DOCS_COMPANY_COUNTRY'),
        'phone' => env('DOCS_COMPANY_PHONE'),
        'email' => env('DOCS_COMPANY_EMAIL'),
        'website' => env('DOCS_COMPANY_WEBSITE'),
        'tax_id' => env('DOCS_COMPANY_TAX_ID'),
    ],
];
