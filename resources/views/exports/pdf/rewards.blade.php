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
.header-table { width: 100%; border-bottom: 2px solid #eab308; margin-bottom: 16px; padding-bottom: 12px; }
.header-table td { border: none; padding: 0; vertical-align: top; }
.company { font-size: 16px; font-weight: bold; color: #eab308; }
.main-table { width: 100%; border-collapse: collapse; font-size: 9px; table-layout: fixed; word-wrap: break-word; }
.main-table th { background: #fef9c3; padding: 6px 8px; text-align: left; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #fde047; }
.main-table td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.tfoot-row td { background: #fafafa; border-top: 2px solid #eab308; font-weight: bold; }
.summary-table { width: 100%; border-top: 2px solid #eab308; margin-top: 4px; background: #fefce8; }
.summary-table td { padding: 10px 8px; border: none; text-align: right; font-size: 8px; color: #666; text-transform: uppercase; }
.summary-table strong { font-size: 13px; color: #854d0e; display: block; }
.footer { margin-top: 16px; font-size: 8px; color: #999; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 8px; }
.info-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 4px; padding: 8px 12px; margin-bottom: 14px; font-size: 8px; color: #0369a1; }
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
            <div style="font-weight:bold;font-size:13px">LAPORAN REWARD &amp; BONUS TOKO</div>
            <div style="font-size:9px;color:#555">Periode: {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</div>
            <div style="font-size:8px;color:#999">Dicetak: {{ now()->format('d/m/Y H:i:s') }}</div>
        </td>
    </tr>
</table>

<div class="info-box">
    <strong style="display:block;margin-bottom:3px">Informasi Perhitungan Reward:</strong>
    Reward Reguler: Dibagikan per tahun, dihitung per penjualan barang sesuai konfigurasi master produk. &nbsp;|&nbsp;
    Bonus Target: Jika total penjualan melebihi target bulanan, toko mendapat bonus Rp 1.000.000 per kelipatan 1.000 barang melebihi target.
</div>

<table class="main-table">
    <thead>
        <tr>
            <th style="width: 3%">#</th>
            <th style="width: 25%">Nama Toko</th>
            <th class="text-right" style="width: 12%">Target (Pcs)</th>
            <th class="text-right" style="width: 12%">Terjual (Pcs)</th>
            <th class="text-right" style="width: 12%">Kelebihan</th>
            <th class="text-right" style="width: 18%">Reward Reguler</th>
            <th class="text-right" style="width: 18%">Bonus Target</th>
        </tr>
    </thead>
    <tbody>
        @foreach($storeRewards as $i => $data)
        <tr style="background: {{ $i % 2 === 0 ? '#fff' : '#fafafa' }}">
            <td style="color:#999">{{ $i+1 }}</td>
            <td style="font-weight:bold">{{ $data['store']->name }}</td>
            <td class="text-right">{{ number_format($data['target'], 0, ',', '.') }}</td>
            <td class="text-right" style="font-weight:bold;color:{{ $data['total_qty'] >= $data['target'] && $data['target'] > 0 ? '#16a34a' : '#111' }}">
                {{ number_format($data['total_qty'], 0, ',', '.') }}
                @if($data['total_qty'] >= $data['target'] && $data['target'] > 0)
                <span style="font-size:7px;color:#16a34a"> ✓</span>
                @endif
            </td>
            <td class="text-right" style="color:#7c3aed">{{ $data['excess'] > 0 ? '+'.number_format($data['excess'],0,',','.') : '—' }}</td>
            <td class="text-right">Rp {{ number_format($data['regular_reward'], 0, ',', '.') }}</td>
            <td class="text-right" style="font-weight:bold;color:#16a34a">Rp {{ number_format($data['bonus'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    @if(count($storeRewards) > 0)
    <tfoot>
        <tr class="tfoot-row">
            <td colspan="5" class="text-right" style="color:#555">Total Keseluruhan</td>
            <td class="text-right">Rp {{ number_format(collect($storeRewards)->sum('regular_reward'), 0, ',', '.') }}</td>
            <td class="text-right" style="color:#16a34a">Rp {{ number_format(collect($storeRewards)->sum('bonus'), 0, ',', '.') }}</td>
        </tr>
        <tr class="tfoot-row">
            <td colspan="5" class="text-right" style="color:#555;font-size:10px">TOTAL REWARD BULAN INI</td>
            <td colspan="2" class="text-right" style="font-size:13px;color:#854d0e">
                Rp {{ number_format(collect($storeRewards)->sum('total_reward'), 0, ',', '.') }}
            </td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="footer">SevenKey ERP — Laporan dibuat otomatis pada {{ now()->format('d F Y H:i:s') }}</div>
</body>
</html>
