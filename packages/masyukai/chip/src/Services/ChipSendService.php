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
        return $this->client->get('accounts/');
    }

    public function createSendInstruction(array $data): SendInstruction
    {
        $response = $this->client->post('send_instructions/', $data);
        
        return SendInstruction::fromArray($response);
    }

    public function getSendInstruction(string $id): SendInstruction
    {
        $response = $this->client->get("send_instructions/{$id}/");
        
        return SendInstruction::fromArray($response);
    }

    public function listSendInstructions(array $filters = []): array
    {
        $queryString = http_build_query($filters);
        $endpoint = 'send_instructions/' . ($queryString ? "?{$queryString}" : '');
        
        return $this->client->get($endpoint);
    }

    public function createBankAccount(array $data): BankAccount
    {
        $response = $this->client->post('bank_accounts/', $data);
        
        return BankAccount::fromArray($response);
    }

    public function getBankAccount(string $id): BankAccount
    {
        $response = $this->client->get("bank_accounts/{$id}/");
        
        return BankAccount::fromArray($response);
    }

    public function listBankAccounts(array $filters = []): array
    {
        $queryString = http_build_query($filters);
        $endpoint = 'bank_accounts/' . ($queryString ? "?{$queryString}" : '');
        
        return $this->client->get($endpoint);
    }

    public function updateBankAccount(string $id, array $data): BankAccount
    {
        $response = $this->client->put("bank_accounts/{$id}/", $data);
        
        return BankAccount::fromArray($response);
    }

    public function deleteBankAccount(string $id): void
    {
        $this->client->delete("bank_accounts/{$id}/");
    }

    public function validateBankAccount(array $data): array
    {
        return $this->client->post('bank_accounts/validate/', $data);
    }

    public function increaseSendLimit(int $amount, string $reason): array
    {
        $data = [
            'amount' => $amount,
            'reason' => $reason,
        ];

        return $this->client->post('send_limit/increase/', $data);
    }

    public function cancelSendInstruction(string $id): SendInstruction
    {
        $response = $this->client->post("send_instructions/{$id}/cancel/");
        
        return SendInstruction::fromArray($response['data'] ?? $response);
    }

    public function verifyBankAccount(string $id): BankAccount
    {
        $response = $this->client->post("bank_accounts/{$id}/verify/");
        
        return BankAccount::fromArray($response['data'] ?? $response);
    }

    public function getBalance(): array
    {
        $response = $this->client->get('balance/');
        
        return $response['data'] ?? $response;
    }

    public function getSendLimits(): array
    {
        $response = $this->client->get('send_limits/');
        
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

        $response = $this->client->post('send_limits/increase/', $data);
        
        return $response['data'] ?? $response;
    }
}
