<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        .summary-box { margin-top: 20px; padding: 10px; background: #f9f9f9; border: 1px solid #eee; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Penjualan POS</h2>
        <p>{{ $store->name }} | Periode: {{ $title }}</p>
    </div>

    <div class="summary-box">
        <table style="width: 100%">
            <tr>
                <td>Total Transaksi: <strong>{{ $summary['count'] }}</strong></td>
                <td>Total Barang: <strong>{{ $summary['total_items'] }}</strong></td>
                <td class="text-right">Total Pendapatan: <strong style="font-size: 16px; color: #4f46e5;">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No. Transaksi</th>
                <th>Waktu</th>
                <th>Metode</th>
                <th class="text-right">Items</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->sale_no }}</td>
                <td>{{ $sale->created_at->format('H:i') }}</td>
                <td>{{ $sale->paymentMethod->name }}</td>
                <td class="text-right">{{ $sale->items->sum('qty') }}</td>
                <td class="text-right">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>