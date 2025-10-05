<?php

declare(strict_types=1);

namespace MasyukAI\Cart;

use Illuminate\Contracts\Events\Dispatcher;
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
}
