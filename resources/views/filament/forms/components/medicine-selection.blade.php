<div 
    x-data="{
        warehouseId: @js($warehouseId),
        inventory: [],
        cart: [],
        selectedMedicine: null,
        quantity: 1,
        loading: false,

        init() {
            if (this.warehouseId) {
                this.loadInventory();
            }
        },

        loadInventory() {
            this.loading = true;
            fetch(`{{ route('api.warehouse.inventory') }}?warehouse_id=${this.warehouseId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            })
            .then(r => r.json())
            .then(data => {
                this.inventory = data;
                this.loading = false;
            })
            .catch(() => {
                this.inventory = [];
                this.loading = false;
            });
        },

        addToCart() {
            if (!this.selectedMedicine || this.quantity <= 0) return;

            const selectedId = parseInt(this.selectedMedicine, 10);
            const medicine = this.inventory.find(i => i.medicine_id === selectedId);
            if (!medicine) return;

            // merge if already in cart
            const existing = this.cart.find(i => i.medicine_id === selectedId);
            if (existing) {
                existing.quantity += this.quantity;
                existing.line_total = existing.quantity * existing.unit_price;
            } else {
                this.cart.push({
                    medicine_id: medicine.medicine_id,
                    medicine_name: medicine.medicine.name,
                    cost_price: medicine.cost_price ?? medicine.unit_price, // fallback
                    unit_price: medicine.unit_price,
                    quantity: this.quantity,
                    line_total: this.quantity * medicine.unit_price,
                });
            }

            // ✅ send to Livewire
            window.Livewire.dispatch('add_item', {
                medicine_id: medicine.medicine_id,
                medicine_name: medicine.medicine.name,
                quantity: this.quantity,
                unit_price: medicine.unit_price,
                cost_price: medicine.cost_price ?? medicine.unit_price,
            });

            this.selectedMedicine = null;
            this.quantity = 1;
        },

        removeFromCart(id) {
            this.cart = this.cart.filter(i => i.medicine_id !== id);
        },

        cartTotal() {
            return this.cart.reduce((sum, i) => sum + i.line_total, 0);
        }
    }"
>
    <div class="mt-4 space-y-6">

        <!-- Medicine selection form -->
        <div class="p-4 border rounded-lg bg-white dark:bg-gray-800 shadow">
            <h3 class="text-lg font-semibold mb-4">Select Medicine</h3>

            <template x-if="loading">
                <p class="text-gray-500">Loading inventory...</p>
            </template>

            <template x-if="!loading && inventory.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Medicine</label>
                        <select 
                            x-model.number="selectedMedicine"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white"
                        >
                            <option value="" disabled>Select...</option>
                            <template x-for="item in inventory" :key="item.id">
                                <option :value="item.medicine_id" 
                                        x-text="`${item.medicine.name} (${item.quantity} in stock)`"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                        <input type="number" x-model.number="quantity" min="1"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white"
                               :disabled="!selectedMedicine">
                    </div>

                    <div class="flex items-end">
                        <button type="button" 
                            @click="addToCart()" 
                            :disabled="!selectedMedicine || quantity <= 0"
                            class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md shadow disabled:opacity-50">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Info about selected medicine -->
        <template x-if="selectedMedicine">
            <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-700">
                <h4 class="font-semibold mb-3">Medicine Info</h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Name</span>
                        <p class="font-medium" x-text="inventory.find(i => i.medicine_id === selectedMedicine)?.medicine.name"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Cost Price</span>
                        <p class="font-medium" x-text="'$' + (inventory.find(i => i.medicine_id === selectedMedicine)?.cost_price ?? 0).toFixed(2)"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Selling Price</span>
                        <p class="font-medium" x-text="'$' + (inventory.find(i => i.medicine_id === selectedMedicine)?.unit_price ?? 0).toFixed(2)"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Line Total</span>
                        <p class="font-semibold text-primary-600"
                           x-text="'$' + ((inventory.find(i => i.medicine_id === selectedMedicine)?.unit_price ?? 0) * quantity).toFixed(2)">
                        </p>
                    </div>
                </div>
            </div>
        </template>

        <!-- Cart summary -->
        <div x-show="cart.length > 0" class="p-4 border rounded-lg bg-white dark:bg-gray-800 shadow">
            <h3 class="text-lg font-semibold mb-4">Cart Summary</h3>
            <div class="space-y-2">
                <template x-for="item in cart" :key="item.medicine_id">
                    <div class="flex items-center justify-between border-b py-2">
                        <div>
                            <p class="font-medium" x-text="item.medicine_name"></p>
                            <p class="text-sm text-gray-500">
                                Qty: <span x-text="item.quantity"></span> × 
                                $<span x-text="item.unit_price.toFixed(2)"></span>
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <p class="font-semibold text-primary-600" x-text="'$' + item.line_total.toFixed(2)"></p>
                            <button @click="removeFromCart(item.medicine_id)" class="text-red-500 hover:text-red-700 text-sm">✕</button>
                        </div>
                    </div>
                </template>
            </div>
            <div class="mt-4 text-right">
                <span class="text-gray-600">Total:</span>
                <span class="font-bold text-xl text-primary-700" x-text="'$' + cartTotal().toFixed(2)"></span>
            </div>
        </div>

    </div>
</div>
