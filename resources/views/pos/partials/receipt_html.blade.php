<style>
    .receipt-wrapper { box-sizing: border-box; font-family: 'Courier New', monospace; font-size: 13px; color: #000; background: #fff; width: 72mm; margin: 0 auto; padding: 12px; }
    .receipt-wrapper .center { text-align: center; }
    .receipt-wrapper .bold { font-weight: bold; }
    .receipt-wrapper .divider { border-top: 1px dashed #000; margin: 10px 0; }
    .receipt-wrapper .divider-solid { border-top: 1px solid #000; margin: 10px 0; }
    .receipt-wrapper .row { display: flex; justify-content: space-between; margin-bottom: 4px; }
    .receipt-wrapper .total-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 15px; margin-top: 8px;}
    .receipt-wrapper .item-name { flex: 1; padding-right: 8px; }
    .receipt-wrapper .item-qty { width: 40px; text-align: center; }
    .receipt-wrapper .item-price { width: 85px; text-align: right; }
    .receipt-wrapper .thanks { margin-top: 16px; text-align: center; font-size: 12px; font-weight: bold; }
    .receipt-wrapper .bank-info { margin-top: 10px; font-size: 11px; text-align: center; line-height: 1.4; border: 1px dashed #666; padding: 6px; border-radius: 4px; }
    .receipt-wrapper .barcode-container { margin: 12px 0; text-align: center; }
    .receipt-wrapper .barcode-container svg { width: 100%; max-width: 200px; height: 40px; }
    .receipt-wrapper .barcode-text { font-size: 12px; margin-top: 2px; letter-spacing: 2px; }
    
    @media screen {
        .hide-in-preview { display: none !important; }
    }
</style>

<div class="receipt-wrapper">
    <div class="center bold" style="font-size:16px; margin-bottom: 4px; text-transform: uppercase;">{{ $sale->store->name }}</div>
    @if($sale->store->address)
    <div class="center" style="font-size:11px;color:#444">{{ $sale->store->address }}</div>
    @endif

    <div class="divider"></div>

    <div class="row"><span>No.</span><span class="bold">{{ $sale->sale_no }}</span></div>
    <div class="row"><span>Tgl</span><span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span>Kasir</span><span>{{ $sale->creator?->name }}</span></div>
    <div class="row"><span>Metode</span><span>{{ strtoupper($sale->paymentMethod?->name) }}</span></div>

    <div class="divider"></div>

    <div style="margin-bottom:6px">
        <div class="row bold" style="font-size:12px;text-transform:uppercase;">
            <span class="item-name">Item</span>
            <span class="item-qty">Qty</span>
            <span class="item-price">Total</span>
        </div>
    </div>
    <div class="divider-solid"></div>

    @foreach($sale->items as $item)
    @php $v = $item->variant; @endphp
    <div style="margin-bottom:8px">
        <div class="bold" style="font-size:13px">{{ $v->product->name }}</div>
        <div style="font-size:11px;color:#444;margin-bottom:2px;">{{ $v->sku }} · {{ $v->color->name }} / {{ $v->size->name }}</div>
        <div class="row">
            <span class="item-name" style="font-size:12px">@ {{ number_format($item->unit_price, 0, ',', '.') }}</span>
            <span class="item-qty">x{{ $item->qty }}</span>
            <span class="item-price bold">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
    </div>
    @endforeach

    <div class="divider-solid"></div>

    <div class="row"><span>Subtotal</span><span>{{ number_format($sale->subtotal, 0, ',', '.') }}</span></div>
    @if($sale->discount_amount > 0)
    <div class="row"><span>Diskon</span><span style="color:#000">− {{ number_format($sale->discount_amount, 0, ',', '.') }}</span></div>
    @endif
    <div class="total-row">
        <span>TOTAL</span>
        <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
    </div>
    <div class="row" style="margin-top:8px"><span>Bayar</span><span>{{ number_format($sale->amount_paid, 0, ',', '.') }}</span></div>
    @if($sale->change_amount > 0)
    <div class="row bold"><span>Kembalian</span><span>{{ number_format($sale->change_amount, 0, ',', '.') }}</span></div>
    @endif

    <div class="divider"></div>

    <div style="margin-top: 15px;">
        @if($sale->store->bank_name || $sale->store->bank_account)
        <div class="bank-info">
            <div class="bold" style="margin-bottom:2px">PEMBAYARAN TRANSFER:</div>
            @if($sale->store->bank_name)
            <div>Bank: <span class="bold">{{ $sale->store->bank_name }}</span></div>
            @endif
            @if($sale->store->bank_account)
            <div style="font-size:12px;" class="bold">{{ $sale->store->bank_account }}</div>
            @endif
            @if($sale->store->bank_account_name)
            <div>A.N. <span class="bold">{{ $sale->store->bank_account_name }}</span></div>
            @endif
        </div>
        <div class="divider"></div>
        @endif

        <!-- QR Code dan Barcode -->
        <div style="text-align:center;padding:10px 0">
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->margin(0)->generate($sale->sale_no) !!}
        </div>
        <div class="barcode-container">
            @php
                $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                $barcodeSvg = $generator->getBarcode($sale->sale_no, $generator::TYPE_CODE_128, 2, 50, 'black');
            @endphp
            {!! $barcodeSvg !!}
            <div class="barcode-text">{{ $sale->sale_no }}</div>
        </div>

        <div class="thanks">TERIMA KASIH TELAH BERBELANJA</div>
        <div class="center" style="font-size:10px; margin-top: 4px;">Silahkan bawa struk ini untuk retur barang</div>
        <div style="height: 2.5cm;" class="hide-in-preview"></div>
    </div>
</div>