<div style="display: none;">
    @push('scripts')
    <script>
        document.addEventListener('livewire:load', function() {
            console.log('[WarehouseInventoryHandler] Livewire loaded');
            
            Livewire.on('addMedicineToCart', function(item) {
                console.log('[WarehouseInventoryHandler] Adding medicine to cart:', item);
                
                // Instead of trying to set data directly, emit an event for the page to handle
                Livewire.emit('medicineAdded', item);
            });
        });
    </script>
    @endpush
</div>