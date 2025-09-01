<?php

declare(strict_types=1);

namespace MasyukAI\Cart;

use Illuminate\Contracts\Events\Dispatcher;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Storage\StorageInterface;
use MasyukAI\Cart\Traits\CalculatesTotals;
use MasyukAI\Cart\Traits\ManagesConditions;
use MasyukAI\Cart\Traits\ManagesIdentifier;
use MasyukAI\Cart\Traits\ManagesInstances;
use MasyukAI\Cart\Traits\ManagesItems;
use MasyukAI\Cart\Traits\ManagesStorage;

readonly class Cart
{
    use CalculatesTotals;
    use ManagesConditions;
    use ManagesIdentifier;
    use ManagesInstances;
    use ManagesItems;
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
}
