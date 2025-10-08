<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use MasyukAI\Jnt\Exceptions\JntException;
use MasyukAI\Jnt\Services\JntExpressService;

beforeEach(function () {
    $this->config = [
        'environment' => 'testing',
        'api_account' => 'test-account',
        'private_key' => 'test-private-key',
        'customer_code' => 'TESTCUST',
        'password' => 'test-password',
        'base_urls' => [
            'testing' => 'https://jtjms-api.jtexpress.my',
            'production' => 'https://jtjms-api.jtexpress.my',
        ],
        'http' => [
            'timeout' => 30,
            'connect_timeout' => 10,
            'retry_times' => 3,
            'retry_sleep' => 100, // Faster for tests
        ],
        'logging' => [
            'enabled' => false, // Disable for tests
        ],
    ];

    $this->service = new JntExpressService(
        $this->config['customer_code'],
        $this->config['password'],
        $this->config
    );
});

test('creates order successfully with Http facade', function () {
    Http::fake([
        '*/api/order/addOrder' => Http::response([
            'code' => '1',
            'msg' => 'Success',
            'data' => [
                'txlogisticId' => 'TXN-001',
                'billCode' => 'JT123456789',
                'sortingCode' => 'PJ01',
            ],
        ], 200),
    ]);

    $result = $this->service->createOrderFromArray([
        'txlogisticId' => 'TXN-001',
        'sender' => ['name' => 'John Doe', 'phone' => '60123456789'],
        'receiver' => ['name' => 'Jane Doe', 'phone' => '60987654321'],
        'items' => [['itemName' => 'Test Item', 'number' => '1']],
        'packageInfo' => ['weight' => '1'],
    ]);

    expect($result->billCode)->toBe('JT123456789')
        ->and($result->txlogisticId)->toBe('TXN-001');

    // Verify request was sent with correct headers
    Http::assertSent(function ($request) {
        return $request->hasHeader('apiAccount')
            && $request->hasHeader('digest')
            && $request->hasHeader('timestamp')
            && $request->url() === 'https://jtjms-api.jtexpress.my/api/order/addOrder';
    });
});

test('handles API errors correctly', function () {
    Http::fake([
        '*/api/order/addOrder' => Http::response([
            'code' => '0',
            'msg' => 'Invalid customer code',
            'data' => null,
        ], 200),
    ]);

    $this->service->createOrderFromArray([
        'txlogisticId' => 'TXN-001',
        'sender' => ['name' => 'John'],
        'receiver' => ['name' => 'Jane'],
        'items' => [['itemName' => 'Item']],
        'packageInfo' => ['weight' => '1'],
    ]);
})->throws(JntException::class, 'Invalid customer code');

test('handles connection errors', function () {
    Http::fake(function () {
        throw new Illuminate\Http\Client\ConnectionException('Connection timeout');
    });

    $this->service->createOrderFromArray([
        'txlogisticId' => 'TXN-001',
        'sender' => ['name' => 'John'],
        'receiver' => ['name' => 'Jane'],
        'items' => [['itemName' => 'Item']],
        'packageInfo' => ['weight' => '1'],
    ]);
})->throws(JntException::class, 'Connection failed');

test('retries on 5xx errors', function () {
    Http::fake([
        '*/api/order/addOrder' => Http::sequence()
            ->push(['code' => '0', 'msg' => 'Server error'], 500)
            ->push(['code' => '0', 'msg' => 'Server error'], 500)
            ->push(['code' => '1', 'msg' => 'Success', 'data' => ['txlogisticId' => 'TXN-001', 'billCode' => 'JT123']], 200),
    ]);

    $result = $this->service->createOrderFromArray([
        'txlogisticId' => 'TXN-001',
        'sender' => ['name' => 'John'],
        'receiver' => ['name' => 'Jane'],
        'items' => [['itemName' => 'Item']],
        'packageInfo' => ['weight' => '1'],
    ]);

    expect($result->billCode)->toBe('JT123');

    // Verify it was called 3 times (2 failures + 1 success)
    Http::assertSentCount(3);
});

test('handles HTTP errors', function () {
    Http::fake([
        '*/api/order/addOrder' => Http::response('Server Error', 503),
    ]);

    $this->service->createOrderFromArray([
        'txlogisticId' => 'TXN-001',
        'sender' => ['name' => 'John'],
        'receiver' => ['name' => 'Jane'],
        'items' => [['itemName' => 'Item']],
        'packageInfo' => ['weight' => '1'],
    ]);
})->throws(JntException::class, 'HTTP 503');

