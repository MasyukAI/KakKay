<?php

declare(strict_types=1);

namespace MasyukAI\Cart;

use Illuminate\Contracts\Events\Dispatcher;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Storage\StorageInterface;
use MasyukAI\Cart\Traits\CalculatesTotals;
use MasyukAI\Cart\Traits\ManagesConditions;
use MasyukAI\Cart\Traits\ManagesDynamicConditions;
use MasyukAI\Cart\Traits\ManagesIdentifier;
use MasyukAI\Cart\Traits\ManagesInstances;
use MasyukAI\Cart\Traits\ManagesItems;
use MasyukAI\Cart\Traits\ManagesMetadata;
use MasyukAI\Cart\Traits\ManagesPricing;
use MasyukAI\Cart\Traits\ManagesStorage;

class Cart
{
    use CalculatesTotals;
    use ManagesConditions;
    use ManagesDynamicConditions;
    use ManagesIdentifier;
    use ManagesInstances;
    use ManagesItems;
    use ManagesMetadata;
    use ManagesPricing;
    use ManagesStorage;

    public function __construct(
        private StorageInterface $storage,
        private ?Dispatcher $events = null,
        private string $instanceName = 'default',
        private bool $eventsEnabled = true,
        private array $config = []
    ) {
        if ($this->eventsEnabled && $this->events) {
            $this->events->dispatch(new CartCreated($this));
        }
    }

    /**
     * Associate model with the last added item (laravel-shopping-cart compatibility)
     * This is typically called after add() to associate the last added item
     */
    public function associate(string $model): static
    {
        $lastAddedItemId = $this->getLastAddedItemId();

        if ($lastAddedItemId === null) {
            throw new \InvalidArgumentException('No item has been added to associate with. Call add() first.');
        }

        $cartItems = $this->getItems();
        if (! $cartItems->has($lastAddedItemId)) {
            throw new \InvalidArgumentException('Last added item not found in cart.');
        }

        $item = $cartItems->get($lastAddedItemId);
        $updatedItem = new \MasyukAI\Cart\Models\CartItem(
            $item->id,
            $item->name,
            $item->price,
            $item->quantity,
            $item->attributes->toArray(),
            $item->conditions->toArray(),
            $model
        );

        $cartItems->put($lastAddedItemId, $updatedItem);

        // Make sure to save the updated collection
        $itemsArray = $cartItems->toArray();
        $this->storage->putItems($this->getIdentifier(), $this->getStorageInstanceName(), $itemsArray);

        return $this;
    }

    /**
     * Get the last added item ID
     */
    public function getLastAddedItemId(): ?string
    {
        return $this->storage->getMetadata($this->getIdentifier(), $this->getStorageInstanceName(), 'last_added_item_id');
    }

    /**
     * Set the last added item ID
     */
    public function setLastAddedItemId(string $itemId): void
    {
        $this->storage->putMetadata($this->getIdentifier(), $this->getStorageInstanceName(), 'last_added_item_id', $itemId);
    }
}
