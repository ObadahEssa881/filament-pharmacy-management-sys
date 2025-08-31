<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    // simple public items array that holds cart items
    public array $items = [];

    public function mount(): void
    {
        parent::mount();
        $this->items = []; // start empty
        Log::info('CreatePurchaseOrder mounted', ['items_init' => $this->items]);
    }

    // Listen for the global Livewire event 'add_item'
    #[On('add_item')]
    public function addItem(...$params): void
    {
        // If Alpine dispatches an object, it will come in as one param
        if (count($params) === 1 && is_array($params[0])) {
            $item = $params[0];
        } else {
            // fallback: map positional arguments
            $item = [
                'medicine_id'   => $params[0] ?? null,
                'medicine_name' => $params[1] ?? null,
                'quantity'      => $params[2] ?? null,
                'unit_price'    => $params[3] ?? null,
                'cost_price'    => $params[4] ?? null,
            ];
        }

        $this->items[] = $item;
    }



    // When Filament/Livewire submits the record, inject $this->items into the final payload
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $items = $this->items; // Use the Livewire property, not $data

        if (count($items) === 0) {
            Notification::make()
                ->title('Validation')
                ->danger()
                ->body('Purchase must include at least one item')
                ->send();

            throw new \Exception('Purchase must include at least one item');
        }

        $payload = [
            'supplier_id' => $data['supplier_id'],
            'items'       => $items,
        ];

        $purchaseService = app(\App\Services\PurchaseService::class);
        return $purchaseService->create($payload);
    }
}
