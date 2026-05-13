<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
.header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #3730a3; padding-bottom: 12px; margin-bottom: 16px; }
.company { font-size: 16px; font-weight: bold; color: #3730a3; }
.meta-row { display: flex; gap: 24px; margin-bottom: 12px; font-size: 9px; color: #555; }
.meta-item strong { color: #111; font-size: 10px; }
table { width: 100%; border-collapse: collapse; font-size: 9px; }
th { background: #e0e7ff; padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #c7d2fe; }
td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }
.text-right { text-align: right; }
.summary { background: #f9fafb; border-top: 2px solid #3730a3; padding: 10px 8px; display: flex; justify-content: flex-end; gap: 32px; margin-top: 4px; }
.summary-item label { font-size: 8px; text-transform: uppercase; color: #666; display: block; }
.summary-item strong { font-size: 13px; color: #3730a3; }
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
        <div style="font-weight:bold;font-size:13px">LAPORAN PENJUALAN</div>
        @if($store)
        <div style="font-size:9px;color:#555">Toko: {{ $store->name }}</div>
        @endif
        @if($request->date_from || $request->date_to)
        <div style="font-size:9px;color:#555">Periode: {{ $request->date_from ?? 'awal' }} s/d {{ $request->date_to ?? 'sekarang' }}</div>
        @endif
        <div style="font-size:8px;color:#999">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>No. Penjualan</th>
            <th>Toko</th>
            <th>Metode</th>
            <th>Kasir</th>
            <th class="text-right">Items</th>
            <th class="text-right">Subtotal</th>
            <th class="text-right">Diskon</th>
            <th class="text-right">Total</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $i => $sale)
        <tr style="background:#fcfcfc">
            <td style="color:#999">{{ $i+1 }}</td>
            <td style="font-family:monospace;font-weight:bold;color:#3730a3">{{ $sale->sale_no }}</td>
            <td>{{ $sale->store->name }}</td>
            <td>{{ $sale->paymentMethod?->name ?? '-' }}</td>
            <td>{{ $sale->creator?->name ?? '-' }}</td>
            <td class="text-right">{{ $sale->items->sum('qty') }}</td>
            <td class="text-right">{{ number_format($sale->subtotal, 0, ',', '.') }}</td>
            <td class="text-right" style="color:#dc2626">{{ $sale->discount_amount > 0 ? number_format($sale->discount_amount, 0, ',', '.') : '-' }}</td>
            <td class="text-right" style="font-weight:bold">{{ number_format($sale->total_amount, 0, ',', '.') }}</td>
            <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="9" style="padding: 0 8px 10px 8px; border-bottom: 2px solid #f3f4f6;">
                <table style="width:100%; border:none; background: #fff; border-left: 2px solid #e0e7ff;">
                    <thead>
                        <tr style="border-bottom: 1px solid #eee">
                            <th style="background:transparent; color:#666; font-size:7px; padding:2px 4px; width:40%">Produk</th>
                            <th style="background:transparent; color:#666; font-size:7px; padding:2px 4px; width:30%">SKU / Variant</th>
                            <th style="background:transparent; color:#666; font-size:7px; padding:2px 4px; text-align:center">Qty</th>
                            <th style="background:transparent; color:#666; font-size:7px; padding:2px 4px; text-align:right">Harga</th>
                            <th style="background:transparent; color:#666; font-size:7px; padding:2px 4px; text-align:right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr>
                            <td style="padding:2px 4px; border:none; font-size:8px">{{ $item->variant->product->name }}</td>
                            <td style="padding:2px 4px; border:none; font-size:7px; color:#666">{{ $item->variant->sku }} ({{ $item->variant->color->name }} / {{ $item->variant->size->name }})</td>
                            <td style="padding:2px 4px; border:none; font-size:8px; text-align:center">{{ $item->qty }}</td>
                            <td style="padding:2px 4px; border:none; font-size:8px; text-align:right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td style="padding:2px 4px; border:none; font-size:8px; text-align:right; font-weight:bold">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="summary">
    <div class="summary-item">
        <label>Total Transaksi</label>
        <strong>{{ number_format($totalOrders) }}</strong>
    </div>
    <div class="summary-item">
        <label>Total Pendapatan</label>
        <strong>Rp {{ number_format($totalSales, 0, ',', '.') }}</strong>
    </div>
</div>

<div class="footer">SevenKey ERP — Laporan dibuat otomatis pada {{ now()->format('d F Y H:i:s') }}</div>
</body>
</html>
