<?php

declare(strict_types=1);

namespace AIArmada\Vouchers;

use AIArmada\Cart\CartManager;
use AIArmada\Cart\Facades\Cart as CartFacade;
use AIArmada\Cart\Services\CartConditionResolver;
use AIArmada\Vouchers\Conditions\VoucherCondition;
use AIArmada\Vouchers\Data\VoucherData;
use AIArmada\Vouchers\Facades\Voucher;
use AIArmada\Vouchers\Services\VoucherService;
use AIArmada\Vouchers\Services\VoucherValidator;
use AIArmada\Vouchers\Support\CartManagerWithVouchers;
use AIArmada\Vouchers\Support\VoucherRulesFactory;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class VoucherServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('vouchers')
            ->hasConfigFile()
            ->discoversMigrations()
            ->runsMigrations();
    }

    public function packageRegistered(): void
    {
        // Register services as singletons
        $this->app->singleton(VoucherService::class);
        $this->app->singleton(VoucherValidator::class);
        $this->app->singleton(VoucherRulesFactory::class, static fn () => new VoucherRulesFactory());

        $this->app->resolving(CartConditionResolver::class, function (CartConditionResolver $resolver): void {
            $resolver->register(function (mixed $payload) {
                if ($payload instanceof VoucherCondition) {
                    $cartCondition = $payload->toCartCondition();

                    return $payload->isDynamic() ? $cartCondition->withoutRules() : $cartCondition;
                }

                if ($payload instanceof VoucherData) {
                    return (new VoucherCondition($payload, dynamic: false))
                        ->toCartCondition();
                }

                if (is_array($payload)) {
                    $code = $payload['voucher_code'] ?? $payload['code'] ?? null;

                    if (is_string($code) && $code !== '' && ($voucherData = Voucher::find($code))) {
                        $order = isset($payload['order']) && is_int($payload['order'])
                            ? $payload['order']
                            : config('vouchers.cart.condition_order', 50);

                        return (new VoucherCondition($voucherData, $order, dynamic: false))
                            ->toCartCondition();
                    }
                }

                if (is_string($payload) && str_starts_with($payload, 'voucher:')) {
                    $code = mb_substr($payload, 8);

                    if ($code !== '' && ($voucherData = Voucher::find($code))) {
                        return (new VoucherCondition($voucherData, dynamic: false))
                            ->toCartCondition();
                    }
                }

                return null;
            }, 100);
        });

        // Bind facade accessor
        $this->app->alias(VoucherService::class, 'voucher');
        CartFacade::resolved(function (CartManager $manager, $app): void {
            if ($manager instanceof CartManagerWithVouchers) {
                return;
            }

            $proxy = CartManagerWithVouchers::fromCartManager($manager);

            $app->instance('cart', $proxy);
            $app->instance(CartManager::class, $proxy);
        });
    }

    /**
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            VoucherService::class,
            VoucherValidator::class,
            'voucher',
        ];
    }
}
