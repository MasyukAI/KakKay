<?php

declare(strict_types=1);

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use MasyukAI\Cart\Storage\DatabaseStorage;

describe('DatabaseStorage Coverage Tests', function () {
    beforeEach(function () {
        $this->mockDatabase = Mockery::mock(ConnectionInterface::class);
        $this->mockBuilder = Mockery::mock(Builder::class);
    });

    afterEach(function () {
        Mockery::close();
    });

    it('can be instantiated with default table name', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        expect($storage)->toBeInstanceOf(DatabaseStorage::class);
    });

    it('can be instantiated with custom table name', function () {
        $storage = new DatabaseStorage($this->mockDatabase, 'custom_cart_table');

        expect($storage)->toBeInstanceOf(DatabaseStorage::class);
    });

    it('can store and retrieve items', function () {
        $storage = new DatabaseStorage($this->mockDatabase);
        $items = ['item1' => ['name' => 'Test Item', 'price' => 100]];

        // Mock for putItems
        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('updateOrInsert')
            ->with(
                ['identifier' => 'user123', 'instance' => 'default'],
                Mockery::on(function ($data) {
                    return isset($data['items']) &&
                           isset($data['updated_at']) &&
                           isset($data['created_at']) &&
                           json_decode($data['items'], true) === ['item1' => ['name' => 'Test Item', 'price' => 100]];
                })
            )
            ->once()
            ->andReturn(true);

        // Mock for getItems
        $record = (object) [
            'items' => json_encode($items),
            'conditions' => null,
            'metadata' => null,
        ];

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('first')
            ->once()
            ->andReturn($record);

        $storage->putItems('user123', 'default', $items);
        $result = $storage->getItems('user123', 'default');

        expect($result)->toBe($items);
    });

    it('can store and retrieve conditions', function () {
        $storage = new DatabaseStorage($this->mockDatabase);
        $conditions = ['discount' => ['type' => 'percentage', 'value' => 10]];

        // Mock for putConditions
        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('updateOrInsert')
            ->with(
                ['identifier' => 'user123', 'instance' => 'default'],
                Mockery::on(function ($data) {
                    return isset($data['conditions']) &&
                           isset($data['updated_at']) &&
                           isset($data['created_at']) &&
                           json_decode($data['conditions'], true) === ['discount' => ['type' => 'percentage', 'value' => 10]];
                })
            )
            ->once()
            ->andReturn(true);

        // Mock for getConditions
        $record = (object) [
            'items' => null,
            'conditions' => json_encode($conditions),
            'metadata' => null,
        ];

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('first')
            ->once()
            ->andReturn($record);

        $storage->putConditions('user123', 'default', $conditions);
        $result = $storage->getConditions('user123', 'default');

        expect($result)->toBe($conditions);
    });

    it('can store both items and conditions at once', function () {
        $storage = new DatabaseStorage($this->mockDatabase);
        $items = ['item1' => ['name' => 'Test']];
        $conditions = ['shipping' => ['value' => 5]];

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('updateOrInsert')
            ->with(
                ['identifier' => 'user123', 'instance' => 'default'],
                Mockery::on(function ($data) {
                    return isset($data['items']) &&
                           isset($data['conditions']) &&
                           isset($data['updated_at']) &&
                           isset($data['created_at']);
                })
            )
            ->once()
            ->andReturn(true);

        $storage->putBoth('user123', 'default', $items, $conditions);

        // Test passes if no exception was thrown
        expect(true)->toBeTrue();
    });

    it('returns empty array when items do not exist', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $result = $storage->getItems('user123', 'default');

        expect($result)->toBe([]);
    });

    it('returns empty array when conditions do not exist', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $result = $storage->getConditions('user123', 'default');

        expect($result)->toBe([]);
    });

    it('returns empty array when record exists but items is null', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $record = (object) [
            'items' => null,
            'conditions' => null,
            'metadata' => null,
        ];

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('first')
            ->once()
            ->andReturn($record);

        $result = $storage->getItems('user123', 'default');

        expect($result)->toBe([]);
    });

    it('returns empty array when record exists but conditions is null', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $record = (object) [
            'items' => null,
            'conditions' => null,
            'metadata' => null,
        ];

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('first')
            ->once()
            ->andReturn($record);

        $result = $storage->getConditions('user123', 'default');

        expect($result)->toBe([]);
    });

    it('handles invalid JSON gracefully for items', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $record = (object) [
            'items' => '{"invalid":json}',
            'conditions' => null,
            'metadata' => null,
        ];

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('first')
            ->once()
            ->andReturn($record);

        $result = $storage->getItems('user123', 'default');

        expect($result)->toBe([]);
    });

    it('handles invalid JSON gracefully for conditions', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $record = (object) [
            'items' => null,
            'conditions' => '{"invalid":json}',
            'metadata' => null,
        ];

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('first')
            ->once()
            ->andReturn($record);

        $result = $storage->getConditions('user123', 'default');

        expect($result)->toBe([]);
    });

    it('can check if cart exists in storage', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('exists')
            ->once()
            ->andReturn(true);

        $result = $storage->has('user123', 'default');

        expect($result)->toBeTrue();
    });

    it('can remove cart from storage', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('delete')
            ->once()
            ->andReturn(1);

        $storage->forget('user123', 'default');

        // Test passes if no exception was thrown
        expect(true)->toBeTrue();
    });

    it('can flush all carts from storage', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('truncate')
            ->once()
            ->andReturn(true);

        $storage->flush();

        // Test passes if no exception was thrown
        expect(true)->toBeTrue();
    });

    it('can get instances for a specific identifier', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $mockCollection = Mockery::mock();
        $mockCollection->shouldReceive('toArray')
            ->once()
            ->andReturn(['default', 'wishlist']);

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('pluck')
            ->with('instance')
            ->once()
            ->andReturn($mockCollection);

        $result = $storage->getInstances('user123');

        expect($result)->toBe(['default', 'wishlist']);
    });

    it('can remove all instances for a specific identifier', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('delete')
            ->once()
            ->andReturn(2);

        $storage->forgetIdentifier('user123');

        // Test passes if no exception was thrown
        expect(true)->toBeTrue();
    });

    it('can store and retrieve metadata', function () {
        $storage = new DatabaseStorage($this->mockDatabase);
        $metadataValue = 'test_value';

        // Mock for putMetadata - getting existing metadata
        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('value')
            ->with('metadata')
            ->once()
            ->andReturn(null);

        // Mock for putMetadata - updateOrInsert
        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('updateOrInsert')
            ->with(
                ['identifier' => 'user123', 'instance' => 'default'],
                Mockery::on(function ($data) {
                    return isset($data['metadata']) &&
                           isset($data['updated_at']) &&
                           isset($data['created_at']);
                })
            )
            ->once()
            ->andReturn(true);

        // Mock for getMetadata
        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('value')
            ->with('metadata')
            ->once()
            ->andReturn('{"test_key":"test_value"}');

        $storage->putMetadata('user123', 'default', 'test_key', $metadataValue);
        $result = $storage->getMetadata('user123', 'default', 'test_key');

        expect($result)->toBe($metadataValue);
    });

    it('can store metadata when existing metadata exists', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        // Mock for putMetadata - getting existing metadata
        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('value')
            ->with('metadata')
            ->once()
            ->andReturn('{"existing_key":"existing_value"}');

        // Mock for putMetadata - updateOrInsert
        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('updateOrInsert')
            ->with(
                ['identifier' => 'user123', 'instance' => 'default'],
                Mockery::on(function ($data) {
                    $metadata = json_decode($data['metadata'], true);

                    return $metadata['existing_key'] === 'existing_value' &&
                           $metadata['new_key'] === 'new_value';
                })
            )
            ->once()
            ->andReturn(true);

        $storage->putMetadata('user123', 'default', 'new_key', 'new_value');

        // Test passes if no exception was thrown
        expect(true)->toBeTrue();
    });

    it('returns null when metadata record does not exist', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('value')
            ->with('metadata')
            ->once()
            ->andReturn(null);

        $result = $storage->getMetadata('user123', 'default', 'nonexistent_key');

        expect($result)->toBeNull();
    });

    it('returns null when metadata key does not exist', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $this->mockDatabase->shouldReceive('table')
            ->with('cart_storage')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('value')
            ->with('metadata')
            ->once()
            ->andReturn('{"existing_key":"existing_value"}');

        $result = $storage->getMetadata('user123', 'default', 'nonexistent_key');

        expect($result)->toBeNull();
    });

    it('uses custom table name correctly', function () {
        $storage = new DatabaseStorage($this->mockDatabase, 'custom_cart_table');

        $this->mockDatabase->shouldReceive('table')
            ->with('custom_cart_table')
            ->once()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->once()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $result = $storage->has('user123', 'default');

        expect($result)->toBeFalse();
    });
});
