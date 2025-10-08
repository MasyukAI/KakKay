<?php

declare(strict_types=1);

use MasyukAI\Jnt\Builders\OrderBuilder;
use MasyukAI\Jnt\Data\AddressData;
use MasyukAI\Jnt\Data\ItemData;
use MasyukAI\Jnt\Data\PackageInfoData;
use MasyukAI\Jnt\Exceptions\JntException;

it('builds a valid order payload', function (): void {
    $sender = new AddressData(
        name: 'John Sender',
        phone: '60123456789',
        address: 'No 32, Jalan Kempas 4',
        postCode: '81930',
        countryCode: 'MYS',
        prov: 'Johor',
        city: 'Bandar Penawar',
        area: 'Taman Desaru Utama'
    );

    $receiver = new AddressData(
        name: 'Jane Receiver',
        phone: '60987654321',
        address: '4678, Laluan Sentang 35',
        postCode: '31000',
        countryCode: 'MYS',
        prov: 'Perak',
        city: 'Batu Gajah',
        area: 'Kampung Seri Mariah'
    );

    $item = new ItemData(
        itemName: 'Basketball',
        number: '2',
        weight: '10',
        itemValue: '50.00'
    );

    $packageInfo = new PackageInfoData(
        packageQuantity: '1',
        weight: '10',
        packageValue: '50',
        goodsType: 'ITN8'
    );

    $builder = new OrderBuilder('ITTEST0001', '9C75439FB1FD01EB01861670DD1B949C');
    $payload = $builder
        ->txlogisticId('TEST-123456')
        ->sender($sender)
        ->receiver($receiver)
        ->addItem($item)
        ->packageInfo($packageInfo)
        ->build();

    expect($payload)->toBeArray()
        ->and($payload['customerCode'])->toBe('ITTEST0001')
        ->and($payload['txlogisticId'])->toBe('TEST-123456')
        ->and($payload['actionType'])->toBe('add')
        ->and($payload['sender'])->toBeArray()
        ->and($payload['receiver'])->toBeArray()
        ->and($payload['items'])->toHaveCount(1)
        ->and($payload['packageInfo'])->toBeArray();
});

it('throws exception when txlogisticId is missing', function (): void {
    $builder = new OrderBuilder('ITTEST0001', '9C75439FB1FD01EB01861670DD1B949C');
    $builder->build();
})->throws(JntException::class, 'txlogisticId is required');

it('throws exception when sender is missing', function (): void {
    $builder = new OrderBuilder('ITTEST0001', '9C75439FB1FD01EB01861670DD1B949C');
    $builder->txlogisticId('TEST-123456')->build();
})->throws(JntException::class, 'Sender address is required');

it('throws exception when items are empty', function (): void {
    $sender = new AddressData(
        name: 'John Sender',
        phone: '60123456789',
        address: 'No 32, Jalan Kempas 4',
        postCode: '81930'
    );

    $receiver = new AddressData(
        name: 'Jane Receiver',
        phone: '60987654321',
        address: '4678, Laluan Sentang 35',
        postCode: '31000'
    );

    $builder = new OrderBuilder('ITTEST0001', '9C75439FB1FD01EB01861670DD1B949C');
    $builder
        ->txlogisticId('TEST-123456')
        ->sender($sender)
        ->receiver($receiver)
        ->build();
})->throws(JntException::class, 'At least one item is required');
