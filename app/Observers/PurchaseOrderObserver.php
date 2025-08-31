<?php

namespace App\Observers;

use App\Models\Purchaseorder;
use App\Services\NestApiService;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class PurchaseOrderObserver
{
    protected NestApiService $nest;

    public function __construct()
    {
        $this->nest = app(NestApiService::class);
    }

    /**
     * On create: mirror to NestJS (create purchase + items)
     */
    public function created(Purchaseorder $order)
    {
        // Extract items from the complex structure
        $items = [];
        $formData = $order->data['data'][0] ?? [];

        if (isset($formData['items']) && is_array($formData['items']) && count($formData['items']) > 0) {
            $itemsContainer = $formData['items'][0];

            if (is_array($itemsContainer)) {
                foreach ($itemsContainer as $uuid => $itemArray) {
                    if (is_array($itemArray) && isset($itemArray[0]) && is_array($itemArray[0])) {
                        foreach ($itemArray[0] as $item) {
                            if (isset($item['medicine_id']) && $item['medicine_id'] !== null) {
                                $items[] = [
                                    'medicine_id' => $item['medicine_id'],
                                    'quantity' => $item['quantity'] ?? 1,
                                    'unit_price' => $item['unit_price'] ?? 0
                                ];
                            }
                        }
                    }
                }
            }
        }

        // If no items, don't call API
        if (empty($items)) {
            Notification::make()
                ->title('Error creating purchase order')
                ->body('Purchase must include at least one item')
                ->danger()
                ->send();

            return;
        }

        // Cast supplier_id to integer
        $supplierId = (int)($formData['supplier_id'] ?? 0);

        // Format data for NestJS API
        $payload = [
            'supplier_id' => $supplierId,
            'items' => $items
        ];

        try {
            $response = $this->nest->createPurchaseOrder($payload);

            // Update local order with response data if needed
            if (isset($response['id'])) {
                $order->update([
                    'external_id' => $response['id'],
                    'status' => $response['status'] ?? $order->status
                ]);
            }

            Notification::make()
                ->title('Purchase order created successfully')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $errorMessage = 'Please try again';

            if ($e->hasResponse()) {
                $responseData = $e->response->json();
                if (isset($responseData['message'])) {
                    $errorMessage = is_string($responseData['message']) ?
                        $responseData['message'] :
                        implode(', ', (array)$responseData['message']);
                }
            }

            Notification::make()
                ->title('Error creating purchase order')
                ->body($errorMessage)
                ->danger()
                ->send();

            Log::error('Failed to create purchase on Nest: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'payload' => $payload
            ]);
        }
    }

    /**
     * On update: detect status change and call Nest updateStatus
     */
    public function updated(Purchaseorder $order)
    {
        $originalStatus = $order->getOriginal('status');
        $newStatus = $order->status;

        if ($originalStatus !== $newStatus) {
            try {
                $this->nest->updatePurchaseStatus($order->id, ['status' => $newStatus]);

                Notification::make()
                    ->title('Purchase order status updated')
                    ->body("Order status changed to {$newStatus}")
                    ->success()
                    ->send();
            } catch (\Throwable $e) {
                $errorMessage = 'Status updated locally, but failed to sync with backend';

                Notification::make()
                    ->title('Error updating purchase status')
                    ->body($errorMessage)
                    ->warning()
                    ->send();

                Log::error('Failed to update purchase status on Nest: ' . $e->getMessage(), [
                    'order_id' => $order->id,
                    'status' => $newStatus
                ]);
            }
        }
    }
}

