<?php

declare(strict_types=1);

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use MasyukAI\Cart\Storage\DatabaseStorage;

beforeEach(function () {
    $this->database = Mockery::mock(ConnectionInterface::class);
    $this->queryBuilder = Mockery::mock(Builder::class);
    $this->storage = new DatabaseStorage($this->database, 'carts');
});

afterEach(function () {
    Mockery::close();
});

it('applies lock for update when config is enabled', function () {
    config(['cart.database.lock_for_update' => true]);

    $this->database->shouldReceive('table')
        ->with('carts')
        ->andReturn($this->queryBuilder);

    $this->queryBuilder->shouldReceive('where')
        ->with('identifier', 'test-cart')
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('where')
        ->with('instance', 'default')
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('lockForUpdate')
        ->once()
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('first')
        ->with(['id', 'version'])
        ->andReturn(null);

    $this->database->shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $this->database->shouldReceive('table')
        ->with('carts')
        ->andReturn($this->queryBuilder);

    $this->queryBuilder->shouldReceive('insert')
        ->once()
        ->andReturn(true);

    $this->storage->putItems('test-cart', 'default', ['test' => 'data']);
});

it('does not apply lock for update when config is disabled', function () {
    config(['cart.database.lock_for_update' => false]);

    $this->database->shouldReceive('table')
        ->with('carts')
        ->andReturn($this->queryBuilder);

    $this->queryBuilder->shouldReceive('where')
        ->with('identifier', 'test-cart')
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('where')
        ->with('instance', 'default')
        ->andReturnSelf();

    $this->queryBuilder->shouldNotReceive('lockForUpdate');

    $this->queryBuilder->shouldReceive('first')
        ->with(['id', 'version'])
        ->andReturn(null);

    $this->database->shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $this->database->shouldReceive('table')
        ->with('carts')
        ->andReturn($this->queryBuilder);

    $this->queryBuilder->shouldReceive('insert')
        ->once()
        ->andReturn(true);

    $this->storage->putItems('test-cart', 'default', ['test' => 'data']);
});

it('applies lock for update on putConditions when enabled', function () {
    config(['cart.database.lock_for_update' => true]);

    $this->database->shouldReceive('table')
        ->with('carts')
        ->andReturn($this->queryBuilder);

    $this->queryBuilder->shouldReceive('where')
        ->with('identifier', 'test-cart')
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('where')
        ->with('instance', 'default')
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('lockForUpdate')
        ->once()
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('first')
        ->with(['id', 'version'])
        ->andReturn(null);

    $this->database->shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $this->database->shouldReceive('table')
        ->with('carts')
        ->andReturn($this->queryBuilder);

    $this->queryBuilder->shouldReceive('insert')
        ->once()
        ->andReturn(true);

    $this->storage->putConditions('test-cart', 'default', ['test' => 'condition']);
});

it('applies lock for update on putBoth when enabled', function () {
    config(['cart.database.lock_for_update' => true]);

    $this->database->shouldReceive('table')
        ->with('carts')
        ->andReturn($this->queryBuilder);

    $this->queryBuilder->shouldReceive('where')
        ->with('identifier', 'test-cart')
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('where')
        ->with('instance', 'default')
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('lockForUpdate')
        ->once()
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('first')
        ->with(['id', 'version'])
        ->andReturn(null);

    $this->database->shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $this->database->shouldReceive('table')
        ->with('carts')
        ->andReturn($this->queryBuilder);

    $this->queryBuilder->shouldReceive('insert')
        ->once()
        ->andReturn(true);

    $this->storage->putBoth('test-cart', 'default', ['test' => 'item'], ['test' => 'condition']);
});

it('applies lock for update on putMetadata when enabled', function () {
    config(['cart.database.lock_for_update' => true]);

    // Mock the first table call for checking existing metadata
    $this->database->shouldReceive('table')
        ->with('carts')
        ->andReturn($this->queryBuilder);

    $this->queryBuilder->shouldReceive('where')
        ->with('identifier', 'test-cart')
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('where')
        ->with('instance', 'default')
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('value')
        ->with('metadata')
        ->andReturn(null);

    // Mock the second table call for the update operation with lock
    $this->database->shouldReceive('table')
        ->with('carts')
        ->andReturn($this->queryBuilder);

    $this->queryBuilder->shouldReceive('where')
        ->with('identifier', 'test-cart')
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('where')
        ->with('instance', 'default')
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('lockForUpdate')
        ->once()
        ->andReturnSelf();

    $this->queryBuilder->shouldReceive('first')
        ->with(['id', 'version'])
        ->andReturn(null);

    $this->database->shouldReceive('transaction')
        ->twice()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $this->database->shouldReceive('table')
        ->with('carts')
        ->andReturn($this->queryBuilder);

    $this->queryBuilder->shouldReceive('insert')
        ->once()
        ->andReturn(true);

    $this->storage->putMetadata('test-cart', 'default', 'test-key', 'test-value');
});
