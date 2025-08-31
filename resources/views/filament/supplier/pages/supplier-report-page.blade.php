{{-- resources/views/filament/supplier/pages/supplier-report-page.blade.php --}}
<x-filament-panels::page>
    {{-- Filters --}}
    <div class="mb-6">
        {{ $this->form }}
    </div>

        {{-- KPIs (responsive, clean spans) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-8 gap-4 mb-8">
        <x-filament::card>
            <div class="text-sm text-gray-500">Total Orders</div>
            <div class="text-2xl font-semibold">{{ $reportData['kpis']['totalOrders'] ?? 0 }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500">Delivered</div>
            <div class="text-2xl font-semibold">{{ $reportData['kpis']['delivered'] ?? 0 }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500">Cancelled</div>
            <div class="text-2xl font-semibold">{{ $reportData['kpis']['cancelled'] ?? 0 }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500">Fulfilment Rate</div>
            <div class="text-2xl font-semibold">{{ $reportData['kpis']['fulfilmentRate'] ?? 0 }}%</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500">Total Amount</div>
            <div class="text-2xl font-semibold">${{ number_format($reportData['kpis']['totalAmount'] ?? 0, 2) }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500">Avg Order Value</div>
            <div class="text-2xl font-semibold">${{ number_format($reportData['kpis']['avgOrderValue'] ?? 0, 2) }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500">Unique Suppliers</div>
            <div class="text-2xl font-semibold">{{ $reportData['kpis']['uniqueSuppliers'] ?? 0 }}</div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-sm text-gray-500">Avg Lead Time</div>
            <div class="text-2xl font-semibold">{{ $reportData['kpis']['avgLeadTime'] ?? 0 }} days</div>
        </x-filament::card>
    </div>


    {{-- Chart --}}
    <x-filament::card>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Orders & Amount over time</h3>
            <div class="text-xs text-gray-500">
                Grouped by: {{ strtoupper($this->filters['group_by'] ?? 'day') }}
            </div>
        </div>

        <canvas id="ordersChart" height="120"></canvas>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                function renderOrdersChart(labels, orders, amount) {
                    const ctx = document.getElementById('ordersChart')?.getContext('2d');
                    if (!ctx) return;

                    if (window.__ordersChart) window.__ordersChart.destroy();

                    window.__ordersChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [
                                {
                                    label: 'Orders',
                                    data: orders,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59,130,246,0.1)',
                                    borderWidth: 2,
                                    tension: 0.35,
                                    yAxisID: 'y',
                                },
                                {
                                    label: 'Amount ($)',
                                    data: amount,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16,185,129,0.1)',
                                    borderWidth: 2,
                                    tension: 0.35,
                                    yAxisID: 'y1',
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            interaction: { mode: 'index', intersect: false },
                            scales: {
                                y: { beginAtZero: true, title: { display: true, text: 'Orders' } },
                                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Amount ($)' } },
                            },
                        }
                    });
                }

                // Initial render
                document.addEventListener('DOMContentLoaded', () => {
                    renderOrdersChart(
                        @json($reportData['timeseries']['labels'] ?? []),
                        @json($reportData['timeseries']['orders'] ?? []),
                        @json($reportData['timeseries']['amount'] ?? []),
                    );
                });

                // Re-render on Livewire event
                window.addEventListener('report-updated', () => {
                    renderOrdersChart(
                        @json($reportData['timeseries']['labels'] ?? []),
                        @json($reportData['timeseries']['orders'] ?? []),
                        @json($reportData['timeseries']['amount'] ?? []),
                    );
                });
            </script>
        @endpush

    </x-filament::card>

    <x-filament::card class="mt-6">
        <h3 class="text-lg font-semibold mb-4">Summary (by period)</h3>
        <div class="overflow-x-auto">
            <table class="w-full table-auto text-sm border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr class="text-left text-gray-600 dark:text-gray-300">
                        <th class="py-3 px-4">Period</th>
                        <th class="py-3 px-4 text-right">Orders</th>
                        <th class="py-3 px-4 text-right">Delivered</th>
                        <th class="py-3 px-4 text-right">Cancelled</th>
                        <th class="py-3 px-4 text-right">Fulfilment Rate</th>
                        <th class="py-3 px-4 text-right">Amount ($)</th>
                        <th class="py-3 px-4 text-right">Avg Order Value ($)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalOrders = 0;
                        $totalDelivered = 0;
                        $totalCancelled = 0;
                        $totalAmount = 0;
                    @endphp

                    @forelse(($reportData['table'] ?? []) as $row)
                        @php
                            $orders = $row['orders'] ?? 0;
                            $delivered = $row['delivered'] ?? 0;
                            $cancelled = $row['cancelled'] ?? 0;
                            $amount = $row['amount'] ?? 0;

                            $totalOrders += $orders;
                            $totalDelivered += $delivered;
                            $totalCancelled += $cancelled;
                            $totalAmount += $amount;
                        @endphp
                        <tr class="border-t border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <td class="py-2 px-4 font-medium">{{ $row['bucket'] }}</td>
                            <td class="py-2 px-4 text-right">{{ $orders }}</td>
                            <td class="py-2 px-4 text-right">{{ $delivered }}</td>
                            <td class="py-2 px-4 text-right">{{ $cancelled }}</td>
                            <td class="py-2 px-4 text-right">
                                {{ $orders > 0 ? round(($delivered / $orders) * 100, 2) : 0 }}%
                            </td>
                            <td class="py-2 px-4 text-right">${{ number_format($amount, 2) }}</td>
                            <td class="py-2 px-4 text-right">
                                ${{ $orders > 0 ? number_format($amount / $orders, 2) : '0.00' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-4 px-4 text-gray-500 text-center" colspan="7">
                                No data for selected filters.
                            </td>
                        </tr>
                    @endforelse

                    {{-- Totals row --}}
                    @if(!empty($reportData['table']))
                        <tr class="bg-gray-100 dark:bg-white/10 font-semibold border-t border-gray-300 dark:border-white/20">
                            <td class="py-3 px-4 text-right">Total</td>
                            <td class="py-3 px-4 text-right">{{ $totalOrders }}</td>
                            <td class="py-3 px-4 text-right">{{ $totalDelivered }}</td>
                            <td class="py-3 px-4 text-right">{{ $totalCancelled }}</td>
                            <td class="py-3 px-4 text-right">
                                {{ $totalOrders > 0 ? round(($totalDelivered / $totalOrders) * 100, 2) : 0 }}%
                            </td>
                            <td class="py-3 px-4 text-right">${{ number_format($totalAmount, 2) }}</td>
                            <td class="py-3 px-4 text-right">
                                ${{ $totalOrders > 0 ? number_format($totalAmount / $totalOrders, 2) : '0.00' }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </x-filament::card>



</x-filament-panels::page>
