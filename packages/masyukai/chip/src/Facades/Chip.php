<?php

declare(strict_types=1);

namespace MasyukAI\Chip\Facades;

use Illuminate\Support\Facades\Facade;
use MasyukAI\Chip\Services\ChipCollectService;

/**
 * @method static \MasyukAI\Chip\DataObjects\Purchase createPurchase(array<string, mixed> $data)
 * @method static \MasyukAI\Chip\DataObjects\Purchase getPurchase(string $id)
 * @method static \MasyukAI\Chip\DataObjects\Purchase cancelPurchase(string $id)
 * @method static \MasyukAI\Chip\DataObjects\Purchase capturePurchase(string $id, int $amount = null)
 * @method static \MasyukAI\Chip\DataObjects\Purchase releasePurchase(string $id)
 * @method static \MasyukAI\Chip\DataObjects\Purchase chargePurchase(string $id, string $recurringToken)
 * @method static \MasyukAI\Chip\DataObjects\Purchase refundPurchase(string $id, int $amount = null)
 * @method static \MasyukAI\Chip\DataObjects\Purchase markPurchaseAsPaid(string $id, int $paidOn = null)
 * @method static \MasyukAI\Chip\DataObjects\Purchase resendInvoice(string $id)
 * @method static void deleteRecurringToken(string $id)
 * @method static \MasyukAI\Chip\Builders\PurchaseBuilder purchase()
 * @method static \MasyukAI\Chip\DataObjects\Client createClient(array<string, mixed> $data)
 * @method static \MasyukAI\Chip\DataObjects\Client getClient(string $id)
 * @method static \MasyukAI\Chip\DataObjects\Client updateClient(string $id, array<string, mixed> $data)
 * @method static \MasyukAI\Chip\DataObjects\Client partialUpdateClient(string $id, array<string, mixed> $data)
 * @method static void deleteClient(string $id)
 * @method static array<string, mixed> listClients(array<string, mixed> $filters = [])
 * @method static array<string, mixed> listClientRecurringTokens(string $clientId)
 * @method static array<string, mixed> getClientRecurringToken(string $clientId, string $tokenId)
 * @method static void deleteClientRecurringToken(string $clientId, string $tokenId)
 * @method static array<string, mixed> createWebhook(array<string, mixed> $data)
 * @method static array<string, mixed> getWebhook(string $id)
 * @method static array<string, mixed> updateWebhook(string $id, array<string, mixed> $data)
 * @method static void deleteWebhook(string $id)
 * @method static array<string, mixed> listWebhooks(array<string, mixed> $filters = [])
 * @method static array<string, mixed> getPaymentMethods(array<string, mixed> $filters = [])
 * @method static string getPublicKey()
 * @method static \MasyukAI\Chip\Services\SubscriptionService subscriptions()
 *
 * @see \MasyukAI\Chip\Services\ChipCollectService
 */
class Chip extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ChipCollectService::class;
    }
}
