<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use AIArmada\Docs\DataObjects\DocumentData;
use AIArmada\Docs\Enums\DocumentStatus;
use AIArmada\Docs\Facades\Document;
use Akaunting\Money\Money;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Response;

final class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('address.name')
                    ->searchable(),
                TextColumn::make('delivery_method')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label('Items Count')
                    ->formatStateUsing(function ($record) {
                        // Try to count from orderItems relationship first
                        if ($record->orderItems && $record->orderItems->isNotEmpty()) {
                            return $record->orderItems->sum('quantity');
                        }
                        // Fallback to cart_items JSON data
                        if (! empty($record->cart_items)) {
                            return collect($record->cart_items)->sum('quantity');
                        }

                        return 0;
                    })
                    ->alignCenter()
                    ->sortable(false),
                TextColumn::make('total')
                    ->numeric()
                    ->formatStateUsing(fn ($state) => Money::MYR($state))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('generateInvoice')
                    ->label('Generate Invoice')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->color('primary')
                    ->action(function ($record) {
                        // Check if invoice already exists for this order
                        $existingDocument = \AIArmada\Docs\Models\Document::query()
                            ->where('documentable_type', \App\Models\Order::class)
                            ->where('documentable_id', $record->id)
                            ->where('document_type', 'invoice')
                            ->first();

                        if ($existingDocument) {
                            // Generate PDF on-the-fly
                            $pdfContent = Document::generatePdf($existingDocument, false);

                            return Response::streamDownload(
                                fn () => print ($pdfContent),
                                "invoice-{$existingDocument->document_number}.pdf",
                                ['Content-Type' => 'application/pdf']
                            );
                        }

                        // Create new invoice document
                        $items = [];
                        if ($record->orderItems && $record->orderItems->isNotEmpty()) {
                            foreach ($record->orderItems as $item) {
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
                            'name' => $record->user?->name ?? 'Guest',
                            'email' => $record->user?->email ?? '',
                        ];

                        if ($record->address) {
                            $customerData['address'] = $record->address->street1;
                            if ($record->address->street2) {
                                $customerData['address'] .= ', '.$record->address->street2;
                            }
                            $customerData['city'] = $record->address->city;
                            $customerData['state'] = $record->address->state;
                            $customerData['postal_code'] = $record->address->postcode;
                            $customerData['phone'] = $record->address->phone;
                        }

                        $documentData = DocumentData::from([
                            'document_type' => 'invoice',
                            'documentable_type' => \App\Models\Order::class,
                            'documentable_id' => $record->id,
                            'status' => DocumentStatus::PAID,
                            'issue_date' => $record->created_at,
                            'due_date' => $record->created_at,
                            'currency' => 'MYR',
                            'items' => $items,
                            'customer_data' => $customerData,
                            'notes' => "Order Number: {$record->order_number}",
                            'generate_pdf' => false,
                        ]);

                        $document = Document::createDocument($documentData);

                        // Generate PDF on-the-fly
                        $pdfContent = Document::generatePdf($document, false);

                        Notification::make()
                            ->success()
                            ->title('Invoice Generated')
                            ->body("Invoice {$document->document_number} has been generated.")
                            ->send();

                        return Response::streamDownload(
                            fn () => print ($pdfContent),
                            "invoice-{$document->document_number}.pdf",
                            ['Content-Type' => 'application/pdf']
                        );
                    })
                    ->successNotification(null), // Disable default notification since we're downloading
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
