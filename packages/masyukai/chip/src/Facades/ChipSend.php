<?php

declare(strict_types=1);

namespace MasyukAI\Chip\Facades;

use Illuminate\Support\Facades\Facade;
use MasyukAI\Chip\Services\ChipSendService;

/**
 * @method static array listAccounts()
 * @method static \MasyukAI\Chip\DataObjects\SendInstruction createSendInstruction(
 *     int $amountInCents,
 *     string $currency,
 *     string $recipientBankAccountId,
 *     string $description,
 *     string $reference,
 *     string $email
 * )
 * @method static \MasyukAI\Chip\DataObjects\SendInstruction getSendInstruction(string $id)
 * @method static array listSendInstructions(array $filters = [])
 * @method static \MasyukAI\Chip\DataObjects\BankAccount createBankAccount(array $data)
 * @method static \MasyukAI\Chip\DataObjects\BankAccount getBankAccount(string $id)
 * @method static array listBankAccounts(array $filters = [])
 * @method static \MasyukAI\Chip\DataObjects\BankAccount updateBankAccount(string $id, array $data)
 * @method static void deleteBankAccount(string $id)
 *
 * @see \MasyukAI\Chip\Services\ChipSendService
 */
class ChipSend extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ChipSendService::class;
    }
}
