<x-filament::page>
    <x-filament::card class="mb-6">
        <form wire:submit.prevent="applyFilters">
            {{ $this->form }}
        </form>
    </x-filament::card>

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        @foreach($reportData['kpis'] as $label => $value)
            <x-filament::card>
                <div class="text-sm text-gray-500">{{ ucfirst($label) }}</div>
                <div class="text-xl font-bold">
                    {{ is_numeric($value) ? number_format($value, 2) : $value }}
                </div>
            </x-filament::card>
        @endforeach
    </div>

    {{-- SALES OVER TIME --}}
    <x-filament::card class="mt-6">
        <h3 class="text-lg font-semibold mb-4">Sales Over Time</h3>
        <div class="h-96">
        <canvas id="salesChart" wire:ignore></canvas>
        </div>
    </x-filament::card>
   

    {{-- TOP MEDICINES --}}
    <x-filament::card class="mt-6">
        <h3 class="text-lg font-semibold mb-4">Top Selling Medicines</h3>
        <div class="h-96">
        <canvas id="topMedicinesChart" wire:ignore></canvas>
        </div>
    </x-filament::card>

    {{-- SUPPLIER SPEND --}}
    <x-filament::card class="w-64 h-64 mt-6" >
        <h3 class="text-lg font-semibold mb-4">Purchases by Supplier</h3>
            <div class="flex justify-center">
                <canvas id="supplierChart" wire:ignore style="width:400px; height:400px;"></canvas>
            </div>

    </x-filament::card>

    <div class="mt-6">
        <x-filament::button wire:click="exportExcel" color="primary" icon="heroicon-o-arrow-down-tray">
            Export Excel
        </x-filament::button>
    </div>

  {{-- SINGLE UPDATABLE DATA ELEMENT (Livewire will update its attributes) --}}
    <div id="chart-data"
        data-sales='@json(["labels" => $reportData["salesOverTime"]->pluck("period"), "data" => $reportData["salesOverTime"]->pluck("total")])'
        data-top='@json(["labels" => $reportData["topMedicines"]->pluck("medicine.name"), "data" => $reportData["topMedicines"]->pluck("qty")])'
        data-supplier='@json(["labels" => collect($reportData["supplierSpend"])->pluck("name"), "data" => $reportData["supplierSpend"]->pluck("total")])'
    ></div>

    {{-- DATA BLOBS (Livewire updates these when filters change) --}}
    <script type="application/json" id="sales-data">
        @json([
            'labels' => $reportData['salesOverTime']->pluck('period')->values(),
            'data'   => $reportData['salesOverTime']->pluck('total')->values(),
        ])
    </script>

    <script type="application/json" id="top-medicines-data">
        @json([
            'labels' => $reportData['topMedicines']->pluck('medicine.name')->values(),
            'data'   => $reportData['topMedicines']->pluck('qty')->values(),
        ])
    </script>

    <script type="application/json" id="supplier-spend-data">
        @json([
            'labels' => collect($reportData['supplierSpend'])->pluck('name')->values(),
            'data'   => $reportData['supplierSpend']->pluck('total')->values(),
        ])
    </script>

