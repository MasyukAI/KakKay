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
     * @return array<string> Array of instance names
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
     * @return array<string, mixed> Cart items array
     */
    public function getItems(string $identifier, string $instance): array;

    /**
     * Retrieve cart conditions from storage
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @return array<string, mixed> Cart conditions array
     */
    public function getConditions(string $identifier, string $instance): array;

    /**
     * Store cart items in storage
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @param  array<string, mixed>  $items  Cart items array
     */
    public function putItems(string $identifier, string $instance, array $items): void;

    /**
     * Store cart conditions in storage
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @param  array<string, mixed>  $conditions  Cart conditions array
     */
    public function putConditions(string $identifier, string $instance, array $conditions): void;

    /**
     * Store both items and conditions in storage
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @param  array<string, mixed>  $items  Cart items array
     * @param  array<string, mixed>  $conditions  Cart conditions array
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

    /**
     * Get cart version for change tracking
     * Returns the version number used for optimistic locking and change detection
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @return int|null Version number or null if cart doesn't exist
     */
    public function getVersion(string $identifier, string $instance): ?int;

    /**
     * Get cart ID (primary key) from storage
     * Useful for linking carts to external systems (payment gateways, orders, etc.)
     *
     * @param  string  $identifier  User/session identifier
     * @param  string  $instance  Cart instance name
     * @return string|null Cart UUID or null if cart doesn't exist
     */
    public function getId(string $identifier, string $instance): ?string;

    /**
     * Swap cart identifier to transfer cart ownership.
     * This changes cart ownership from old identifier to new identifier by updating the identifier column.
     * The objective is to ensure the new identifier has an active cart, preventing cart abandonment.
     *
     * @param  string  $oldIdentifier  The old identifier (e.g., guest session)
     * @param  string  $newIdentifier  The new identifier (e.g., user ID)
     * @param  string  $instance  Cart instance name
     * @return bool True if swap was successful (new identifier now has the cart)
     */
    public function swapIdentifier(string $oldIdentifier, string $newIdentifier, string $instance): bool;
}
