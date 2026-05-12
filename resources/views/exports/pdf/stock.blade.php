<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #16a34a; padding-bottom: 12px; margin-bottom: 16px; }
.company { font-size: 16px; font-weight: bold; color: #16a34a; }
table { width: 100%; border-collapse: collapse; font-size: 9px; }
th { background: #dcfce7; padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; border-bottom: 1px solid #bbf7d0; }
td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }
.text-right { text-align: right; }
.footer { margin-top: 16px; font-size: 8px; color: #999; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 8px; }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="company">SevenKey ERP</div>
        <div style="color:#666;font-size:9px;margin-top:2px">Fashion Retail Management System</div>
    </div>
    <div style="text-align:right">
        <div style="font-weight:bold;font-size:13px">LAPORAN STOK</div>
        <div style="font-size:9px;color:#555">
            {{ $locationType === 'warehouse' ? 'Gudang' : 'Toko' }}{{ $location ? ': ' . $location->name : ' (Semua)' }}
        </div>
        <div style="font-size:8px;color:#999">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>SKU</th>
            <th>Produk</th>
            <th>Brand</th>
            <th>Warna</th>
            <th>Ukuran</th>
            <th class="text-right">Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stocks as $i => $stock)
        <tr>
            <td style="color:#999">{{ $i+1 }}</td>
            <td style="font-family:monospace;color:#16a34a;font-weight:bold">{{ $stock->variant->sku }}</td>
            <td>{{ $stock->variant->product->name }}</td>
            <td style="color:#555">{{ $stock->variant->product->brand?->name ?? '-' }}</td>
            <td>{{ $stock->variant->color->name }}</td>
            <td>{{ $stock->variant->size->name }}</td>
            <td class="text-right" style="font-weight:bold">{{ number_format($stock->qty) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align:right;font-weight:bold;background:#f9fafb;border-top:2px solid #16a34a">TOTAL QTY</td>
            <td style="text-align:right;font-weight:bold;font-size:12px;color:#16a34a;background:#f9fafb;border-top:2px solid #16a34a">{{ number_format($totalQty) }}</td>
        </tr>
    </tfoot>
</table>

<div class="footer">SevenKey ERP — Laporan dibuat otomatis pada {{ now()->format('d F Y H:i:s') }}</div>
</body>
</html>