{{-- CHART.JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function () {
        // Chart instances (kept global in closure)
        let salesChart = null, topMedicinesChart = null, supplierChart = null;

        // ---- Utilities ----
        function safeParseJSON(text) {
            try {
                return JSON.parse(text);
            } catch (e) {
                // sometimes Livewire will HTML-escape attributes; using fallback
                console.warn('safeParseJSON failed', e);
                return null;
            }
        }

        // Accept array or object keyed shape -> convert to array
        function normalizeToArray(x) {
            if (!x && x !== 0) return [];
            if (Array.isArray(x)) return x;
            if (typeof x === 'object') return Object.values(x);
            return [x];
        }

        function readScriptJson(id) {
            const el = document.getElementById(id);
            if (!el) return { labels: [], data: [] };

            // prefer .textContent (the JSON script block)
            const raw = (el.textContent || el.innerText || '').trim();
            if (!raw) return { labels: [], data: [] };

            const parsed = safeParseJSON(raw);
            if (!parsed) return { labels: [], data: [] };

            const labels = normalizeToArray(parsed.labels || []);
            const data = normalizeToArray(parsed.data || []).map(v => {
                // coerce numeric strings to numbers when possible
                if (typeof v === 'string' && v !== '' && !Number.isNaN(Number(v))) return Number(v);
                return v;
            });

            return { labels, data };
        }

        function setCanvasFullSize(canvasId) {
            const c = document.getElementById(canvasId);
            if (!c) return;
            c.style.width = '100%';
            c.style.height = '100%';
            c.style.display = 'block';
        }

        // ---- Renderers ----
        function renderSalesChart() {
            const data = readScriptJson('sales-data');
            const canvas = document.getElementById('salesChart');
            if (!canvas) return;
            setCanvasFullSize('salesChart');

            const ctx = canvas.getContext('2d');
            if (salesChart) { try { salesChart.destroy(); } catch(e){/*ignore*/} }

            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Sales ($)',
                        data: data.data,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.25)',
                        fill: true,
                        tension: 0.25,
                        pointRadius: 2,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true } },
                    scales: {
                        x: { ticks: { autoSkip: true, maxRotation: 45, minRotation: 0 } },
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        function renderTopMedicinesChart() {
            const data = readScriptJson('top-medicines-data');
            const canvas = document.getElementById('topMedicinesChart');
            if (!canvas) return;
            setCanvasFullSize('topMedicinesChart');

            const ctx = canvas.getContext('2d');
            if (topMedicinesChart) { try { topMedicinesChart.destroy(); } catch(e){/*ignore*/} }

            topMedicinesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Quantity Sold',
                        data: data.data,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        function renderSupplierChart() {
            const data = readScriptJson('supplier-spend-data');
            const canvas = document.getElementById('supplierChart');
            if (!canvas) return;
            // setCanvasFullSize('supplierChart');

            const ctx = canvas.getContext('2d');
            if (supplierChart) { try { supplierChart.destroy(); } catch(e){/*ignore*/} }

            supplierChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.data,
                        backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        function renderAllCharts() {
            // Render in safe order
            renderSalesChart();
            renderTopMedicinesChart();
            renderSupplierChart();
        }

        // ---- Watchers ----
        // 1) Run on initial load
        document.addEventListener('DOMContentLoaded', renderAllCharts);
        // 2) Run on Livewire lifecycle events (v3 + compatibility)
        window.addEventListener('livewire:load', renderAllCharts);
        window.addEventListener('livewire:update', renderAllCharts);
        window.addEventListener('livewire:navigated', renderAllCharts);
        if (window.Livewire && Livewire.hook) {
            Livewire.hook('message.processed', renderAllCharts);
        }

        // 3) MutationObserver on the JSON script nodes so we react immediately when Livewire replaces them.
        ['sales-data','top-medicines-data','supplier-spend-data'].forEach(id => {
            const node = document.getElementById(id);
            if (!node) return;
            const mo = new MutationObserver(() => {
                // tiny debounce to avoid double renders during fast updates
                clearTimeout(window.__chartRenderTimeout);
                window.__chartRenderTimeout = setTimeout(renderAllCharts, 50);
            });
            mo.observe(node, { childList: true, characterData: true, subtree: true });
        });

        // Export the render function to window for debug if needed
        window.__renderDashboardCharts = renderAllCharts;
    })();
    </script>





    {{-- Sales Records --}}
    <x-filament::card class="mt-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Sales Records</h3>
            <x-filament::button wire:click="exportSales" icon="heroicon-o-arrow-down-tray">
                Export Excel
            </x-filament::button>
        </div>
        <div class="overflow-x-auto w-full">
            <table class="table-auto w-full text-sm border-collapse">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr class="text-left border-b">
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Customer</th>
                        <th class="px-4 py-2">Items</th>
                        <th class="px-4 py-2">Total ($)</th>
                        <th class="px-4 py-2">Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['salesRecords'] as $sale)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $sale->sale_date->toDateString() }}</td>
                            <td class="px-4 py-2">{{ $sale->customer_name }}</td>
                            <td class="px-4 py-2">{{ $sale->saleitems_count }}</td>
                            <td class="px-4 py-2">${{ number_format($sale->total_amount, 2) }}</td>
                            <td class="px-4 py-2">{{ $sale->payment_mode }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="font-bold bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white">
                    <tr>
                        <td colspan="2" class="px-4 py-2">Totals</td>
                        <td class="px-4 py-2">{{ $reportData['salesRecords']->sum('saleitems_count') }}</td>
                        <td class="px-4 py-2">${{ number_format($reportData['salesRecords']->sum('total_amount'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::card>

    {{-- Purchase Records --}}
    <x-filament::card class="mt-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Purchase Records</h3>
            <x-filament::button wire:click="exportPurchases" icon="heroicon-o-arrow-down-tray">
                Export Excel
            </x-filament::button>
        </div>
        <div class="overflow-x-auto w-full">
            <table class="table-auto w-full text-sm border-collapse">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr class="text-left border-b">
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Supplier</th>
                        <th class="px-4 py-2">Items</th>
                        <th class="px-4 py-2">Total ($)</th>
                        <th class="px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['purchaseRecords'] as $po)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $po->order_date->toDateString() }}</td>
                            <td class="px-4 py-2">{{ $po->supplier?->name }}</td>
                            <td class="px-4 py-2">{{ $po->purchaseorderitems_count }}</td>
                            <td class="px-4 py-2">
                                ${{ number_format($po->purchaseorderitems->sum(fn($i) => $i->quantity * $i->unit_price), 2) }}
                            </td>
                            <td class="px-4 py-2">{{ $po->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="font-bold bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white">
                    <tr>
                        <td colspan="2" class="px-4 py-2">Totals</td>
                        <td class="px-4 py-2">{{ $reportData['purchaseRecords']->sum('purchaseorderitems_count') }}</td>
                        <td class="px-4 py-2">
                            ${{ number_format($reportData['purchaseRecords']->flatMap->purchaseorderitems->sum(fn($i) => $i->quantity * $i->unit_price), 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::card>

</x-filament::page>
