<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Log;
use MasyukAI\Chip\DataObjects\ClientDetails;
use MasyukAI\Chip\DataObjects\Product as ChipProduct;
use MasyukAI\Chip\Services\ChipCollectService;

final class ChipPaymentGateway implements PaymentGatewayInterface
{
    private ChipCollectService $chipService;

    public function __construct(?ChipCollectService $chipService = null)
    {
        $this->chipService = $chipService ?? app(ChipCollectService::class);
    }

    /**
     * Create a purchase through CHIP gateway
     *
     * @param  array  $customerData  Customer details
     * @param  array  $items  Cart items to be purchased
     * @return array Result with success status, purchase ID, checkout URL, etc.
     */
    public function createPurchase(array $customerData, array $items): array
    {
        try {
            // Convert cart items to CHIP products
            $chipProducts = $this->convertToChipProducts($items);

            // Create client details
            $clientDetails = $this->createClientDetails($customerData);

            // Construct required URLs for CHIP API
            $successUrl = route('checkout.success');
            $failureUrl = route('checkout.failure');
            $webhookUrl = route('webhooks.chip');

            // Create purchase with CHIP
            $purchase = $this->chipService->createCheckoutPurchase(
                $chipProducts,
                $clientDetails,
                [
                    'success_redirect' => $successUrl,
                    'failure_redirect' => $failureUrl,
                    'cancel_redirect' => $failureUrl,
                    'success_callback' => $webhookUrl,
                    'payment_method_whitelist' => $customerData['payment_method_whitelist'] ?? [],
                    'send_receipt' => true,
                ]
            );

            return [
                'success' => true,
                'purchase_id' => $purchase->id,
                'checkout_url' => $purchase->checkout_url,
                'gateway_response' => $purchase->toArray(),
            ];
        } catch (Exception $e) {
            Log::error('CHIP payment creation failed', [
                'error' => $e->getMessage(),
                'customer_data' => $customerData,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get available payment methods from CHIP
     *
     * @return array List of available payment methods
     */
    public function getAvailablePaymentMethods(): array
    {
        try {
            $methods = $this->chipService->getPaymentMethods([
                'brand_id' => config('chip.collect.brand_id'),
                'currency' => 'MYR',
                'country' => 'MY',
            ]);

            return collect($methods['available_payment_methods'] ?? [])
                ->map(function ($methodId) use ($methods) {
                    return [
                        'id' => $methodId,
                        'name' => $methods['names'][$methodId] ?? ucfirst(str_replace('_', ' ', $methodId)),
                        'description' => $this->getPaymentMethodDescription($methodId),
                        'icon' => $this->mapPaymentMethodToIcon($methodId),
                        'group' => $this->getPaymentMethodGroup($methodId),
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::error('Failed to load CHIP payment methods', [
                'error' => $e->getMessage(),
            ]);

            // Fallback payment methods
            return [
                [
                    'id' => 'fpx_b2c',
                    'name' => 'FPX Online Banking',
                    'description' => 'Bayar dengan Internet Banking Malaysia',
                    'icon' => 'building-office',
                    'group' => 'banking',
                ],
                [
                    'id' => 'visa',
                    'name' => 'Kad Kredit/Debit',
                    'description' => 'Visa, Mastercard',
                    'icon' => 'credit-card',
                    'group' => 'card',
                ],
            ];
        }
    }

    /**
     * Get the status of an existing purchase from CHIP
     *
     * @param  string  $purchaseId  The purchase ID to check
     * @return array|null Purchase status data or null if not found
     */
    public function getPurchaseStatus(string $purchaseId): ?array
    {
        try {
            $purchase = $this->chipService->getPurchase($purchaseId);

            if (! $purchase) {
                return null;
            }

            return [
                'id' => $purchase->id,
                'status' => $purchase->status,
                'checkout_url' => $purchase->checkout_url ?? null,
                'created_at' => $purchase->created_at,
                'updated_at' => $purchase->updated_at,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get CHIP purchase status', [
                'purchase_id' => $purchaseId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Convert cart items to CHIP products
     *
     * @param  array  $items  Cart items
     * @return array CHIP product objects
     */
    private function convertToChipProducts(array $items): array
    {
        $chipProducts = [];

        foreach ($items as $item) {
            // Try to determine the product category
            $category = 'books'; // Default category

            // First check if category is explicitly provided in attributes
            if (isset($item['attributes']['category']) && ! empty($item['attributes']['category'])) {
                $category = $item['attributes']['category'];
            }
            // If not, try to look up the product and get its category
            elseif (isset($item['id'])) {
                $product = Product::find($item['id']);
                if ($product && $product->category) {
                    $category = $product->category->name;
                }
            }

            $chipProducts[] = ChipProduct::fromArray([
                'name' => $item['name'],
                'price' => (int) $item['price'], // Ensure price is integer (cents)
                'quantity' => (string) $item['quantity'], // CHIP expects string<float> quantity
                'discount' => 0,
                'tax_percent' => 0.0,
                'category' => $category,
            ]);
        }

        return $chipProducts;
    }

    /**
     * Create CHIP client details from customer data
     * Send ONLY required fields to CHIP API (email is the only required field)
     *
     * @param  array  $customerData  Customer details
     * @return ClientDetails CHIP client details object
     */
    private function createClientDetails(array $customerData): ClientDetails
    {
        // CHIP API requires only email. Send minimal data.
        return ClientDetails::fromArray([
            'email' => $customerData['email'], // REQUIRED by CHIP API
            'full_name' => $customerData['name'] ?? null,
            'phone' => $customerData['phone'] ?? null,
            // All other fields are optional - only send if explicitly provided
            'street_address' => isset($customerData['street1'])
                ? $customerData['street1'].(isset($customerData['street2']) ? ', '.$customerData['street2'] : '')
                : null,
            'country' => $customerData['country'] ?? null,
            'city' => $customerData['city'] ?? null,
            'zip_code' => isset($customerData['postcode']) ? (string) $customerData['postcode'] : null,
            'state' => $customerData['state'] ?? null,
        ]);
    }

    /**
     * Get payment method description
     *
     * @param  string  $methodId  Payment method ID
     * @return string Description
     */
    private function getPaymentMethodDescription(string $methodId): string
    {
        $descriptions = [
            'fpx_b2c' => 'Bayar dengan Internet Banking Malaysia',
            'fpx_b2b' => 'Bayar dengan Internet Banking untuk Perniagaan',
            'visa' => 'Kad Kredit/Debit Visa',
            'mastercard' => 'Kad Kredit/Debit Mastercard',
            'tng' => 'Touch \'n Go eWallet',
            'grabpay' => 'GrabPay Malaysia',
            'boost' => 'Boost eWallet',
            'maybank_qr' => 'Maybank QR Pay',
            'duitnow_qr' => 'DuitNow QR',
            'alipay' => 'Alipay',
            'wechatpay' => 'WeChat Pay',
        ];

        return $descriptions[$methodId] ?? ucfirst(str_replace('_', ' ', $methodId));
    }

    /**
     * Map payment method ID to an icon
     *
     * @param  string  $methodId  Payment method ID
     * @return string Icon name
     */
    private function mapPaymentMethodToIcon(string $methodId): string
    {
        $iconMap = [
            'fpx_b2c' => 'building-office',
            'visa' => 'credit-card',
            'mastercard' => 'credit-card',
            'alipay' => 'currency-yen',
            'wechatpay' => 'chat-bubble-left',
            'tng' => 'device-phone-mobile',
            'grabpay' => 'device-phone-mobile',
            'boost' => 'device-phone-mobile',
            'maybank_qr' => 'qr-code',
            'razerpay' => 'device-phone-mobile',
        ];

        return $iconMap[$methodId] ?? 'credit-card';
    }

    /**
     * Get payment method group
     *
     * @param  string  $methodId  Payment method ID
     * @return string Group name
     */
    private function getPaymentMethodGroup(string $methodId): string
    {
        $bankingMethods = ['fpx_b2c', 'fpx_b2b', 'fpx_m2e'];
        $cardMethods = ['visa', 'mastercard', 'amex', 'unionpay'];
        $ewalletMethods = ['tng', 'grabpay', 'boost', 'razerpay', 'alipay', 'wechatpay'];
        $qrMethods = ['maybank_qr', 'duitnow_qr'];
        $bnplMethods = ['atome', 'billplz', 'zip'];

        if (in_array($methodId, $bankingMethods)) {
            return 'banking';
        }
        if (in_array($methodId, $cardMethods)) {
            return 'card';
        }
        if (in_array($methodId, $ewalletMethods)) {
            return 'ewallet';
        }
        if (in_array($methodId, $qrMethods)) {
            return 'qr';
        }
        if (in_array($methodId, $bnplMethods)) {
            return 'bnpl';
        }

        return 'other';
    }
}
