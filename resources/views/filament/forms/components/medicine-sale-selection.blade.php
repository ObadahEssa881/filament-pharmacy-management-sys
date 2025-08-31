<div x-data="medicineSaleSelection({
    pharmacyId: @js($pharmacyId)
})" x-init="init()">
    <div class="mt-4 space-y-6">
        <!-- Medicine selection form -->
        <div class="p-4 border rounded-lg bg-white dark:bg-gray-800 shadow">
            <h3 class="text-lg font-semibold mb-4">Select Medicine</h3>
            <template x-if="loading">
                <p class="text-gray-500">Loading inventory...</p>
            </template>
            <template x-if="!loading && inventory.length === 0">
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1 md:flex md:justify-between">
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                No inventory found in this pharmacy.
                            </p>
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="!loading && inventory.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Medicine</label>
                        <select 
                            x-model.number="selectedMedicine"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white"
                            :disabled="!pharmacyId"
                        >
                            <option value="" disabled>Select...</option>
                            <template x-for="item in inventory" :key="item.id">
                                <option :value="item.medicine_id" 
                                        x-text="`${item.medicine.name} - ${item.medicine.titer} (Stock: ${item.quantity})`"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <input type="hidden" name="items_json" x-ref="itemsJson" wire:model="data.items_json">
                            <input 
                                type="number" 
                                x-model.number="quantity" 
                                min="1"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white"
                                :max="selectedMedicine ? inventory.find(i => i.medicine_id === selectedMedicine)?.quantity : 1"
                                :disabled="!selectedMedicine"
                            />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500 sm:text-sm" x-show="selectedMedicine">
                                    Max: <span x-text="inventory.find(i => i.medicine_id === selectedMedicine)?.quantity"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-end">
                       <button type="button" 
                            @click="addToCart()" 
                            :disabled="!selectedMedicine || quantity <= 0 || loading || !inventoryLoaded"
                            class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md shadow disabled:opacity-50">
                            <span x-show="!loading">Add to Cart</span>
                            <span x-show="loading">Loading...</span>
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
                        <span class="text-sm text-gray-500">Titer</span>
                        <p class="font-medium" x-text="inventory.find(i => i.medicine_id === selectedMedicine)?.medicine.titer"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Unit Price</span>
                        <p class="font-medium" x-text="'$' + (inventory.find(i => i.medicine_id === selectedMedicine)?.selling_price ?? 0).toFixed(2)"></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Line Total</span>
                        <p class="font-semibold text-primary-600"
                           x-text="'$' + ((inventory.find(i => i.medicine_id === selectedMedicine)?.selling_price ?? 0) * quantity).toFixed(2)">
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

