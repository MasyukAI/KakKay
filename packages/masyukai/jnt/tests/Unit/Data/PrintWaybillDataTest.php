<?php

declare(strict_types=1);

use MasyukAI\Jnt\Data\PrintWaybillData;

describe('PrintWaybillData', function () {
    it('creates from API array with base64 content (single parcel)', function () {
        $data = [
            'txlogisticId' => 'ORDER-123',
            'billCode' => 'JT987654321',
            'base64EncodeContent' => base64_encode('PDF content here'),
        ];

        $result = PrintWaybillData::fromApiArray($data);

        expect($result)
            ->toBeInstanceOf(PrintWaybillData::class)
            ->orderId->toBe('ORDER-123')
            ->trackingNumber->toBe('JT987654321')
            ->base64Content->toBe($data['base64EncodeContent'])
            ->urlContent->toBeNull()
            ->isMultiParcel->toBeFalse();
    });

    it('creates from API array with URL content (multi parcel)', function () {
        $data = [
            'txlogisticId' => 'ORDER-456',
            'billCode' => 'JT123456789',
            'urlContent' => 'https://api.jnt.com/download/waybill.pdf',
        ];

        $result = PrintWaybillData::fromApiArray($data);

        expect($result)
            ->toBeInstanceOf(PrintWaybillData::class)
            ->orderId->toBe('ORDER-456')
            ->urlContent->toBe('https://api.jnt.com/download/waybill.pdf')
            ->base64Content->toBeNull()
            ->isMultiParcel->toBeTrue();
    });

    it('handles both base64 and URL content', function () {
        $data = [
            'txlogisticId' => 'ORDER-789',
            'base64EncodeContent' => base64_encode('PDF'),
            'urlContent' => 'https://example.com/pdf',
        ];

        $result = PrintWaybillData::fromApiArray($data);

        expect($result)
            ->base64Content->not->toBeNull()
            ->urlContent->not->toBeNull()
            ->isMultiParcel->toBeFalse(); // Base64 takes precedence
    });

    it('accepts clean field names (orderId, trackingNumber)', function () {
        $data = [
            'orderId' => 'ORDER-CLEAN',
            'trackingNumber' => 'JT-CLEAN',
        ];

        $result = PrintWaybillData::fromApiArray($data);

        expect($result)
            ->orderId->toBe('ORDER-CLEAN')
            ->trackingNumber->toBe('JT-CLEAN');
    });

    it('includes template name', function () {
        $data = [
            'txlogisticId' => 'ORDER-123',
            'templateName' => 'custom_template',
        ];

        $result = PrintWaybillData::fromApiArray($data);

        expect($result->templateName)->toBe('custom_template');
    });

    it('converts to array', function () {
        $original = [
            'orderId' => 'ORDER-123',
            'trackingNumber' => 'JT123',
            'base64Content' => 'base64data',
            'urlContent' => null,
            'isMultiParcel' => false,
            'templateName' => 'default',
        ];

        $data = new PrintWaybillData(...$original);
        $array = $data->toArray();

        expect($array)->toBe($original);
    });

    it('detects base64 content availability', function () {
        $withBase64 = new PrintWaybillData(
            orderId: 'ORDER-1',
            trackingNumber: null,
            base64Content: 'content',
            urlContent: null,
            isMultiParcel: false
        );

        $withoutBase64 = new PrintWaybillData(
            orderId: 'ORDER-2',
            trackingNumber: null,
            base64Content: null,
            urlContent: 'url',
            isMultiParcel: true
        );

        expect($withBase64->hasBase64Content())->toBeTrue()
            ->and($withoutBase64->hasBase64Content())->toBeFalse();
    });

    it('detects URL content availability', function () {
        $withUrl = new PrintWaybillData(
            orderId: 'ORDER-1',
            trackingNumber: null,
            base64Content: null,
            urlContent: 'https://example.com',
            isMultiParcel: true
        );

        $withoutUrl = new PrintWaybillData(
            orderId: 'ORDER-2',
            trackingNumber: null,
            base64Content: 'content',
            urlContent: null,
            isMultiParcel: false
        );

        expect($withUrl->hasUrlContent())->toBeTrue()
            ->and($withoutUrl->hasUrlContent())->toBeFalse();
    });

    it('decodes PDF content', function () {
        $pdfContent = 'PDF binary content';
        $data = new PrintWaybillData(
            orderId: 'ORDER-123',
            trackingNumber: null,
            base64Content: base64_encode($pdfContent),
            urlContent: null,
            isMultiParcel: false
        );

        expect($data->getPdfContent())->toBe($pdfContent);
    });

    it('returns null for PDF content when not available', function () {
        $data = new PrintWaybillData(
            orderId: 'ORDER-123',
            trackingNumber: null,
            base64Content: null,
            urlContent: 'https://example.com',
            isMultiParcel: true
        );

        expect($data->getPdfContent())->toBeNull();
    });

    it('calculates PDF size', function () {
        $pdfContent = str_repeat('X', 1024); // 1 KB
        $data = new PrintWaybillData(
            orderId: 'ORDER-123',
            trackingNumber: null,
            base64Content: base64_encode($pdfContent),
            urlContent: null,
            isMultiParcel: false
        );

        expect($data->getPdfSize())->toBe(1024);
    });

    it('validates PDF format', function () {
        $validPdf = new PrintWaybillData(
            orderId: 'ORDER-123',
            trackingNumber: null,
            base64Content: base64_encode('%PDF-1.4 content'),
            urlContent: null,
            isMultiParcel: false
        );

        $invalidPdf = new PrintWaybillData(
            orderId: 'ORDER-456',
            trackingNumber: null,
            base64Content: base64_encode('Not a PDF'),
            urlContent: null,
            isMultiParcel: false
        );

        expect($validPdf->isValidPdf())->toBeTrue()
            ->and($invalidPdf->isValidPdf())->toBeFalse();
    });

    it('formats file size in human-readable format', function () {
        $sizes = [
            512 => '512.00 B',
            1024 => '1.00 KB',
            1024 * 1024 => '1.00 MB',
            1024 * 1024 * 1024 => '1.00 GB',
        ];

        foreach ($sizes as $bytes => $expected) {
            $content = str_repeat('X', $bytes);
            $data = new PrintWaybillData(
                orderId: 'ORDER-123',
                trackingNumber: null,
                base64Content: base64_encode($content),
                urlContent: null,
                isMultiParcel: false
            );

            expect($data->getFormattedSize())->toBe($expected);
        }
    });

    it('returns null for formatted size when content not available', function () {
        $data = new PrintWaybillData(
            orderId: 'ORDER-123',
            trackingNumber: null,
            base64Content: null,
            urlContent: 'https://example.com',
            isMultiParcel: true
        );

        expect($data->getFormattedSize())->toBeNull();
    });

    it('gets download URL for multi-parcel', function () {
        $url = 'https://api.jnt.com/download/123.pdf';
        $data = new PrintWaybillData(
            orderId: 'ORDER-123',
            trackingNumber: null,
            base64Content: null,
            urlContent: $url,
            isMultiParcel: true
        );

        expect($data->getDownloadUrl())->toBe($url);
    });

    it('returns null download URL for single parcel', function () {
        $data = new PrintWaybillData(
            orderId: 'ORDER-123',
            trackingNumber: null,
            base64Content: 'content',
            urlContent: null,
            isMultiParcel: false
        );

        expect($data->getDownloadUrl())->toBeNull();
    });

    it('saves PDF to file', function () {
        $pdfContent = '%PDF-1.4 Test PDF content';
        $data = new PrintWaybillData(
            orderId: 'ORDER-123',
            trackingNumber: null,
            base64Content: base64_encode($pdfContent),
            urlContent: null,
            isMultiParcel: false
        );

        $tempFile = sys_get_temp_dir().'/test_waybill_'.uniqid().'.pdf';

        try {
            $result = $data->savePdf($tempFile);

            expect($result)->toBeTrue()
                ->and(file_exists($tempFile))->toBeTrue()
                ->and(file_get_contents($tempFile))->toBe($pdfContent);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    });

    it('creates directory when saving PDF', function () {
        $pdfContent = 'PDF content';
        $data = new PrintWaybillData(
            orderId: 'ORDER-123',
            trackingNumber: null,
            base64Content: base64_encode($pdfContent),
            urlContent: null,
            isMultiParcel: false
        );

        $tempDir = sys_get_temp_dir().'/jnt_test_'.uniqid();
        $tempFile = $tempDir.'/waybill.pdf';

        try {
            $result = $data->savePdf($tempFile);

            expect($result)->toBeTrue()
                ->and(is_dir($tempDir))->toBeTrue()
                ->and(file_exists($tempFile))->toBeTrue();
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            if (is_dir($tempDir)) {
                rmdir($tempDir);
            }
        }
    });

    it('returns false when saving PDF without content', function () {
        $data = new PrintWaybillData(
            orderId: 'ORDER-123',
            trackingNumber: null,
            base64Content: null,
            urlContent: 'https://example.com',
            isMultiParcel: true
        );

        expect($data->savePdf('/tmp/test.pdf'))->toBeFalse();
    });

    it('handles empty base64 content', function () {
        $data = PrintWaybillData::fromApiArray([
            'orderId' => 'ORDER-123',
            'base64EncodeContent' => '',
        ]);

        expect($data->hasBase64Content())->toBeFalse()
            ->and($data->getPdfContent())->toBeNull();
    });
});
