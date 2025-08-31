<div x-data="{
    warehouseId: @js($warehouseId),
    inventory: [],
    selectedItems: [],
    loading: false,
    init() {
        if (this.warehouseId) {
            this.loadInventory();
        }
    },
    loadInventory() {
        this.loading = true;
        fetch(`{{ route('api.warehouse.inventory') }}?warehouse_id=${this.warehouseId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                this.inventory = data;
                this.loading = false;
            })
            .catch(error => {
                console.error('Error loading inventory:', error);
                this.loading = false;
                alert('Failed to load warehouse inventory. Error: ' + error.message);
                this.inventory = [];
            });
    },
    toggleItem(medicineId) {
        const index = this.selectedItems.indexOf(medicineId);
        if (index === -1) {
            this.selectedItems.push(medicineId);
        } else {
            this.selectedItems.splice(index, 1);
        }
    },
    addItemToOrder() {
        if (this.selectedItems.length === 0) {
            alert('Please select at least one medicine to add');
            return;
        }
        
        const items = this.inventory.filter(item => 
            this.selectedItems.includes(item.medicine_id)
        ).map(item => ({
            medicine_id: item.medicine_id,
            medicine_name: item.medicine.name,
            quantity: 1,
            unit_price: item.unit_price
        }));
        
        // Send event to Filament form
        Livewire.emit('addItemsToOrder', items);
        
        // Clear selections after adding
        this.selectedItems = [];
        
        // Show confirmation
        alert(`${items.length} item(s) added to your order`);
    }
}">
    <template x-if="warehouseId">
        <div class="mt-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Warehouse Inventory</h3>
            
            <template x-if="loading">
                <div class="flex items-center justify-center py-4">
                    <div class="animate-spin rounded-full h-6 w-6 border-t-2 border-b-2 border-primary-500"></div>
                    <span class="ml-2 text-gray-600 dark:text-gray-300">Loading inventory...</span>
                </div>
            </template>
            
            <template x-if="!loading && inventory.length === 0">
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 00 2 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1 md:flex md:justify-between">
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                No inventory found in this warehouse.
                            </p>
                        </div>
                    </div>
                </div>
            </template>
            
            <template x-if="!loading && inventory.length > 0">
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <input type="checkbox" 
                                           @click="selectedItems = $event.target.checked ? inventory.map(i => i.medicine_id) : []"
                                           class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Medicine
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Titer
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Unit Price
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="item in inventory" :key="item.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" 
                                               :checked="selectedItems.includes(item.medicine_id)"
                                               @change="toggleItem(item.medicine_id)"
                                               class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <span x-text="item.medicine.name"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <span x-text="item.medicine.titer"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span x-text="item.quantity" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                              :class="{
                                                  'bg-red-100 text-red-800': item.quantity <= 5,
                                                  'bg-yellow-100 text-yellow-800': item.quantity > 5 && item.quantity <= 15,
                                                  'bg-green-100 text-green-800': item.quantity > 15
                                              }">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        $<span x-text="item.unit_price.toFixed(2)"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 flex justify-end">
                    <button type="button" 
                            @click="addItemToOrder()"
                            :disabled="selectedItems.length === 0"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Selected (<span x-text="selectedItems.length"></span>)
                    </button>
                </div>
            </template>
        </div>
    </template>
</div>