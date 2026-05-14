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
.header-table { width: 100%; border-bottom: 2px solid #dc2626; margin-bottom: 16px; padding-bottom: 12px; }
.header-table td { border: none; padding: 0; vertical-align: top; }
.company { font-size: 16px; font-weight: bold; color: #dc2626; }
.main-table { width: 100%; border-collapse: collapse; font-size: 9px; table-layout: fixed; word-wrap: break-word; }
.main-table th { background: #fee2e2; padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #fca5a5; }
.main-table td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.summary-table { width: 100%; border-top: 2px solid #dc2626; margin-top: 4px; background: #fff7f7; }
.summary-table td { padding: 10px 8px; border: none; text-align: right; font-size: 8px; color: #666; text-transform: uppercase; }
.summary-table strong { font-size: 13px; color: #dc2626; display: block; }
.footer { margin-top: 16px; font-size: 8px; color: #999; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 8px; }
.badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 7.5px; font-weight: bold; }
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
            <div style="font-weight:bold;font-size:13px">LAPORAN PENGELUARAN</div>
            @if($sourceFilter)
            <div style="font-size:9px;color:#555">Sumber: {{ $sourceFilter }}</div>
            @endif
            @if($expenseType)
            <div style="font-size:9px;color:#555">Jenis: {{ $expenseType }}</div>
            @endif
            @if($dateFrom || $dateTo)
            <div style="font-size:9px;color:#555">Periode: {{ $dateFrom ?? 'awal' }} s/d {{ $dateTo ?? 'sekarang' }}</div>
            @endif
            <div style="font-size:8px;color:#999">Dicetak: {{ now()->format('d/m/Y H:i:s') }}</div>
        </td>
    </tr>
</table>

<table class="main-table">
    <thead>
        <tr>
            <th style="width: 3%">#</th>
            <th style="width: 10%">Tanggal</th>
            <th style="width: 14%">Jenis</th>
            <th style="width: 30%">Judul Pengeluaran</th>
            <th style="width: 20%">Sumber</th>
            <th class="text-right" style="width: 23%">Nominal (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($expenses as $i => $expense)
        <tr style="background: {{ $i % 2 === 0 ? '#fff' : '#fafafa' }}">
            <td style="color:#999">{{ $i+1 }}</td>
            <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
            <td>
                <span class="badge" style="background:#f1f5f9;color:#475569">{{ $expense->expense_type }}</span>
            </td>
            <td>
                <span style="font-weight:bold">{{ $expense->title }}</span>
                @if($expense->description)
                <br><span style="color:#999;font-size:7.5px">{{ Str::limit($expense->description, 80) }}</span>
                @endif
            </td>
            <td>
                @if($expense->store_id)
                <span style="color:#2563eb">Toko: {{ $expense->store->name ?? '-' }}</span>
                @elseif($expense->warehouse_id)
                <span style="color:#7c3aed">Gudang: {{ $expense->warehouse->name ?? '-' }}</span>
                @else
                <span style="color:#ccc">—</span>
                @endif
            </td>
            <td class="text-right" style="font-weight:bold;color:#dc2626">{{ number_format($expense->amount, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="summary-table">
    <tr>
        <td>
            Total Transaksi<strong>{{ number_format(count($expenses)) }}</strong>
        </td>
        <td style="width: 250px">
            Total Pengeluaran<strong>Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong>
        </td>
    </tr>
</table>

<div class="footer">SevenKey ERP — Laporan dibuat otomatis pada {{ now()->format('d F Y H:i:s') }}</div>
</body>
</html>