<script>
function medicineSaleSelection({ pharmacyId }) {
    return {
        pharmacyId: pharmacyId,
        inventory: [], // Initialize as empty array
        cart: [],
        selectedMedicine: null,
        quantity: 1,
        loading: true, // Start in loading state
        inventoryLoaded: false, // Track if inventory is loaded
        
        init() {
            // Ensure inventory is always an array
            this.inventory = this.inventory || [];
            
            if (this.pharmacyId) {
                this.loadInventory();
            } else {
                this.getPharmacyId();
            }
        },
        
        getPharmacyId() {
            fetch('/api/pharmacy/id', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                this.pharmacyId = data.pharmacy_id;
                if (this.pharmacyId) {
                    this.loadInventory();
                } else {
                    this.loading = false;
                    window.Livewire.dispatch('notify', {
                        title: 'Error',
                        description: 'Pharmacy ID not found',
                        variant: 'danger'
                    });
                }
            })
            .catch(error => {
                console.error('Error getting pharmacy ID:', error);
                this.loading = false;
                window.Livewire.dispatch('notify', {
                    title: 'Error',
                    description: 'Failed to get pharmacy ID',
                    variant: 'danger'
                });
            });
        },
        
        loadInventory() {
            this.loading = true;
            this.inventoryLoaded = false;
            
            // Ensure inventory is an array before loading
            this.inventory = [];
            
            fetch(`/api/pharmacy/inventory?pharmacy_id=${this.pharmacyId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Ensure data is an array
                this.inventory = Array.isArray(data) ? data : [];
                this.loading = false;
                this.inventoryLoaded = true;
            })
            .catch(error => {
                console.error('Error loading inventory:', error);
                this.loading = false;
                this.inventory = []; // Ensure it's an empty array on error
                
                window.Livewire.dispatch('notify', {
                    title: 'Error',
                    description: 'Failed to load pharmacy inventory',
                    variant: 'danger'
                });
            });
        },
        
        addToCart() {
            // Multiple safety checks
            if (!Array.isArray(this.inventory)) {
                console.error('Inventory is not an array:', this.inventory);
                this.inventory = []; // Reset to empty array
                window.Livewire.dispatch('notify', {
                    title: 'Error',
                    description: 'Inventory data is corrupted. Please refresh the page.',
                    variant: 'danger'
                });
                return;
            }
            
            if (this.inventory.length === 0) {
                window.Livewire.dispatch('notify', {
                    title: 'Error',
                    description: 'Inventory not loaded. Please try again.',
                    variant: 'danger'
                });
                return;
            }
            
            if (!this.selectedMedicine || this.quantity <= 0) {
                window.Livewire.dispatch('notify', {
                    title: 'Error',
                    description: 'Please select a medicine and quantity',
                    variant: 'danger'
                });
                return;
            }
            
            const selectedId = parseInt(this.selectedMedicine, 10);
            
            // Additional safety check before using find
            if (!this.inventory || this.inventory.length === 0) {
                window.Livewire.dispatch('notify', {
                    title: 'Error',
                    description: 'Inventory not available',
                    variant: 'danger'
                });
                return;
            }
            
            const medicine = this.inventory.find(i => i.medicine_id === selectedId);
            
            if (!medicine) {
                window.Livewire.dispatch('notify', {
                    title: 'Error',
                    description: 'Selected medicine not found in inventory',
                    variant: 'danger'
                });
                return;
            }
            
            // Merge if already in cart
            const existing = this.cart.find(i => i.medicine_id === selectedId);
            if (existing) {
                existing.quantity += this.quantity;
                existing.line_total = existing.quantity * existing.unit_price;
            } else {
                this.cart.push({
                    medicine_id: medicine.medicine_id,
                    medicine_name: medicine.medicine.name,
                    cost_price: medicine.cost_price || 0,
                    unit_price: medicine.selling_price,
                    quantity: this.quantity,
                    line_total: this.quantity * medicine.selling_price,
                });
            }
            
            // Update form fields directly
            this.updateFormFields();
            
            // Reset selection
            this.selectedMedicine = null;
            this.quantity = 1;
            
            // Show success notification
            window.Livewire.dispatch('notify', {
                title: 'Success',
                description: `${medicine.medicine.name} added to cart`,
                variant: 'success'
            });
        },
        
       updateFormFields() {
            const totalDisplay = document.querySelector('input[name="total_amount_display"]');
            if (totalDisplay) {
                totalDisplay.value = this.cartTotal().toFixed(2);
                totalDisplay.dispatchEvent(new Event('input', { bubbles: true }));
            }

            const totalAmount = document.querySelector('input[name="total_amount"]');
            if (totalAmount) {
                totalAmount.value = this.cartTotal().toFixed(2);
                totalAmount.dispatchEvent(new Event('input', { bubbles: true }));
            }

            // Push full cart to hidden JSON field
           const itemsJson = this.$refs.itemsJson;
            if (itemsJson) {
                itemsJson.value = JSON.stringify(this.cart);
                itemsJson.dispatchEvent(new Event('input', { bubbles: true }));
                itemsJson.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },

        
        removeFromCart(id) {
            this.cart = this.cart.filter(i => i.medicine_id !== id);
            this.updateFormFields();
        },
        
        cartTotal() {
            return this.cart.reduce((sum, i) => sum + (i.line_total || 0), 0);
        }
    }
}
</script>