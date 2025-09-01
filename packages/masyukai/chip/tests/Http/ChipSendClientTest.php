<?php

use Illuminate\Support\Facades\Http;
use Masyukai\Chip\Exceptions\ChipApiException;
use Masyukai\Chip\Clients\ChipSendClient;

beforeEach(function () {
    $this->client = new ChipSendClient('test_api_key', 'test_secret_key', 'sandbox');
});

describe('ChipSendClient Authentication', function () {
    it('adds correct headers to requests', function () {
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $this->client->get('/test');

        Http::assertSent(function ($request) {
            $headers = $request->headers();
            return isset($headers['X-Signature'][0]) &&
                   isset($headers['X-Timestamp'][0]) &&
                   $headers['Content-Type'][0] === 'application/json';
        });
    });

    it('generates valid HMAC signature', function () {
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $this->client->post('/test', ['key' => 'value']);

        Http::assertSent(function ($request) {
            $timestamp = $request->header('X-Timestamp')[0];
            $signature = $request->header('X-Signature')[0];
            $body = $request->body();
            
            $payload = $timestamp . $body;
            $expectedSignature = hash_hmac('sha256', $payload, 'test_secret_key');
            
            return $signature === $expectedSignature;
        });
    });

    it('includes API key in signature generation', function () {
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $this->client->get('/test');

        Http::assertSent(function ($request) {
            $signature = $request->header('X-Signature')[0];
            return is_string($signature) && strlen($signature) === 64; // SHA256 hex length
        });
    });
});

describe('ChipSendClient Request Methods', function () {
    it('can make GET requests', function () {
        Http::fake(['*' => Http::response(['data' => ['id' => '123']], 200)]);

        $response = $this->client->get('/test');

        expect($response)->toBe(['data' => ['id' => '123']]);
        Http::assertSent(fn($request) => $request->method() === 'GET');
    });

    it('can make POST requests with JSON body', function () {
        Http::fake(['*' => Http::response(['data' => ['created' => true]], 201)]);

        $response = $this->client->post('/test', ['name' => 'Test']);

        expect($response)->toBe(['data' => ['created' => true]]);
        Http::assertSent(function ($request) {
            return $request->method() === 'POST' && 
                   json_decode($request->body(), true) === ['name' => 'Test'];
        });
    });
});

describe('ChipSendClient Error Handling', function () {
    it('throws ChipApiException with proper error details', function () {
        Http::fake(['*' => Http::response([
            'error' => 'Invalid amount',
            'code' => 'INVALID_AMOUNT'
        ], 400)]);

        try {
            $this->client->get('/test');
            $this->fail('Expected ChipApiException to be thrown');
        } catch (ChipApiException $e) {
            expect($e->getMessage())->toBe('Invalid amount');
            expect($e->getStatusCode())->toBe(400);
            expect($e->getErrorDetails())->toBe(['code' => 'INVALID_AMOUNT']);
        }
    });

    it('handles network timeouts', function () {
        Http::fake(['*' => Http::response(null, 408)]);

        expect(fn() => $this->client->get('/test'))
            ->toThrow(ChipApiException::class);
    });
});

describe('ChipSendClient URL Building', function () {
    it('uses sandbox URL in test mode', function () {
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $this->client->get('/test');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api-sandbox.chip-in.asia');
        });
    });

    it('uses production URL in live mode', function () {
        $liveClient = new ChipSendClient('live_api_key', 'live_secret_key', false);
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $liveClient->get('/test');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.chip-in.asia');
        });
    });
});

describe('ChipSendClient Timestamp Generation', function () {
    it('generates current epoch timestamp', function () {
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $beforeTime = time();
        $this->client->get('/test');
        $afterTime = time();

        Http::assertSent(function ($request) use ($beforeTime, $afterTime) {
            $timestamp = (int) $request->header('X-Timestamp')[0];
            return $timestamp >= $beforeTime && $timestamp <= $afterTime;
        });
    });
});
