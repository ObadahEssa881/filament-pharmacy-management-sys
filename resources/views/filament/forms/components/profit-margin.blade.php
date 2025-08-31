@if($record)
    @php
        $costPrice = $record->cost_price;
        $sellingPrice = $record->selling_price;
        $profitMargin = $costPrice > 0 ? (($sellingPrice - $costPrice) / $costPrice) * 100 : 0;
        $formattedMargin = round($profitMargin, 2) . '%';
        
        $color = $profitMargin > 30 ? 'bg-green-100 text-green-800' : 
                 ($profitMargin > 15 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
    @endphp
    
    <div class="col-span-full">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profit Margin</label>
        <div class="flex items-center">
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 rounded border border-gray-300 dark:border-gray-600 flex-1">
                {{ $formattedMargin }}
            </div>
            <span class="ml-2 px-2 py-1 text-xs font-medium rounded {{ $color }}">
                {{ $profitMargin > 0 ? 'Good' : 'Low' }}
            </span>
        </div>
    </div>
@else
    <div class="col-span-full">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profit Margin</label>
        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 rounded border border-gray-300 dark:border-gray-600">
            N/A
        </div>
    </div>
@endif