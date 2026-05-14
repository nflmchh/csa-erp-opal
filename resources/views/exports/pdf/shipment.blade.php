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
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
.header-table { width: 100%; border-bottom: 2px solid #f97316; margin-bottom: 16px; padding-bottom: 12px; }
.header-table td { border: none; padding: 0; vertical-align: top; }
.company { font-size: 16px; font-weight: bold; color: #f97316; }
.main-table { width: 100%; border-collapse: collapse; font-size: 9px; table-layout: fixed; word-wrap: break-word; }
.main-table th { background: #ffedd5; padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #fed7aa; }
.main-table td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.summary-table { width: 100%; border-top: 2px solid #f97316; margin-top: 4px; background: #fff7ed; }
.summary-table td { padding: 10px 8px; border: none; text-align: right; font-size: 8px; color: #666; text-transform: uppercase; }
.summary-table strong { font-size: 13px; color: #f97316; display: block; }
.footer { margin-top: 16px; font-size: 8px; color: #999; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 8px; }
.badge-draft    { background: #f1f5f9; color: #475569; padding: 1px 5px; border-radius: 3px; font-size: 7px; font-weight: bold; }
.badge-sent     { background: #dbeafe; color: #1d4ed8; padding: 1px 5px; border-radius: 3px; font-size: 7px; font-weight: bold; }
.badge-received { background: #dcfce7; color: #15803d; padding: 1px 5px; border-radius: 3px; font-size: 7px; font-weight: bold; }
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
            <div style="font-weight:bold;font-size:13px">LAPORAN PENGIRIMAN</div>
            @if($warehouse)
            <div style="font-size:9px;color:#555">Gudang: {{ $warehouse->name }}</div>
            @endif
            @if($store)
            <div style="font-size:9px;color:#555">Toko: {{ $store->name }}</div>
            @endif
            @if($request->date_from || $request->date_to)
            <div style="font-size:9px;color:#555">Periode: {{ $request->date_from ?? 'awal' }} s/d {{ $request->date_to ?? 'sekarang' }}</div>
            @endif
            <div style="font-size:8px;color:#999">Dicetak: {{ now()->format('d/m/Y H:i:s') }}</div>
        </td>
    </tr>
</table>

<table class="main-table">
    <thead>
        <tr>
            <th style="width: 3%">#</th>
            <th style="width: 16%">No. SHP</th>
            <th style="width: 20%">Gudang</th>
            <th style="width: 20%">Toko Tujuan</th>
            <th class="text-center" style="width: 12%">Status</th>
            <th class="text-right" style="width: 9%">Qty</th>
            <th style="width: 20%">Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($shipments as $i => $shipment)
        @php
            $badgeClass = ['draft' => 'badge-draft', 'sent' => 'badge-sent', 'received' => 'badge-received'][$shipment->status] ?? 'badge-draft';
            $statusLabel = ['draft' => 'Draft', 'sent' => 'Terkirim', 'received' => 'Diterima'][$shipment->status] ?? $shipment->status;
        @endphp
        <tr style="background: {{ $i % 2 === 0 ? '#fff' : '#fafafa' }}">
            <td style="color:#999">{{ $i+1 }}</td>
            <td style="font-family:monospace;font-weight:bold;color:#f97316">{{ $shipment->shipment_no }}</td>
            <td>{{ $shipment->warehouse->name }}</td>
            <td>{{ $shipment->store->name }}</td>
            <td class="text-center"><span class="{{ $badgeClass }}">{{ $statusLabel }}</span></td>
            <td class="text-right" style="font-weight:bold">{{ number_format($shipment->items->sum('qty')) }}</td>
            <td>{{ $shipment->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="summary-table">
    <tr>
        <td>
            Total Pengiriman<strong>{{ number_format(count($shipments)) }}</strong>
        </td>
        <td style="width: 220px">
            Total Qty Barang<strong>{{ number_format($shipments->sum(fn($s) => $s->items->sum('qty'))) }}</strong>
        </td>
    </tr>
</table>

<div class="footer">SevenKey ERP — Laporan dibuat otomatis pada {{ now()->format('d F Y H:i:s') }}</div>
</body>
</html>
