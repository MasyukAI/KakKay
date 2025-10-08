<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Invoice Template
    |--------------------------------------------------------------------------
    |
    | The default template to use when generating invoices. This should match
    | a Blade view in the resources/views/templates directory.
    |
    */
    'default_template' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Invoice Number Format
    |--------------------------------------------------------------------------
    |
    | Configure the format for invoice numbers. The prefix will be prepended
    | to the auto-incrementing number.
    |
    */
    'number_format' => [
        'prefix' => 'INV',
        'year_format' => 'y', // 'y' for 2-digit, 'Y' for 4-digit year
        'separator' => '-',
        'suffix_length' => 6, // Length of the random suffix
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Storage
    |--------------------------------------------------------------------------
    |
    | Configure where generated PDFs should be stored. You can use any
    | disk configured in your filesystems.php config.
    |
    */
    'storage' => [
        'disk' => env('INVOICE_STORAGE_DISK', 'local'),
        'path' => env('INVOICE_STORAGE_PATH', 'invoices'),
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
    | Invoice Defaults
    |--------------------------------------------------------------------------
    |
    | Default values for invoices
    |
    */
    'defaults' => [
        'currency' => env('INVOICE_CURRENCY', 'MYR'),
        'tax_rate' => env('INVOICE_TAX_RATE', 0),
        'due_days' => env('INVOICE_DUE_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    |
    | Default company information to display on invoices. This can be
    | overridden when creating an invoice.
    |
    */
    'company' => [
        'name' => env('INVOICE_COMPANY_NAME', config('app.name')),
        'address' => env('INVOICE_COMPANY_ADDRESS'),
        'city' => env('INVOICE_COMPANY_CITY'),
        'state' => env('INVOICE_COMPANY_STATE'),
        'postal_code' => env('INVOICE_COMPANY_POSTAL_CODE'),
        'country' => env('INVOICE_COMPANY_COUNTRY'),
        'phone' => env('INVOICE_COMPANY_PHONE'),
        'email' => env('INVOICE_COMPANY_EMAIL'),
        'website' => env('INVOICE_COMPANY_WEBSITE'),
        'tax_id' => env('INVOICE_COMPANY_TAX_ID'),
    ],
];
