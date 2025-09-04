<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Masyukai\Chip\DataObjects\ClientDetails;
use Masyukai\Chip\DataObjects\Product as ChipProduct;
use Masyukai\Chip\Services\ChipCollectService;

class ChipPaymentGateway implements PaymentGatewayInterface
{
    protected ChipCollectService $chipService;

    public function __construct(ChipCollectService $chipService = null)
    {
        $this->chipService = $chipService ?? new ChipCollectService();
    }

    /**
     * Create a purchase through CHIP gateway
     *
     * @param array $customerData Customer details
     * @param array $items Cart items to be purchased
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
                    'reference' => $customerData['reference'] ?? null,
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
        } catch (\Exception $e) {
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
                'country' => 'MY', // Default to Malaysia
            ]);

            return collect($methods['items'] ?? [])
                ->map(function ($method) {
                    return [
                        'id' => $method['id'],
                        'name' => $method['name'],
                        'description' => $method['description'] ?? null,
                        'icon' => $this->mapPaymentMethodToIcon($method['id']),
                        'group' => $this->getPaymentMethodGroup($method['id']),
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
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
     * Convert cart items to CHIP products
     *
     * @param array $items Cart items
     * @return array CHIP product objects
     */
    protected function convertToChipProducts(array $items): array
    {
        $chipProducts = [];
        
        foreach ($items as $item) {
            // Try to determine the product category
            $category = null;
            
            // First check if category is explicitly provided in attributes
            if (isset($item['attributes']['category']) && !empty($item['attributes']['category'])) {
                $category = $item['attributes']['category'];
            } 
            // If not, try to look up the product and get its category
            else if (isset($item['id'])) {
                $product = Product::find($item['id']);
                if ($product && $product->category) {
                    $category = $product->category->name;
                }
            }
            
            // Always ensure we have a category, default to 'books' if nothing found
            if (!$category) {
                $category = 'books';
            }
            
            $chipProducts[] = ChipProduct::fromArray([
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'discount' => 0,
                'tax_percent' => 0.0,
                'category' => $category,
            ]);
        }
        
        return $chipProducts;
    }

    /**
     * Create CHIP client details from customer data
     *
     * @param array $customerData Customer details
     * @return ClientDetails CHIP client details object
     */
    protected function createClientDetails(array $customerData): ClientDetails
    {
        return ClientDetails::fromArray([
            'full_name' => $customerData['name'],
            'email' => $customerData['email'],
            'phone' => $customerData['phone'] ?? '',
            'personal_code' => $customerData['personal_code'] ?? $customerData['id_number'] ?? '',
            'legal_name' => $customerData['company_name'] ?? $customerData['name'],
            'brand_name' => $customerData['brand_name'] ?? $customerData['company_name'] ?? $customerData['name'],
            'street_address' => $customerData['address'] ?? '',
            'country' => $customerData['country'] ?? 'MY',
            'city' => $customerData['city'] ?? '',
            'zip_code' => $customerData['zip'] ?? $customerData['postal_code'] ?? '',
            'state' => $customerData['state'] ?? '',
            'registration_number' => $customerData['registration_number'] ?? '',
            'tax_number' => $customerData['tax_number'] ?? '',
            // Required fields that were missing
            'bank_account' => $customerData['bank_account'] ?? 'default',
            'bank_code' => $customerData['bank_code'] ?? 'default',
            // Shipping details
            'shipping_street_address' => $customerData['shipping_address'] ?? $customerData['address'] ?? '',
            'shipping_country' => $customerData['shipping_country'] ?? $customerData['country'] ?? 'MY',
            'shipping_city' => $customerData['shipping_city'] ?? $customerData['city'] ?? '',
            'shipping_zip_code' => $customerData['shipping_zip'] ?? $customerData['zip'] ?? $customerData['postal_code'] ?? '',
            'shipping_state' => $customerData['shipping_state'] ?? $customerData['state'] ?? '',
        ]);
    }

    /**
     * Map payment method ID to an icon
     *
     * @param string $methodId Payment method ID
     * @return string Icon name
     */
    protected function mapPaymentMethodToIcon(string $methodId): string
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
     * @param string $methodId Payment method ID
     * @return string Group name
     */
    protected function getPaymentMethodGroup(string $methodId): string
    {
        $bankingMethods = ['fpx_b2c', 'fpx_b2b', 'fpx_m2e'];
        $cardMethods = ['visa', 'mastercard', 'amex', 'unionpay'];
        $ewalletMethods = ['tng', 'grabpay', 'boost', 'razerpay', 'alipay', 'wechatpay'];
        $qrMethods = ['maybank_qr', 'duitnow_qr'];
        $bnplMethods = ['atome', 'billplz', 'zip'];

        if (in_array($methodId, $bankingMethods)) {
            return 'banking';
        } elseif (in_array($methodId, $cardMethods)) {
            return 'card';
        } elseif (in_array($methodId, $ewalletMethods)) {
            return 'ewallet';
        } elseif (in_array($methodId, $qrMethods)) {
            return 'qr';
        } elseif (in_array($methodId, $bnplMethods)) {
            return 'bnpl';
        }

        return 'other';
    }
}
