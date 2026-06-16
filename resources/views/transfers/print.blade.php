<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transfer {{ $transfer->transfer_no }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #111; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #111; padding-bottom: 12px; margin-bottom: 16px; }
        .company { font-size: 18px; font-weight: 700; }
        .doc-title { font-size: 14px; font-weight: 700; text-align: right; }
        .doc-no { font-family: monospace; font-size: 16px; color: #4f46e5; }
        .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 24px; margin-bottom: 16px; }
        .meta-row { display: flex; gap: 8px; }
        .meta-label { color: #666; width: 90px; flex-shrink: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #f3f4f6; border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { border: 1px solid #d1d5db; padding: 6px 8px; }
        .text-right { text-align: right; }
        .footer { margin-top: 32px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .sign-box { border-top: 1px solid #111; padding-top: 8px; text-align: center; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="company">SevenKey ERP</div>
            <div style="color:#666;margin-top:2px">Dokumen Transfer Antar Toko</div>
        </div>
        <div style="text-align:right">
            <div class="doc-title">TRANSFER ORDER</div>
            <div class="doc-no">{{ $transfer->transfer_no }}</div>
            <div style="color:#666;margin-top:4px">{{ $transfer->created_at->format('d/m/Y H:i') }}</div>
            <svg id="docBarcode" style="margin-top:6px;max-width:180px"></svg>
        </div>
    </div>

    <div class="meta">
        <div class="meta-row"><span class="meta-label">Dari Toko</span><strong>{{ $transfer->fromStore->name }}</strong></div>
        <div class="meta-row"><span class="meta-label">Status</span><strong>{{ $transfer->statusLabel() }}</strong></div>
        <div class="meta-row"><span class="meta-label">Ke Toko</span><strong>{{ $transfer->toStore->name }}</strong></div>
        <div class="meta-row"><span class="meta-label">Dibuat oleh</span>{{ $transfer->creator?->name ?? '—' }}</div>
        @if($transfer->notes)
        <div class="meta-row" style="grid-column:1/-1"><span class="meta-label">Catatan</span>{{ $transfer->notes }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>SKU</th>
                <th>Produk</th>
                <th class="text-right">Diminta</th>
                <th class="text-right">Dikirim</th>
                <th class="text-right">Diterima</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfer->items as $i => $item)
            @php $v = $item->variant; @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td style="font-family:monospace">{{ $v?->sku }}</td>
                <td>{{ $v?->product?->name }} · {{ $v?->color?->name }} / {{ $v?->size?->name }}</td>
                <td class="text-right">{{ $item->qty_requested }}</td>
                <td class="text-right">{{ $item->qty_sent ?: '—' }}</td>
                <td class="text-right">{{ $item->qty_received ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right" style="font-weight:700">Total</td>
                <td class="text-right" style="font-weight:700">{{ $transfer->totalQtyRequested() }}</td>
                <td class="text-right" style="font-weight:700">{{ $transfer->totalQtySent() ?: '—' }}</td>
                <td class="text-right" style="font-weight:700">{{ $transfer->totalQtyReceived() ?: '—' }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <div class="sign-box">Dikirim oleh ({{ $transfer->fromStore->name }})<br><br><br>{{ $transfer->shipper?->name ?? '........................' }}</div>
        <div class="sign-box">Diterima oleh ({{ $transfer->toStore->name }})<br><br><br>{{ $transfer->receiver?->name ?? '........................' }}</div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js"></script>
    <script>
    JsBarcode("#docBarcode", "{{ $transfer->transfer_no }}", {
        format: 'CODE128', width: 1.5, height: 40, displayValue: true,
        fontSize: 9, margin: 0, background: 'transparent'
    });
    window.print();
    </script>
</body>
</html>
