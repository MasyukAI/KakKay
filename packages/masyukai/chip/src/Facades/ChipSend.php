<?php

declare(strict_types=1);

namespace Masyukai\Chip\Facades;

use Illuminate\Support\Facades\Facade;
use Masyukai\Chip\Services\ChipSendService;

/**
 * @method static array listAccounts()
 * @method static \Masyukai\Chip\DataObjects\SendInstruction createSendInstruction(array $data)
 * @method static \Masyukai\Chip\DataObjects\SendInstruction getSendInstruction(string $id)
 * @method static array listSendInstructions(array $filters = [])
 * @method static \Masyukai\Chip\DataObjects\BankAccount createBankAccount(array $data)
 * @method static \Masyukai\Chip\DataObjects\BankAccount getBankAccount(string $id)
 * @method static array listBankAccounts(array $filters = [])
 * @method static \Masyukai\Chip\DataObjects\BankAccount updateBankAccount(string $id, array $data)
 * @method static void deleteBankAccount(string $id)
 * @method static array validateBankAccount(array $data)
 * @method static array increaseSendLimit(int $amount, string $reason)
 *
 * @see \Masyukai\Chip\Services\ChipSendService
 */
class ChipSend extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ChipSendService::class;
    }
}
