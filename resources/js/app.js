

document.addEventListener("alpine:init", () => {
    Alpine.data("purchaseOrderForm", () => ({
        init() {
            // Listen for items to be added from warehouse inventory
            Livewire.on("addItemsToOrder", (items) => {
                let currentItems = this.$wire.get("data.items") || [];

                items.forEach((item) => {
                    // Check if item already exists
                    const exists = currentItems.some(
                        (i) => i.medicine_id == item.medicine_id
                    );
                    if (!exists) {
                        currentItems.push({
                            medicine_id: item.medicine_id,
                            medicine_name: item.medicine_name,
                            quantity: item.quantity,
                            unit_price: item.unit_price,
                        });
                    }
                });

                this.$wire.set("data.items", currentItems);
            });
        },
    }));
});
