<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use MasyukAI\Jnt\Data\AddressData;
use MasyukAI\Jnt\Data\ItemData;
use MasyukAI\Jnt\Data\PackageInfoData;
use MasyukAI\Jnt\Enums\GoodsType;
use MasyukAI\Jnt\Exceptions\JntApiException;
use MasyukAI\Jnt\Exceptions\JntNetworkException;
use MasyukAI\Jnt\Exceptions\JntValidationException;
use MasyukAI\Jnt\Services\JntExpressService;

class OrderCreateCommand extends Command
{
    protected $signature = 'jnt:order:create
                          {--order-id= : Order ID}
                          {--sender-name= : Sender name}
                          {--sender-mobile= : Sender mobile}
                          {--receiver-name= : Receiver name}
                          {--receiver-mobile= : Receiver mobile}
                          {--receiver-address= : Receiver address}
                          {--item-name= : Item name}
                          {--item-qty=1 : Item quantity}
                          {--weight=1 : Package weight in kg}';

    protected $description = 'Create a new J&T Express order';

    public function handle(JntExpressService $jnt): int
    {
        $this->info('J&T Express - Create Order');
        $this->newLine();

        try {
            // Gather order information
            $orderId = $this->option('order-id') ?: $this->ask('Order ID');
            $senderName = $this->option('sender-name') ?: $this->ask('Sender Name');
            $senderMobile = $this->option('sender-mobile') ?: $this->ask('Sender Mobile');
            $receiverName = $this->option('receiver-name') ?: $this->ask('Receiver Name');
            $receiverMobile = $this->option('receiver-mobile') ?: $this->ask('Receiver Mobile');
            $receiverAddress = $this->option('receiver-address') ?: $this->ask('Receiver Address');
            $itemName = $this->option('item-name') ?: $this->ask('Item Name');
            $itemQty = (int) ($this->option('item-qty') ?: $this->ask('Item Quantity', 1));
            $weight = (float) ($this->option('weight') ?: $this->ask('Package Weight (kg)', 1));

            // Create data objects
            $sender = new AddressData(
                name: $senderName,
                phone: $senderMobile,
                address: 'Default Sender Address',
                postCode: '50000'
            );

            $receiver = new AddressData(
                name: $receiverName,
                phone: $receiverMobile,
                address: $receiverAddress,
                postCode: '50000'
            );

            $items = [
                new ItemData(
                    name: $itemName,
                    quantity: $itemQty,
                    weight: 100, // 100 grams default
                    price: 0
                ),
            ];

            $packageInfo = new PackageInfoData(
                quantity: 1,
                weight: $weight,
                value: 0,
                goodsType: GoodsType::PACKAGE
            );

            $this->info('Creating order...');

            $order = $jnt->createOrder($sender, $receiver, $items, $packageInfo, $orderId);

            $this->newLine();
            $this->info('✓ Order created successfully!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Order ID', $order->orderId],
                    ['Tracking Number', $order->txlogisticId ?? 'N/A'],
                    ['Status', 'Created'],
                ]
            );

            return self::SUCCESS;
        } catch (JntValidationException $e) {
            $this->newLine();
            $this->error('Validation Error: '.$e->getMessage());

            if (! empty($e->errors)) {
                $this->newLine();
                $this->warn('Validation Errors:');
                foreach ($e->errors as $field => $errors) {
                    foreach ((array) $errors as $error) {
                        $this->line("  • {$field}: {$error}");
                    }
                }
            }

            return self::FAILURE;
        } catch (JntNetworkException $e) {
            $this->newLine();
            $this->error('Network Error: '.$e->getMessage());
            $this->warn('Please check your internet connection and try again.');

            return self::FAILURE;
        } catch (JntApiException $e) {
            $this->newLine();
            $this->error('API Error: '.$e->getMessage());

            if ($e->errorCode) {
                $this->warn("Error Code: {$e->errorCode}");
            }

            return self::FAILURE;
        } catch (Exception $e) {
            $this->newLine();
            $this->error('Unexpected Error: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
