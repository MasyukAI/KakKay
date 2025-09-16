<?php

declare(strict_types=1);

namespace MasyukAI\Chip\Services;

use MasyukAI\Chip\Clients\ChipSendClient;
use MasyukAI\Chip\DataObjects\BankAccount;
use MasyukAI\Chip\DataObjects\SendInstruction;

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

        return $this->client->post('send/send_limits/request_increase', $data);
    }

    /**
     * Create a send limit
     */
    public function createSendLimit(array $data): array
    {
        return $this->client->post('send/send_limits', $data);
    }

    /**
     * Get a send limit
     */
    public function getSendLimit(string $id): array
    {
        return $this->client->get("send/send_limits/{$id}");
    }

    /**
     * Resend send limit approval requests
     */
    public function resendSendLimitApprovalRequests(string $id): array
    {
        return $this->client->post("send/send_limits/{$id}/resend_approval_requests");
    }

    /**
     * Create a group
     */
    public function createGroup(array $data): array
    {
        return $this->client->post('send/groups', $data);
    }

    /**
     * Get a group
     */
    public function getGroup(string $id): array
    {
        return $this->client->get("send/groups/{$id}");
    }

    /**
     * Update a group
     */
    public function updateGroup(string $id, array $data): array
    {
        return $this->client->put("send/groups/{$id}", $data);
    }

    /**
     * Delete a group
     */
    public function deleteGroup(string $id): void
    {
        $this->client->delete("send/groups/{$id}");
    }

    /**
     * List groups
     */
    public function listGroups(array $filters = []): array
    {
        $queryString = http_build_query($filters);
        $endpoint = 'send/groups'.($queryString ? '?'.$queryString : '');

        return $this->client->get($endpoint);
    }

    /**
     * Create a webhook for CHIP Send
     */
    public function createSendWebhook(array $data): array
    {
        return $this->client->post('send/webhooks', $data);
    }

    /**
     * Get a CHIP Send webhook
     */
    public function getSendWebhook(string $id): array
    {
        return $this->client->get("send/webhooks/{$id}");
    }

    /**
     * Update a CHIP Send webhook
     */
    public function updateSendWebhook(string $id, array $data): array
    {
        return $this->client->put("send/webhooks/{$id}", $data);
    }

    /**
     * Delete a CHIP Send webhook
     */
    public function deleteSendWebhook(string $id): void
    {
        $this->client->delete("send/webhooks/{$id}");
    }

    /**
     * List CHIP Send webhooks
     */
    public function listSendWebhooks(array $filters = []): array
    {
        $queryString = http_build_query($filters);
        $endpoint = 'send/webhooks'.($queryString ? '?'.$queryString : '');

        return $this->client->get($endpoint);
    }

    /**
     * Delete a send instruction
     */
    public function deleteSendInstruction(string $id): void
    {
        $this->client->delete("send/send_instructions/{$id}");
    }

    /**
     * Resend a send instruction webhook
     */
    public function resendSendInstructionWebhook(string $id): array
    {
        return $this->client->post("send/send_instructions/{$id}/resend_webhook");
    }

    /**
     * Resend a bank account webhook
     */
    public function resendBankAccountWebhook(string $id): array
    {
        return $this->client->post("send/bank_accounts/{$id}/resend_webhook");
    }
}
