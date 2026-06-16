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
.header-table { width: 100%; border-bottom: 2px solid #6366f1; margin-bottom: 16px; padding-bottom: 12px; }
.header-table td { border: none; padding: 0; vertical-align: top; }
.company { font-size: 16px; font-weight: bold; color: #6366f1; }
.main-table { width: 100%; border-collapse: collapse; font-size: 9px; table-layout: fixed; word-wrap: break-word; }
.main-table th { background: #f5f3ff; padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; border-bottom: 1px solid #ddd6fe; }
.main-table td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.footer { margin-top: 16px; font-size: 8px; color: #999; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 8px; }
.badge { padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; }
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
            <div style="font-weight:bold;font-size:13px">LAPORAN BARANG MASUK</div>
            <div style="font-size:9px;color:#555">
                {{ $warehouse ? 'Gudang: ' . $warehouse->name : 'Semua Gudang' }}
            </div>
            @if($request->date_from || $request->date_to)
            <div style="font-size:8px;color:#666">
                Periode: {{ $request->date_from ? \Carbon\Carbon::parse($request->date_from)->format('d/m/Y') : '...' }} - {{ $request->date_to ? \Carbon\Carbon::parse($request->date_to)->format('d/m/Y') : '...' }}
            </div>
            @endif
            <div style="font-size:8px;color:#999;margin-top:2px">Dicetak: {{ now()->format('d/m/Y H:i:s') }}</div>
        </td>
    </tr>
</table>

<table class="main-table">
    <thead>
        <tr>
            <th style="width: 15%">No. Ref</th>
            <th style="width: 15%">Gudang</th>
            <th style="width: 15%">Supplier</th>
            <th class="text-center" style="width: 10%">Status</th>
            <th class="text-right" style="width: 10%">Items</th>
            <th class="text-right" style="width: 10%">Total Qty</th>
            <th style="width: 15%">Tanggal</th>
            <th style="width: 10%">Oleh</th>
        </tr>
    </thead>
    <tbody>
        @foreach($inbounds as $inbound)
        @php
            $statusLabel = [
                'draft'    => 'DRAFT',
                'received' => 'DITERIMA',
            ];
            $statusColor = [
                'draft'    => 'background:#f3f4f6; color:#4b5563;',
                'received' => 'background:#dcfce7; color:#15803d;',
            ];
        @endphp
        <tr>
            <td style="font-family:monospace;color:#4f46e5;font-weight:bold">{{ $inbound->reference_no }}</td>
            <td>{{ $inbound?->warehouse?->name }}</td>
            <td>{{ $inbound->supplier_name ?? '-' }}</td>
            <td class="text-center">
                <span class="badge" style="{{ $statusColor[$inbound->status] ?? '' }}">
                    {{ $statusLabel[$inbound->status] ?? $inbound->status }}
                </span>
            </td>
            <td class="text-right">{{ number_format($inbound->items->count()) }}</td>
            <td class="text-right" style="font-weight:bold">{{ number_format($inbound->items->sum('qty')) }}</td>
            <td>{{ $inbound->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $inbound->creator->name }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table style="width:100%;margin-top:20px;border-top:1px solid #eee;padding-top:10px">
    <tr>
        <td>
            Total Penerimaan<strong>{{ number_format(count($inbounds)) }}</strong>
        </td>
        <td style="text-align:right">
            Total Qty Barang<strong>{{ number_format($inbounds->sum(fn($i) => $i->items->sum('qty'))) }}</strong>
        </td>
    </tr>
</table>

<div class="footer">SevenKey ERP — Laporan dibuat otomatis pada {{ now()->format('d F Y, H:i:s') }}</div>
</body>
</html>