test('verifies request payload structure', function () {
    Http::fake([
        '*/api/order/addOrder' => Http::response([
            'code' => '1',
            'msg' => 'Success',
            'data' => ['txlogisticId' => 'TXN-001', 'billCode' => 'JT123'],
        ], 200),
    ]);

    $this->service->createOrderFromArray([
        'txlogisticId' => 'TXN-001',
        'sender' => ['name' => 'John'],
        'receiver' => ['name' => 'Jane'],
        'items' => [['itemName' => 'Item']],
        'packageInfo' => ['weight' => '1'],
    ]);

    Http::assertSent(function ($request) {
        $body = $request->data();

        // Verify it's form-encoded
        return isset($body['bizContent'])
            && is_string($body['bizContent'])
            && json_decode($body['bizContent']) !== null;
    });
});

test('creates order with data objects', function () {
    Http::fake([
        '*/api/order/addOrder' => Http::response([
            'code' => '1',
            'msg' => 'Success',
            'data' => [
                'txlogisticId' => 'TXN-002',
                'billCode' => 'JT987654321',
                'sortingCode' => 'KL01',
            ],
        ], 200),
    ]);

    $sender = new MasyukAI\Jnt\Data\AddressData(
        name: 'John Doe',
        phone: '60123456789',
        address: '123 Test Street',
        postCode: '47300',
        countryCode: 'MYS',
        prov: 'Selangor',
        city: 'Petaling Jaya',
        area: 'SS2'
    );

    $receiver = new MasyukAI\Jnt\Data\AddressData(
        name: 'Jane Doe',
        phone: '60987654321',
        address: '456 Test Avenue',
        postCode: '50000',
        countryCode: 'MYS',
        prov: 'Kuala Lumpur',
        city: 'KL',
        area: 'Bukit Bintang'
    );

    $item = new MasyukAI\Jnt\Data\ItemData(
        itemName: 'Test Product',
        number: '1',
        weight: '1.5',
        itemValue: '100.00'
    );

    $packageInfo = new MasyukAI\Jnt\Data\PackageInfoData(
        packageQuantity: '1',
        weight: '1.5',
        packageValue: '100.00',
        goodsType: 'General'
    );

    $result = $this->service->createOrder(
        sender: $sender,
        receiver: $receiver,
        items: [$item],
        packageInfo: $packageInfo,
        txlogisticId: 'TXN-002'
    );

    expect($result->billCode)->toBe('JT987654321')
        ->and($result->txlogisticId)->toBe('TXN-002');

    Http::assertSent(function ($request) {
        $body = json_decode($request->data()['bizContent'], true);

        return $body['sender']['name'] === 'John Doe'
            && $body['receiver']['name'] === 'Jane Doe'
            && $body['items'][0]['itemName'] === 'Test Product';
    });
});

test('uses builder pattern', function () {
    Http::fake([
        '*/api/order/addOrder' => Http::response([
            'code' => '1',
            'msg' => 'Success',
            'data' => ['txlogisticId' => 'TXN-BUILDER', 'billCode' => 'JT555'],
        ], 200),
    ]);

    $sender = new MasyukAI\Jnt\Data\AddressData(
        name: 'Builder Sender',
        phone: '60111111111',
        address: 'Builder Address',
        postCode: '40000',
        countryCode: 'MYS',
        prov: 'Selangor',
        city: 'Shah Alam',
        area: 'Sec 13'
    );

    $receiver = new MasyukAI\Jnt\Data\AddressData(
        name: 'Builder Receiver',
        phone: '60222222222',
        address: 'Receiver Address',
        postCode: '80000',
        countryCode: 'MYS',
        prov: 'Johor',
        city: 'Johor Bahru',
        area: 'JB City'
    );

    $item = new MasyukAI\Jnt\Data\ItemData(
        itemName: 'Builder Item',
        number: '5',
        weight: '2.5',
        itemValue: '250.00'
    );

    $packageInfo = new MasyukAI\Jnt\Data\PackageInfoData(
        packageQuantity: '1',
        weight: '2.5',
        packageValue: '250.00',
        goodsType: 'Electronics'
    );

    $order = $this->service->createOrderBuilder()
        ->txlogisticId('TXN-BUILDER')
        ->sender($sender)
        ->receiver($receiver)
        ->addItem($item)
        ->packageInfo($packageInfo)
        ->build();

    $result = $this->service->createOrderFromArray($order);

    expect($result->billCode)->toBe('JT555');

    Http::assertSent(function ($request) {
        $body = json_decode($request->data()['bizContent'], true);

        return $body['txlogisticId'] === 'TXN-BUILDER'
            && $body['sender']['name'] === 'Builder Sender';
    });
});
