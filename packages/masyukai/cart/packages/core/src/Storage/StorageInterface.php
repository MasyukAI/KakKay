<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Storage;

interface StorageInterface
{
    /**
     * Check if cart exists in storage
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     */
    public function has(string $identifier, string $instance): bool;

    /**
     * Remove cart from storage
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     */
    public function forget(string $identifier, string $instance): void;

    /**
     * Clear all carts from storage
     */
    public function flush(): void;

    /**
     * Get all instances for a specific identifier
     *
     * @param  string  $identifier  User/session identifier
     * @return array Array of instance names
     */
    public function getInstances(string $identifier): array;

    /**
     * Remove all instances for a specific identifier
     *
     * @param  string  $identifier  User/session identifier
     */
    public function forgetIdentifier(string $identifier): void;

    /**
     * Retrieve cart items from storage
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @return array Cart items array
     */
    public function getItems(string $identifier, string $instance): array;

    /**
     * Retrieve cart conditions from storage
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @return array Cart conditions array
     */
    public function getConditions(string $identifier, string $instance): array;

    /**
     * Store cart items in storage
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @param  array  $items  Cart items array
     */
    public function putItems(string $identifier, string $instance, array $items): void;

    /**
     * Store cart conditions in storage
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @param  array  $conditions  Cart conditions array
     */
    public function putConditions(string $identifier, string $instance, array $conditions): void;

    /**
     * Store both items and conditions in storage
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @param  array  $items  Cart items array
     * @param  array  $conditions  Cart conditions array
     */
    public function putBoth(string $identifier, string $instance, array $items, array $conditions): void;

    /**
     * Store cart metadata
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @param  string  $key  Metadata key
     * @param  mixed  $value  Metadata value
     */
    public function putMetadata(string $identifier, string $instance, string $key, mixed $value): void;

    /**
     * Retrieve cart metadata
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @param  string  $key  Metadata key
     * @return mixed Metadata value or null if not found
     */
    public function getMetadata(string $identifier, string $instance, string $key): mixed;
}
