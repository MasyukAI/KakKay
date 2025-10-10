<?php

declare(strict_types=1);

namespace MasyukAI\Jnt\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use MasyukAI\Jnt\Data\PrintWaybillData;
use MasyukAI\Jnt\Exceptions\JntApiException;
use MasyukAI\Jnt\Services\JntExpressService;

class OrderPrintCommand extends Command
{
    protected $signature = 'jnt:order:print {order-id : Order ID to print} {--path=storage/waybills : Directory to save PDF}';

    protected $description = 'Print waybill for a J&T Express order';

    public function handle(JntExpressService $jnt): int
    {
        $orderId = $this->argument('order-id');
        $path = $this->option('path');

        $this->info('Printing waybill for order: '.$orderId);

        try {
            $result = $jnt->printOrder($orderId);
            $waybill = PrintWaybillData::fromApiArray($result);

            if ($waybill->hasBase64Content()) {
                $filename = $orderId.'.pdf';
                $fullPath = base_path(sprintf('%s/%s', $path, $filename));

                if ($waybill->savePdf($fullPath)) {
                    $this->newLine();
                    $this->info('✓ Waybill saved successfully!');
                    $this->line('Location: '.$fullPath);
                    $this->line('Size: '.$waybill->getFormattedSize());
                } else {
                    $this->error('Failed to save waybill PDF.');

                    return self::FAILURE;
                }
            } elseif ($waybill->hasUrlContent()) {
                $this->newLine();
                $this->info('✓ Waybill URL generated!');
                $this->line('Download URL: '.$waybill->getDownloadUrl());
            } else {
                $this->warn('No waybill content available.');

                return self::FAILURE;
            }

            return self::SUCCESS;
        } catch (JntApiException $e) {
            $this->newLine();
            $this->error('API Error: '.$e->getMessage());

            return self::FAILURE;
        } catch (Exception $e) {
            $this->newLine();
            $this->error('Error: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
