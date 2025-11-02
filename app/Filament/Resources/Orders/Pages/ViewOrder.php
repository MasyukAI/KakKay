<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use AIArmada\Docs\DataObjects\DocData;
use AIArmada\Docs\Enums\DocStatus;
use AIArmada\Docs\Facades\Doc;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Response;

final class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('generateInvoice')
                ->label('Generate Invoice')
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('primary')
                ->action(function () {
                    /** @var \App\Models\Order $record */
                    $record = $this->record;

                    // Check if invoice already exists for this order
                    $existingDoc = \AIArmada\Docs\Models\Doc::query()
                        ->where('docable_type', \App\Models\Order::class)
                        ->where('docable_id', $record->id)
                        ->where('doc_type', 'invoice')
                        ->first();

                    if ($existingDoc) {
                        // Generate PDF on-the-fly
                        $pdfContent = Doc::generatePdf($existingDoc, false);

                        return Response::streamDownload(
                            fn () => print ($pdfContent),
                            "invoice-{$existingDoc->doc_number}.pdf",
                            ['Content-Type' => 'application/pdf']
                        );
                    }

                    // Create new invoice document
                    $items = [];
                    if ($record->orderItems->isNotEmpty()) {
                        foreach ($record->orderItems as $item) {
                            /** @var \App\Models\OrderItem $item */
                            $items[] = [
                                'description' => $item->product_name ?? 'Product',
                                'quantity' => $item->quantity,
                                'price' => $item->price / 100, // Convert cents to MYR
                                'amount' => ($item->price * $item->quantity) / 100,
                            ];
                        }
                    } elseif (! empty($record->cart_items)) {
                        foreach ($record->cart_items as $item) {
                            $items[] = [
                                'description' => $item['name'] ?? 'Product',
                                'quantity' => $item['quantity'] ?? 1,
                                'price' => ($item['price'] ?? 0) / 100,
                                'amount' => (($item['price'] ?? 0) * ($item['quantity'] ?? 1)) / 100,
                            ];
                        }
                    }

                    $customerData = [
                        'name' => $record->user->name ?? 'Guest',
                        'email' => $record->user->email ?? '',
                    ];

                    if ($record->address) {
                        /** @var \App\Models\Address $address */
                        $address = $record->address;
                        $customerData['address'] = $address->street1;
                        if ($address->street2) {
                            $customerData['address'] .= ', '.$address->street2;
                        }
                        $customerData['city'] = $address->city;
                        $customerData['state'] = $address->state;
                        $customerData['postal_code'] = $address->postcode;
                        $customerData['phone'] = $address->phone;
                    }

                    $docData = DocData::from([
                        'doc_type' => 'invoice',
                        'docable_type' => \App\Models\Order::class,
                        'docable_id' => $record->id,
                        'status' => DocStatus::PAID,
                        'issue_date' => $record->created_at,
                        'due_date' => $record->created_at,
                        'currency' => 'MYR',
                        'items' => $items,
                        'customer_data' => $customerData,
                        'notes' => "Order Number: {$record->order_number}",
                        'generate_pdf' => false,
                    ]);

                    $doc = Doc::createDocument($docData);

                    // Generate PDF on-the-fly
                    $pdfContent = Doc::generatePdf($doc, false);

                    Notification::make()
                        ->success()
                        ->title('Invoice Generated')
                        ->body("Invoice {$doc->doc_number} has been generated.")
                        ->send();

                    return Response::streamDownload(
                        fn () => print ($pdfContent),
                        "invoice-{$doc->doc_number}.pdf",
                        ['Content-Type' => 'application/pdf']
                    );
                }),
        ];
    }
}
