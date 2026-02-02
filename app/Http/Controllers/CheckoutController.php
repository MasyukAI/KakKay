<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use AIArmada\Checkout\Actions\BuildCheckoutSessionViewData;
use AIArmada\Checkout\Models\CheckoutSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Renders checkout result pages (success/failure/cancel).
 *
 * All payment processing and status updates are handled by the
 * aiarmada/checkout package's PaymentCallbackController and
 * PaymentWebhookController. This controller only renders views.
 *
 * Uses the package's view data action for consistent data structure.
 */
final class CheckoutController extends Controller
{
    /**
     * Display checkout success page.
     *
     * Payment verification and order creation already handled by package callback.
     */
    public function success(Request $request, string $session): View
    {
        $checkoutSession = CheckoutSession::find($session);

        $viewData = $checkoutSession ? BuildCheckoutSessionViewData::run($checkoutSession) : ['reference' => $session];

        // Add app-specific Order model with relationships if needed
        if ($checkoutSession?->order_id) {
            $viewData['order'] = \App\Models\Order::with([
                'items',
                'billingAddress',
                'shippingAddress',
                'payments',
                'shipments',
            ])->find($checkoutSession->order_id);
        }

        return view('checkout.success', $viewData);
    }

    /**
     * Display checkout failure page.
     *
     * Payment status already updated by package callback.
     */
    public function failure(Request $request, string $session): View
    {
        $checkoutSession = CheckoutSession::find($session);

        $viewData = $checkoutSession ? BuildCheckoutSessionViewData::run($checkoutSession) : ['reference' => $session];
        $viewData['error'] = session('error', $request->get('error', 'Masalah teknikal dengan pembayaran'));

        return view('checkout.failure', $viewData);
    }

    /**
     * Display checkout cancel page.
     *
     * Session already marked cancelled by package callback.
     */
    public function cancel(Request $request, string $session): View
    {
        $checkoutSession = CheckoutSession::find($session);

        $viewData = $checkoutSession ? BuildCheckoutSessionViewData::run($checkoutSession) : ['reference' => $session];
        $viewData['message'] = 'Pembayaran dibatalkan';

        return view('checkout.cancel', $viewData);
    }
}
