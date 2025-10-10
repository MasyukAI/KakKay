<?php

declare(strict_types=1);

namespace MasyukAI\Cart;

use Illuminate\Contracts\Events\Dispatcher;
use MasyukAI\Cart\Contracts\RulesFactoryInterface;
use MasyukAI\Cart\Storage\StorageInterface;
use MasyukAI\Cart\Traits\CalculatesTotals;
use MasyukAI\Cart\Traits\ManagesConditions;
use MasyukAI\Cart\Traits\ManagesDynamicConditions;
use MasyukAI\Cart\Traits\ManagesIdentifier;
use MasyukAI\Cart\Traits\ManagesInstances;
use MasyukAI\Cart\Traits\ManagesItems;
use MasyukAI\Cart\Traits\ManagesMetadata;
use MasyukAI\Cart\Traits\ManagesStorage;

final class Cart
{
    use CalculatesTotals;
    use ManagesConditions;
    use ManagesDynamicConditions;
    use ManagesIdentifier;
    use ManagesInstances;
    use ManagesItems;
    use ManagesMetadata;
    use ManagesStorage;

    public function __construct(
        private StorageInterface $storage,
        private string $identifier,
        private ?Dispatcher $events = null,
        private string $instanceName = 'default',
        private bool $eventsEnabled = true
    ) {
        // Cart is now created when first item is added, not during instantiation
    }

    /**
     * Initialize cart with rules factory for dynamic condition persistence.
     *
     * This method sets up the rules factory and automatically restores
     * any previously persisted dynamic conditions.
     *
     * @param  RulesFactoryInterface  $factory  Factory to create rule closures
     */
    public function withRulesFactory(RulesFactoryInterface $factory): static
    {
        $this->setRulesFactory($factory);
        $this->restoreDynamicConditions();

        return $this;
    }

    /**
     * Get cart version for change tracking
     * Useful for detecting cart modifications and optimistic locking
     *
     * @return int|null Version number or null if not supported by storage driver
     */
    public function getVersion(): ?int
    {
        return $this->storage->getVersion($this->getIdentifier(), $this->instance());
    }

    /**
     * Get cart ID (primary key) from storage
     * Useful for linking carts to external systems like payment gateways, orders, etc.
     *
     * @return string|null Cart UUID or null if not supported by storage driver
     */
    public function getId(): ?string
    {
        return $this->storage->getId($this->getIdentifier(), $this->instance());
    }
}
