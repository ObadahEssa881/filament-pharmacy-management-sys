<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Services\SaleService;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    /**
     * Add an item to the repeater state and keep totals in sync.
     * Called from your Alpine component via Livewire: livewire.call('addItem', ...)
     */
    public function addItem(
        int $medicine_id,
        string $medicine_name,
        int $quantity,
        float $unit_price,
        float $cost_price = 0
    ): void {
        // Read current form state
        $state  = $this->form->getState();
        $items  = $state['saleItems'] ?? [];

        // Merge if the item already exists
        $found = false;
        foreach ($items as &$existing) {
            if ((int) $existing['medicine_id'] === (int) $medicine_id) {
                $existing['quantity']   = (int) $existing['quantity'] + (int) $quantity;
                $existing['unit_price'] = (float) $existing['unit_price']; // keep as-is
                $existing['cost_price'] = isset($existing['cost_price']) ? (float) $existing['cost_price'] : 0;
                $found = true;
                break;
            }
        }
        unset($existing);

        if (!$found) {
            $items[] = [
                'medicine_id'  => (int) $medicine_id,
                'medicine_name' => $medicine_name,
                'quantity'     => (int) $quantity,
                'unit_price'   => (float) $unit_price,
                'cost_price'   => (float) $cost_price,
                'total_price'  => (float) $quantity * (float) $unit_price,
            ];
        }

        // Recompute total
        $total = 0;
        foreach ($items as $it) {
            $line = (float) ($it['quantity'] ?? 0) * (float) ($it['unit_price'] ?? 0);
            $total += $line;
        }

        // Update form state (no weird 'data[0]' wrappers)
        $this->form->fill([
            'saleItems'           => $items,
            'total_amount'        => $total,
            'total_amount_display' => $total,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Filament passes a flat $data array with keys = your field names.
     * We extract saleItems directly and send to the service.
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $items = json_decode($data['items_json'] ?? '[]', true) ?: [];

        if (empty($items)) {
            Notification::make()
                ->title('Error creating sale')
                ->body('Sale must include at least one item')
                ->danger()
                ->send();

            $sale = new \App\Models\Sale();
            $sale->id = -1;
            return $sale;
        }

        $payload = [
            'customer_name' => $data['customer_name'] ?? 'Walk-in Customer',
            'payment_mode'  => $data['payment_mode'] ?? 'CASH',
            'sale_date'     => $data['sale_date'] ?? now(),
            'items'         => $items,
        ];

        try {
            $saleService = app(\App\Services\SaleService::class);
            $sale = $saleService->create($payload);

            Notification::make()
                ->title('Sale created successfully')
                ->success()
                ->send();

            return $sale;
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error creating sale')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $sale = new \App\Models\Sale();
            $sale->id = -1;
            return $sale;
        }
        logger()->info('Submitted items_json:', [
            'items_json' => $data['items_json'] ?? null,
        ]);
    }
}
