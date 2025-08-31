<table>
    <thead>
        <tr>
            <th>KPI</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reportData['kpis'] as $kpi => $value)
            <tr>
                <td>{{ ucfirst($kpi) }}</td>
                <td>{{ is_numeric($value) ? number_format($value, 2) : $value }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>Medicine</th>
            <th>Quantity Sold</th>
            <th>Total Revenue</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reportData['topMedicines'] as $row)
            <tr>
                <td>{{ $row->medicine->name ?? 'N/A' }}</td>
                <td>{{ $row->qty }}</td>
                <td>{{ number_format($row->total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>Supplier</th>
            <th>Total Spend</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reportData['supplierSpend'] as $row)
            <tr>
                <td>{{ $row->purchaseorder->supplier->name ?? 'N/A' }}</td>
                <td>{{ number_format($row->total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
