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

        // Mock transaction for putItems
        $this->mockDatabase->shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        // Mock for putItems
        $this->mockDatabase->shouldReceive('table')
            ->with('carts')
            ->twice() // once for checking current version, once for update
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
            ->with(['id', 'version'])
            ->once()
            ->andReturn(null); // No existing record

        $this->mockBuilder->shouldReceive('insert')
            ->with(Mockery::on(function ($data) {
                return isset($data['items']) &&
                       isset($data['updated_at']) &&
                       isset($data['created_at']) &&
                       isset($data['version']) &&
                       $data['version'] === 1 &&
                       json_decode($data['items'], true) === ['item1' => ['name' => 'Test Item', 'price' => 100]];
            }))
            ->once()
            ->andReturn(true);

        // Mock for getItems
        $record = (object) [
            'items' => json_encode($items),
            'conditions' => null,
            'metadata' => null,
        ];

        $this->mockDatabase->shouldReceive('table')
            ->with('carts')
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

        // Mock transaction for putConditions
        $this->mockDatabase->shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        // Mock for putConditions
        $this->mockDatabase->shouldReceive('table')
            ->with('carts')
            ->twice() // once for checking current version, once for update
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
            ->with(['id', 'version'])
            ->once()
            ->andReturn(null); // No existing record

        $this->mockBuilder->shouldReceive('insert')
            ->with(Mockery::on(function ($data) {
                return isset($data['conditions']) &&
                       isset($data['updated_at']) &&
                       isset($data['created_at']) &&
                       isset($data['version']) &&
                       $data['version'] === 1 &&
                       json_decode($data['conditions'], true) === ['discount' => ['type' => 'percentage', 'value' => 10]];
            }))
            ->once()
            ->andReturn(true);

        // Mock for getConditions
        $record = (object) [
            'items' => null,
            'conditions' => json_encode($conditions),
            'metadata' => null,
        ];

        $this->mockDatabase->shouldReceive('table')
            ->with('carts')
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

        // Mock transaction for putBoth
        $this->mockDatabase->shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->mockDatabase->shouldReceive('table')
            ->with('carts')
            ->twice() // once for checking current version, once for update
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
            ->with(['id', 'version'])
            ->once()
            ->andReturn(null); // No existing record

        $this->mockBuilder->shouldReceive('insert')
            ->with(Mockery::on(function ($data) {
                return isset($data['items']) &&
                       isset($data['conditions']) &&
                       isset($data['updated_at']) &&
                       isset($data['created_at']) &&
                       isset($data['version']) &&
                       $data['version'] === 1;
            }))
            ->once()
            ->andReturn(true);

        $storage->putBoth('user123', 'default', $items, $conditions);

        // Test passes if no exception was thrown
        expect(true)->toBeTrue();
    });

    it('returns empty array when items do not exist', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $this->mockDatabase->shouldReceive('table')
            ->with('carts')
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
            ->with('carts')
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
            ->with('carts')
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
            ->with('carts')
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
            ->with('carts')
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
            ->with('carts')
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
            ->with('carts')
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
            ->with('carts')
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
            ->with('carts')
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
            ->with('carts')
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
            ->with('carts')
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

        // Mock transaction for putMetadata
        $this->mockDatabase->shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        // Mock for putMetadata - getting existing metadata
        $this->mockDatabase->shouldReceive('table')
            ->with('carts')
            ->times(4) // once for first(), once for value(), once for insert(), one for getMetadata
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->times(3) // for first(), value(), and getMetadata() calls
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->times(3) // for first(), value(), and getMetadata() calls
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('first')
            ->with(['id', 'version'])
            ->once()
            ->andReturn(null); // No existing record

        $this->mockBuilder->shouldReceive('value')
            ->with('metadata')
            ->twice() // once for putMetadata, once for getMetadata
            ->andReturn(null, '{"test_key":"test_value"}');

        $this->mockBuilder->shouldReceive('insert')
            ->with(Mockery::on(function ($data) {
                return isset($data['metadata']) &&
                       isset($data['updated_at']) &&
                       isset($data['created_at']) &&
                       isset($data['version']) &&
                       $data['version'] === 1;
            }))
            ->once()
            ->andReturn(true);

        $storage->putMetadata('user123', 'default', 'test_key', $metadataValue);
        $result = $storage->getMetadata('user123', 'default', 'test_key');

        expect($result)->toBe($metadataValue);
    });

    it('can store metadata when existing metadata exists', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        // Mock transaction for putMetadata
        $this->mockDatabase->shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        // Mock for putMetadata - getting existing metadata
        $this->mockDatabase->shouldReceive('table')
            ->with('carts')
            ->times(3) // once for first(), once for value(), once for insert()
            ->andReturn($this->mockBuilder);

        $this->mockBuilder->shouldReceive('where')
            ->with('identifier', 'user123')
            ->twice()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('where')
            ->with('instance', 'default')
            ->twice()
            ->andReturnSelf();

        $this->mockBuilder->shouldReceive('first')
            ->with(['id', 'version'])
            ->once()
            ->andReturn(null); // No existing record

        $this->mockBuilder->shouldReceive('value')
            ->with('metadata')
            ->once()
            ->andReturn('{"existing_key":"existing_value"}');

        $this->mockBuilder->shouldReceive('insert')
            ->with(Mockery::on(function ($data) {
                $metadata = json_decode($data['metadata'], true);

                return $metadata['existing_key'] === 'existing_value' &&
                       $metadata['new_key'] === 'new_value' &&
                       isset($data['version']) &&
                       $data['version'] === 1;
            }))
            ->once()
            ->andReturn(true);

        $storage->putMetadata('user123', 'default', 'new_key', 'new_value');

        // Test passes if no exception was thrown
        expect(true)->toBeTrue();
    });

    it('returns null when metadata record does not exist', function () {
        $storage = new DatabaseStorage($this->mockDatabase);

        $this->mockDatabase->shouldReceive('table')
            ->with('carts')
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
            ->with('carts')
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
