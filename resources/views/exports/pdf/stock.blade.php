<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
@page {
    margin-top: 1.5cm;
    margin-right: 1.5cm;
    margin-bottom: 2.5cm;
    margin-left: 1.5cm;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; padding: 12px 14px; }
.header-table { width: 100%; border-bottom: 2px solid #16a34a; margin-bottom: 16px; padding-bottom: 12px; }
.header-table td { border: none; padding: 0; vertical-align: top; }
.company { font-size: 16px; font-weight: bold; color: #16a34a; }
.main-table { width: 100%; border-collapse: collapse; font-size: 9px; table-layout: fixed; word-wrap: break-word; }
.main-table th { background: #dcfce7; padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; border-bottom: 1px solid #bbf7d0; }
.main-table td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }
.text-right { text-align: right; }
.footer { margin-top: 16px; font-size: 8px; color: #999; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 8px; }
</style>
</head>
<body>

<table class="header-table">
    <tr>
        <td>
            <div class="company">SevenKey ERP</div>
            <div style="color:#666;font-size:9px;margin-top:2px">Fashion Retail Management System</div>
        </td>
        <td style="text-align:right">
            <div style="font-weight:bold;font-size:13px">LAPORAN STOK</div>
            <div style="font-size:9px;color:#555">
                {{ $locationType === 'warehouse' ? 'Gudang' : 'Toko' }}{{ $location ? ': ' . $location->name : ' (Semua)' }}
            </div>
            @if(!empty($searchProduct))
            <div style="font-size:8px;color:#555">Produk: <strong>{{ $searchProduct }}</strong></div>
            @endif
            @if(!empty($filterBrand))
            <div style="font-size:8px;color:#555">Brand: <strong>{{ $filterBrand }}</strong></div>
            @endif
            @if(!empty($filterColor))
            <div style="font-size:8px;color:#555">Warna: <strong>{{ $filterColor }}</strong></div>
            @endif
            @if(!empty($filterSize))
            <div style="font-size:8px;color:#555">Ukuran: <strong>{{ $filterSize }}</strong></div>
            @endif
            <div style="font-size:8px;color:#999;margin-top:2px">Dicetak: {{ now()->format('d/m/Y H:i:s') }}</div>
        </td>
    </tr>
</table>

<table class="main-table">
    <thead>
        <tr>
            <th style="width: 5%">#</th>
            <th style="width: 20%">SKU</th>
            <th style="width: 30%">Produk</th>
            <th style="width: 15%">Brand</th>
            <th style="width: 10%">Warna</th>
            <th style="width: 10%">Ukuran</th>
            <th class="text-right" style="width: 10%">Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stocks as $i => $stock)
        <tr>
            <td style="color:#999">{{ $i+1 }}</td>
            <td style="font-family:monospace;color:#16a34a;font-weight:bold">{{ $stock->variant??->sku ?? '-' }}</td>
            <td>{{ $stock->variant?->product?->name ?? 'Produk Terhapus' }}</td>
            <td style="color:#555">{{ $stock->variant?->product?->brand?->name ?? '-' }}</td>
            <td>{{ $stock->variant?->color?->name ?? '-' }}</td>
            <td>{{ $stock->variant?->size?->name ?? '-' }}</td>
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

<div class="footer">SevenKey ERP — Laporan dibuat otomatis pada {{ now()->format('d F Y, H:i:s') }}</div>
</body>
</html>
