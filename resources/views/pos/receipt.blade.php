<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $sale->sale_no }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        /* UBAH: Diperlebar jadi 72mm (Area cetak printer 80mm) & Ukuran Font diperbesar */
        body { font-family: 'Courier New', monospace; font-size: 14px; color: #000; background: #fff; width: 72mm; margin: 0 auto; padding: 12px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .row-right { text-align: right; }
        .total-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 16px; margin-top: 6px;}
        .item-name { flex: 1; padding-right: 8px; }
        .item-qty { width: 50px; text-align: center; }
        .item-price { width: 90px; text-align: right; }
        .thanks { margin-top: 16px; text-align: center; font-size: 13px; }
        
        @media print {
            @page { margin: 0; }
            body { width: 100%; padding: 4mm; margin: 0; }
            .no-print { display: none; }
            .print-spacer { height: 2.5cm; } 
        }
    </style>
</head>
<body>

<div class="center bold" style="font-size:20px; margin-bottom: 4px;">SevenKey ERP</div>
<div class="center" style="font-size:12px;color:#555">{{ $sale->store->name }}</div>
@if($sale->store->address)
<div class="center" style="font-size:11px;color:#777">{{ $sale->store->address }}</div>
@endif

<div class="divider"></div>

<div class="row"><span>No. Transaksi</span><span class="bold">{{ $sale->sale_no }}</span></div>
<div class="row"><span>Tanggal</span><span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>
<div class="row"><span>Kasir</span><span>{{ $sale->creator?->name }}</span></div>
<div class="row"><span>Pembayaran</span><span>{{ $sale->paymentMethod?->name }}</span></div>

<div class="divider"></div>

<div style="margin-bottom:6px">
    <div class="row bold" style="font-size:12px;text-transform:uppercase;color:#555">
        <span class="item-name">Produk</span>
        <span class="item-qty">Qty</span>
        <span class="item-price">Total</span>
    </div>
</div>

@foreach($sale->items as $item)
@php $v = $item->variant; @endphp
<div style="margin-bottom:8px">
    <div class="bold" style="font-size:13px">{{ $v->product->name }}</div>
    <div style="font-size:11px;color:#555">{{ $v->sku }} · {{ $v->color->name }} / {{ $v->size->name }}</div>
    <div class="row" style="margin-top:2px">
        <span class="item-name" style="font-size:12px">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
        <span class="item-qty">x{{ $item->qty }}</span>
        <span class="item-price bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
    </div>
</div>
@endforeach

<div class="divider"></div>

<div class="row"><span>Subtotal</span><span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span></div>
@if($sale->discount_amount > 0)
<div class="row"><span>Diskon</span><span style="color:#d00">− Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span></div>
@endif
<div class="total-row">
    <span>TOTAL</span>
    <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
</div>
<div class="row" style="margin-top:8px"><span>Bayar</span><span>Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}</span></div>
@if($sale->change_amount > 0)
<div class="row bold"><span>Kembalian</span><span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span></div>
@endif

<div class="divider"></div>
<div style="text-align:center;padding:10px 0">
    {!! QrCode::size(100)->margin(0)->generate($sale->sale_no) !!}
    <div style="font-size:11px;color:#666;margin-top:4px">{{ $sale->sale_no }}</div>
</div>
<div class="divider"></div>
<div class="thanks">Terima kasih atas kunjungan Anda!</div>

<div class="print-spacer"></div>

<div class="no-print" style="margin-top:20px;text-align:center;font-family:sans-serif">
    <button onclick="window.print()"
        style="background:#f3f4f6;color:#374151;border:none;padding:12px 16px;border-radius:8px;font-size:14px;cursor:pointer;margin-right:5px;font-weight:bold;">
        🖨️ Cetak (Laptop / Sistem)
    </button>
    <button onclick="printKeAplikasiPos()"
        style="background:#4f46e5;color:white;border:none;padding:12px 16px;border-radius:8px;font-size:14px;cursor:pointer;margin-right:5px;font-weight:bold;">
        🔵 Cetak via Aplikasi
    </button>
    <br><br>
    <a href="{{ route('pos.index') }}"
        style="background:#10b981;color:white;text-decoration:none;padding:12px 20px;border-radius:8px;font-size:14px;display:inline-block;font-weight:bold;">
        Transaksi Baru
    </a>
</div>

<script>
    function printKeAplikasiPos() {
        // Merakit data transaksi menjadi format JSON
        const dataStruk = {
            store_name: "{{ $sale->store->name }}",
            store_address: "{{ $sale->store->address ?? '' }}",
            receipt_no: "{{ $sale->sale_no }}",
            date: "{{ $sale->created_at->format('d/m/Y H:i') }}",
            cashier: "{{ substr($sale->creator?->name, 0, 15) }}",
            
            // Membuat Array (Daftar) Barang yang dibeli
            items: [
                @foreach($sale->items as $item)
                {
                    name: "{{ $item->variant->product->name }}",
                    qty: "{{ $item->qty }}",
                    price: "{{ number_format($item->unit_price, 0, ',', '.') }}",
                    total: "{{ number_format($item->subtotal, 0, ',', '.') }}"
                },
                @endforeach
            ],
            
            subtotal: "{{ number_format($sale->subtotal, 0, ',', '.') }}",
            grand_total: "{{ number_format($sale->total_amount, 0, ',', '.') }}",
            paid: "{{ number_format($sale->amount_paid, 0, ',', '.') }}"
        };

        // Mengirim JSON ke Jembatan Flutter (PrintChannel)
        if (window.PrintChannel) {
            window.PrintChannel.postMessage(JSON.stringify(dataStruk));
        } else {
            alert("Fitur ini hanya berjalan jika Anda membuka sistem melalui Aplikasi SevenKey POS di Android/iOS.");
        }
    }
</script>
</body>
</html>