<?php

declare(strict_types=1);

namespace MasyukAI\Chip\Facades;

use Illuminate\Support\Facades\Facade;
use MasyukAI\Chip\Services\ChipCollectService;

/**
 * @method static \MasyukAI\Chip\DataObjects\Purchase createPurchase(array $data)
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
 * @method static \MasyukAI\Chip\DataObjects\Client createClient(array $data)
 * @method static \MasyukAI\Chip\DataObjects\Client getClient(string $id)
 * @method static \MasyukAI\Chip\DataObjects\Client updateClient(string $id, array $data)
 * @method static \MasyukAI\Chip\DataObjects\Client partialUpdateClient(string $id, array $data)
 * @method static void deleteClient(string $id)
 * @method static array listClients(array $filters = [])
 * @method static array listClientRecurringTokens(string $clientId)
 * @method static array getClientRecurringToken(string $clientId, string $tokenId)
 * @method static void deleteClientRecurringToken(string $clientId, string $tokenId)
 * @method static array createWebhook(array $data)
 * @method static array getWebhook(string $id)
 * @method static array updateWebhook(string $id, array $data)
 * @method static void deleteWebhook(string $id)
 * @method static array listWebhooks(array $filters = [])
 * @method static array getPaymentMethods(array $filters = [])
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
