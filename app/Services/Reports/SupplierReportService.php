<?php

namespace App\Services\Reports;

use App\Models\Purchaseorder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SupplierReportService
{
    public function run(array $filters): array
    {
        $start     = $filters['start'] ?? now()->startOfMonth()->toDateString();
        $end       = $filters['end']   ?? now()->endOfMonth()->toDateString();
        $pharmacy  = $filters['pharmacy_id'] ?? null;
        $supplier  = $filters['supplier_id'] ?? null;
        $warehouse = $filters['warehouse_id'] ?? null;
        $statuses  = $filters['statuses'] ?? [];
        $groupBy   = in_array(($filters['group_by'] ?? 'day'), ['day', 'week', 'month'], true)
            ? $filters['group_by']
            : 'day';

        // Base scope (assuming you have local scopes for these)
        $base = Purchaseorder::query()
            ->dateBetween($start, $end)
            ->pharmacy($pharmacy)
            ->supplier($supplier)
            ->warehouse($warehouse)
            ->statuses($statuses);

        // === KPIs ===

        $totalOrders   = (clone $base)->count();
        $delivered     = (clone $base)->where('status', 'DELIVERED')->count();
        $cancelled     = (clone $base)->where('status', 'CANCELLED')->count();

        // Amount per order: prefer invoice sum; fall back to items sum.
        // Use correlated subqueries so we don't duplicate rows by joins.
        $totalAmount = (clone $base)
            ->selectRaw("
                SUM(
                    COALESCE(
                        (SELECT SUM(i.total_amount)
                         FROM invoice i
                         WHERE i.order_id = purchaseorder.id),
                        (SELECT SUM(poi.quantity * poi.unit_price)
                         FROM purchaseorderitem poi
                         WHERE poi.order_id = purchaseorder.id),
                        0
                    )
                ) AS agg_total
            ")
            ->value('agg_total') ?? 0;

        $avgOrderValue   = $totalOrders > 0 ? round((float)$totalAmount / $totalOrders, 2) : 0.0;
        $uniqueSuppliers = (clone $base)->distinct('supplier_id')->count('supplier_id');
        $fulfilmentRate  = $totalOrders > 0 ? round(($delivered / $totalOrders) * 100, 2) : 0.0;

        // Optional lead time in days
        $leadTimeDays = (clone $base)
            ->whereNotNull('delivery_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(DAY, order_date, delivery_date)) as avg_days')
            ->value('avg_days');
        $avgLeadTime = $leadTimeDays ? round($leadTimeDays, 2) : 0.0;

        // === Time series (grouped) ===
        // Build bucket formatter for SQL
        [$dateSql] = match ($groupBy) {
            'week'  => ["DATE_FORMAT(purchaseorder.order_date, '%x-%v')"], // ISO year-week
            'month' => ["DATE_FORMAT(purchaseorder.order_date, '%Y-%m')"],
            default => ["DATE(purchaseorder.order_date)"],
        };

        // Group by bucket and aggregate orders and amount with correlated subqueries
        $series = (clone $base)
            ->selectRaw("$dateSql AS bucket")
            ->selectRaw("COUNT(*) AS orders")
            ->selectRaw("
                SUM(
                    COALESCE(
                        (SELECT SUM(i.total_amount)
                         FROM invoice i
                         WHERE i.order_id = purchaseorder.id),
                        (SELECT SUM(poi.quantity * poi.unit_price)
                         FROM purchaseorderitem poi
                         WHERE poi.order_id = purchaseorder.id),
                        0
                    )
                ) AS amount
            ")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        // Fill missing buckets for charts/tables
        $filled = $this->fillSeries($series, $start, $end, $groupBy);

        return [
            'kpis' => [
                'totalOrders'     => (int)$totalOrders,
                'delivered'       => (int)$delivered,
                'cancelled'       => (int)$cancelled,
                'totalAmount'     => round((float)$totalAmount, 2),
                'avgOrderValue'   => (float)$avgOrderValue,
                'fulfilmentRate'  => (float)$fulfilmentRate,
                'uniqueSuppliers' => (int)$uniqueSuppliers,
                'avgLeadTime'     => (float)$avgLeadTime,
            ],
            'timeseries' => [
                'labels' => $filled['labels'],
                'orders' => $filled['orders'],
                'amount' => $filled['amount'],
            ],
            'table' => $filled['table'], // [{bucket, orders, amount}]
        ];
    }

    private function fillSeries(Collection $series, string $start, string $end, string $groupBy): array
    {
        $labels = [];
        $orders = [];
        $amount = [];
        $table  = [];

        $startC = Carbon::parse($start)->startOfDay();
        $endC   = Carbon::parse($end)->endOfDay();

        $cursor = match ($groupBy) {
            'week'  => (clone $startC)->startOfWeek(),   // Monday
            'month' => (clone $startC)->startOfMonth(),
            default => (clone $startC),
        };

        $step = fn(Carbon $c) => match ($groupBy) {
            'week'  => $c->addWeek(),
            'month' => $c->addMonth(),
            default => $c->addDay(),
        };

        $formatLabel = fn(Carbon $c) => match ($groupBy) {
            'week'  => $c->format('o-\WW'),  // e.g. 2025-W34
            'month' => $c->format('Y-m'),    // e.g. 2025-08
            default => $c->format('Y-m-d'),
        };

        $indexed = $series->keyBy('bucket');

        while ($cursor <= $endC) {
            $bucket = $formatLabel($cursor);
            $row    = $indexed->get($bucket);
            $o = $row?->orders ? (int)$row->orders : 0;
            $a = $row?->amount ? (float)$row->amount : 0.0;

            $labels[] = $bucket;
            $orders[] = $o;
            $amount[] = round($a, 2);
            $table[]  = ['bucket' => $bucket, 'orders' => $o, 'amount' => round($a, 2)];

            $step($cursor);
        }

        return compact('labels', 'orders', 'amount', 'table');
    }
}
