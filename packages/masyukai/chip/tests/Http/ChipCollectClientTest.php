<?php

use Illuminate\Support\Facades\Http;
use Masyukai\Chip\Exceptions\ChipApiException;
use Masyukai\Chip\Clients\ChipCollectClient;

beforeEach(function () {
    $this->client = new ChipCollectClient(
        apiKey: 'test_api_key',
        brandId: 'test_brand_id',
        environment: 'sandbox',
        baseUrl: 'https://gate-sandbox.chip-in.asia/api/v1',
        timeout: 30,
        retryConfig: ['times' => 3, 'sleep' => 1000]
    );
});

describe('ChipCollectClient Authentication', function () {
    it('adds bearer token to requests', function () {
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $this->client->get('/test');

        Http::assertSent(function ($request) {
            return $request->header('Authorization')[0] === 'Bearer test_api_key';
        });
    });

    it('sets correct content type', function () {
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $this->client->post('/test', ['key' => 'value']);

        Http::assertSent(function ($request) {
            return $request->header('Content-Type')[0] === 'application/json';
        });
    });
});

describe('ChipCollectClient Request Methods', function () {
    it('can make GET requests', function () {
        Http::fake(['*' => Http::response(['data' => ['id' => '123']], 200)]);

        $response = $this->client->get('/test');

        expect($response)->toBe(['data' => ['id' => '123']]);
        Http::assertSent(fn($request) => $request->method() === 'GET');
    });

    it('can make POST requests', function () {
        Http::fake(['*' => Http::response(['data' => ['created' => true]], 201)]);

        $response = $this->client->post('/test', ['name' => 'Test']);

        expect($response)->toBe(['data' => ['created' => true]]);
        Http::assertSent(fn($request) => $request->method() === 'POST');
    });

    it('can make PUT requests', function () {
        Http::fake(['*' => Http::response(['data' => ['updated' => true]], 200)]);

        $response = $this->client->put('/test', ['name' => 'Updated']);

        expect($response)->toBe(['data' => ['updated' => true]]);
        Http::assertSent(fn($request) => $request->method() === 'PUT');
    });

    it('can make DELETE requests', function () {
        Http::fake(['*' => Http::response([], 204)]);

        $response = $this->client->delete('/test');

        expect($response)->toBe([]);
        Http::assertSent(fn($request) => $request->method() === 'DELETE');
    });
});

describe('ChipCollectClient Error Handling', function () {
    it('throws ChipApiException on 400 error', function () {
        Http::fake(['*' => Http::response(['error' => 'Bad Request'], 400)]);

        expect(fn() => $this->client->get('/test'))
            ->toThrow(ChipApiException::class, 'Bad Request');
    });

    it('throws ChipApiException on 401 error', function () {
        Http::fake(['*' => Http::response(['error' => 'Unauthorized'], 401)]);

        expect(fn() => $this->client->get('/test'))
            ->toThrow(ChipApiException::class, 'Unauthorized');
    });

    it('throws ChipApiException on 404 error', function () {
        Http::fake(['*' => Http::response(['error' => 'Not Found'], 404)]);

        expect(fn() => $this->client->get('/test'))
            ->toThrow(ChipApiException::class, 'Not Found');
    });

    it('throws ChipApiException on 500 error', function () {
        Http::fake(['*' => Http::response(['error' => 'Internal Server Error'], 500)]);

        expect(fn() => $this->client->get('/test'))
            ->toThrow(ChipApiException::class, 'Internal Server Error');
    });

    it('includes error details in exception', function () {
        Http::fake(['*' => Http::response([
            'error' => 'Validation failed',
            'details' => ['field' => 'required']
        ], 422)]);

        try {
            $this->client->get('/test');
        } catch (ChipApiException $e) {
            expect($e->getMessage())->toBe('Validation failed');
            expect($e->getErrorDetails())->toBe(['field' => 'required']);
            expect($e->getStatusCode())->toBe(422);
        }
    });
});

describe('ChipCollectClient Retry Logic', function () {
    it('retries on network failures', function () {
        Http::fake([
            '*' => Http::sequence()
                ->push(null, 500)
                ->push(null, 500)
                ->push(['data' => ['success' => true]], 200)
        ]);

        $response = $this->client->get('/test');

        expect($response)->toBe(['data' => ['success' => true]]);
    });

    it('gives up after max retries', function () {
        Http::fake(['*' => Http::response(null, 500)]);

        expect(fn() => $this->client->get('/test'))
            ->toThrow(ChipApiException::class);
    });
});

describe('ChipCollectClient URL Building', function () {
    it('uses sandbox URL in test mode', function () {
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $this->client->get('/test');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'gate-sandbox.chip-in.asia');
        });
    });

    it('uses production URL in live mode', function () {
        $liveClient = new ChipCollectClient(
            apiKey: 'live_api_key',
            brandId: 'live_brand_id', 
            environment: 'production'
        );
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $liveClient->get('/test');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'gate.chip-in.asia');
        });
    });
});
