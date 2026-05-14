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
.header-table { width: 100%; border-bottom: 2px solid #7c3aed; margin-bottom: 16px; padding-bottom: 12px; }
.header-table td { border: none; padding: 0; vertical-align: top; }
.company { font-size: 16px; font-weight: bold; color: #7c3aed; }
.main-table { width: 100%; border-collapse: collapse; font-size: 9px; table-layout: fixed; word-wrap: break-word; }
.main-table th { background: #ede9fe; padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #c4b5fd; }
.main-table td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.summary-table { width: 100%; border-top: 2px solid #7c3aed; margin-top: 4px; background: #f5f3ff; }
.summary-table td { padding: 10px 8px; border: none; text-align: right; font-size: 8px; color: #666; text-transform: uppercase; }
.summary-table strong { font-size: 13px; color: #7c3aed; display: block; }
.footer { margin-top: 16px; font-size: 8px; color: #999; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 8px; }
.badge { padding: 1px 5px; border-radius: 3px; font-size: 7px; font-weight: bold; }
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
            <div style="font-weight:bold;font-size:13px">LAPORAN TRANSFER TOKO</div>
            @if($fromStore)
            <div style="font-size:9px;color:#555">Dari Toko: {{ $fromStore->name }}</div>
            @endif
            @if($toStore)
            <div style="font-size:9px;color:#555">Ke Toko: {{ $toStore->name }}</div>
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
            <th style="width: 16%">No. Transfer</th>
            <th style="width: 18%">Dari Toko</th>
            <th style="width: 18%">Ke Toko</th>
            <th class="text-center" style="width: 12%">Status</th>
            <th class="text-right" style="width: 10%">Diminta</th>
            <th class="text-right" style="width: 10%">Diterima</th>
            <th style="width: 13%">Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transfers as $i => $t)
        <tr style="background: {{ $i % 2 === 0 ? '#fff' : '#fafafa' }}">
            <td style="color:#999">{{ $i+1 }}</td>
            <td style="font-family:monospace;font-weight:bold;color:#7c3aed">{{ $t->transfer_no }}</td>
            <td>{{ $t->fromStore->name }}</td>
            <td>{{ $t->toStore->name }}</td>
            <td class="text-center">
                <span class="badge" style="{{ $t->statusColor() === 'bg-green-100 text-green-700' ? 'background:#dcfce7;color:#15803d' : ($t->statusColor() === 'bg-blue-100 text-blue-700' ? 'background:#dbeafe;color:#1d4ed8' : ($t->statusColor() === 'bg-yellow-100 text-yellow-700' ? 'background:#fef9c3;color:#854d0e' : 'background:#f1f5f9;color:#475569')) }}">
                    {{ $t->statusLabel() }}
                </span>
            </td>
            <td class="text-right" style="font-weight:bold">{{ number_format($t->items->sum('qty_requested')) }}</td>
            <td class="text-right" style="color:{{ $t->isReceived() ? '#16a34a' : '#ccc' }};font-weight:{{ $t->isReceived() ? 'bold' : 'normal' }}">
                {{ $t->isReceived() ? number_format($t->items->sum('qty_received')) : '—' }}
            </td>
            <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="summary-table">
    <tr>
        <td>
            Total Transfer<strong>{{ number_format(count($transfers)) }}</strong>
        </td>
        <td style="width: 200px">
            Total Qty Diminta<strong>{{ number_format($transfers->sum(fn($t) => $t->items->sum('qty_requested'))) }}</strong>
        </td>
    </tr>
</table>

<div class="footer">SevenKey ERP — Laporan dibuat otomatis pada {{ now()->format('d F Y H:i:s') }}</div>
</body>
</html>
