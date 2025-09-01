<?php

declare(strict_types=1);

namespace Masyukai\Chip\Facades;

use Illuminate\Support\Facades\Facade;
use Masyukai\Chip\Services\ChipCollectService;

/**
 * @method static \Masyukai\Chip\DataObjects\Purchase createPurchase(array $data)
 * @method static \Masyukai\Chip\DataObjects\Purchase getPurchase(string $id)
 * @method static \Masyukai\Chip\DataObjects\Purchase cancelPurchase(string $id)
 * @method static \Masyukai\Chip\DataObjects\Purchase capturePurchase(string $id, int $amount = null)
 * @method static \Masyukai\Chip\DataObjects\Purchase releasePurchase(string $id)
 * @method static \Masyukai\Chip\DataObjects\Purchase chargePurchase(string $id, string $recurringToken)
 * @method static \Masyukai\Chip\DataObjects\Payment refundPurchase(string $id, int $amount = null)
 * @method static \Masyukai\Chip\DataObjects\Purchase markAsPaid(string $id)
 * @method static \Masyukai\Chip\DataObjects\Purchase resendInvoice(string $id)
 * @method static void deleteRecurringToken(string $id)
 * @method static \Masyukai\Chip\DataObjects\Client createClient(array $data)
 * @method static \Masyukai\Chip\DataObjects\Client getClient(string $id)
 * @method static \Masyukai\Chip\DataObjects\Client updateClient(string $id, array $data)
 * @method static void deleteClient(string $id)
 * @method static array listClients(array $filters = [])
 * @method static array listRecurringTokens(string $clientId)
 * @method static \Masyukai\Chip\DataObjects\Webhook createWebhook(array $data)
 * @method static \Masyukai\Chip\DataObjects\Webhook getWebhook(string $id)
 * @method static \Masyukai\Chip\DataObjects\Webhook updateWebhook(string $id, array $data)
 * @method static void deleteWebhook(string $id)
 * @method static array listWebhooks()
 * @method static array getPaymentMethods()
 * @method static string getPublicKey()
 * @method static \Masyukai\Chip\Services\SubscriptionService subscriptions()
 *
 * @see \Masyukai\Chip\Services\ChipCollectService
 */
class Chip extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ChipCollectService::class;
    }
}
