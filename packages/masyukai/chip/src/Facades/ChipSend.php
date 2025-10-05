<?php

declare(strict_types=1);

namespace MasyukAI\Chip\Facades;

use Illuminate\Support\Facades\Facade;
use MasyukAI\Chip\Services\ChipSendService;

/**
 * @method static array<string, mixed> listAccounts()
 * @method static \MasyukAI\Chip\DataObjects\SendInstruction createSendInstruction(int $amountInCents, string $currency, string $recipientBankAccountId, string $description, string $reference, string $email)
 * @method static \MasyukAI\Chip\DataObjects\SendInstruction getSendInstruction(string $id)
 * @method static array<string, mixed> listSendInstructions(array<string, mixed> $filters = [])
 * @method static \MasyukAI\Chip\DataObjects\BankAccount createBankAccount(array<string, mixed> $data)
 * @method static \MasyukAI\Chip\DataObjects\BankAccount getBankAccount(string $id)
 * @method static array<string, mixed> listBankAccounts(array<string, mixed> $filters = [])
 * @method static \MasyukAI\Chip\DataObjects\BankAccount updateBankAccount(string $id, array<string, mixed> $data)
 * @method static void deleteBankAccount(string $id)
 *
 * @see ChipSendService
 */
final class ChipSend extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ChipSendService::class;
    }
}
