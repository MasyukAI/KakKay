<?php

declare(strict_types=1);

namespace MasyukAI\Shipping;

use Illuminate\Support\Manager;
use MasyukAI\Shipping\Providers\LocalShippingProvider;
use MasyukAI\Shipping\Contracts\ShippingProviderInterface;

class ShippingManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('shipping.default', 'local');
    }

    /**
     * Create the local driver.
     */
    protected function createLocalDriver(): ShippingProviderInterface
    {
        $config = $this->config->get('shipping.providers.local', []);
        
        return new LocalShippingProvider($config);
    }

    /**
     * Get available shipping methods from the current provider.
     */
    public function getShippingMethods(): array
    {
        return $this->driver()->getShippingMethods();
    }

    /**
     * Calculate shipping cost.
     */
    public function calculateCost(array $items, string $method, array $destination = []): int
    {
        return $this->driver()->calculateCost($items, $method, $destination);
    }

    /**
     * Get shipping quotes from all providers or a specific provider.
     */
    public function getQuotes(array $items, array $destination = [], ?string $provider = null): array
    {
        if ($provider) {
            return $this->driver($provider)->getQuotes($items, $destination);
        }

        $allQuotes = [];
        $providers = array_keys($this->config->get('shipping.providers', []));

        foreach ($providers as $providerName) {
            try {
                $quotes = $this->driver($providerName)->getQuotes($items, $destination);
                $allQuotes = array_merge($allQuotes, $quotes);
            } catch (\Exception $e) {
                // Log error but continue with other providers
                logger()->error("Failed to get quotes from provider {$providerName}: " . $e->getMessage());
            }
        }

        return $allQuotes;
    }

    /**
     * Create a shipment.
     */
    public function createShipment($shipment, ?string $provider = null): array
    {
        $providerName = $provider ?: $this->getDefaultDriver();
        
        return $this->driver($providerName)->createShipment($shipment);
    }

    /**
     * Get tracking information.
     */
    public function getTrackingInfo(string $trackingNumber, ?string $provider = null): array
    {
        $providerName = $provider ?: $this->getDefaultDriver();
        
        return $this->driver($providerName)->getTrackingInfo($trackingNumber);
    }

    /**
     * Check if a provider can ship to the given destination.
     */
    public function canShipTo(array $destination, ?string $provider = null): bool
    {
        $providerName = $provider ?: $this->getDefaultDriver();
        
        return $this->driver($providerName)->canShipTo($destination);
    }
}