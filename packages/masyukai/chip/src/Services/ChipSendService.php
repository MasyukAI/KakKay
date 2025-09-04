<?php

declare(strict_types=1);

namespace Masyukai\Chip\Services;

use Masyukai\Chip\Clients\ChipSendClient;
use Masyukai\Chip\DataObjects\BankAccount;
use Masyukai\Chip\DataObjects\SendInstruction;

class ChipSendService
{
    public function __construct(
        protected ChipSendClient $client
    ) {}

    public function listAccounts(): array
    {
        return $this->client->get('send/accounts');
    }

    public function createSendInstruction(
        int $amountInCents,
        string $currency,
        string $recipientBankAccountId,
        string $description,
        ?string $reference = null,
        ?string $email = null
    ): SendInstruction {
        $data = [
            'bank_account_id' => $recipientBankAccountId,
            'amount' => number_format($amountInCents / 100, 2, '.', ''),
            'description' => $description,
        ];

        if ($reference) {
            $data['reference'] = $reference;
        }

        if ($email) {
            $data['email'] = $email;
        }

        $response = $this->client->post('send/send_instructions', $data);

        return SendInstruction::fromArray($response);
    }

    public function getSendInstruction(string $id): SendInstruction
    {
        $response = $this->client->get("send/send_instructions/{$id}");

        return SendInstruction::fromArray($response);
    }

    public function listSendInstructions(array $filters = []): array
    {
        $queryString = http_build_query($filters);
        $endpoint = 'send/send_instructions'.($queryString ? "?{$queryString}" : '');

        return $this->client->get($endpoint);
    }

    public function createBankAccount(
        string $bankCode,
        string $accountNumber,
        string $accountHolderName,
        string $accountType = 'savings'
    ): BankAccount {
        $data = [
            'bank_code' => $bankCode,
            'account_number' => $accountNumber,
            'name' => $accountHolderName,
        ];

        $response = $this->client->post('send/bank_accounts', $data);

        return BankAccount::fromArray($response);
    }

    public function getBankAccount(string $id): BankAccount
    {
        $response = $this->client->get("send/bank_accounts/{$id}");

        return BankAccount::fromArray($response);
    }

    public function listBankAccounts(array $filters = []): array
    {
        $queryString = http_build_query($filters);
        $endpoint = 'send/bank_accounts'.($queryString ? "?{$queryString}" : '');

        return $this->client->get($endpoint);
    }

    public function updateBankAccount(string $id, array $data): BankAccount
    {
        $response = $this->client->put("send/bank_accounts/{$id}", $data);

        return BankAccount::fromArray($response);
    }

    public function deleteBankAccount(string $id): void
    {
        $this->client->delete("send/bank_accounts/{$id}");
    }

    public function validateBankAccount(array $data): array
    {
        return $this->client->post('send/bank_accounts/validate', $data);
    }

    public function increaseSendLimit(int $amount, string $reason): array
    {
        $data = [
            'amount' => $amount,
            'reason' => $reason,
        ];

        return $this->client->post('send/send_limits/increase', $data);
    }

    public function cancelSendInstruction(string $id): SendInstruction
    {
        $response = $this->client->post("send/send_instructions/{$id}/cancel");

        return SendInstruction::fromArray($response['data'] ?? $response);
    }

    public function verifyBankAccount(string $id): BankAccount
    {
        $response = $this->client->post("send/bank_accounts/{$id}/verify");

        return BankAccount::fromArray($response['data'] ?? $response);
    }

    public function getBalance(): array
    {
        $response = $this->client->get('send/balance');

        return $response['data'] ?? $response;
    }

    public function getSendLimits(): array
    {
        $response = $this->client->get('send/send_limits');

        return $response['data'] ?? $response;
    }

    public function requestSendLimitIncrease(
        int $requestedDailyLimitInCents,
        int $requestedMonthlyLimitInCents,
        string $businessJustification
    ): array {
        $data = [
            'requested_daily_limit_in_cents' => $requestedDailyLimitInCents,
            'requested_monthly_limit_in_cents' => $requestedMonthlyLimitInCents,
            'business_justification' => $businessJustification,
        ];

        $response = $this->client->post('send/send_limits/increase', $data);

        return $response['data'] ?? $response;
    }
}
