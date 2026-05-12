<div class="receipt-print-area" style="font-family: 'Courier New', monospace; font-size: 14px; color: #000; background: #fff; width: 72mm; margin: 0 auto; padding: 0;">
    <div style="text-align: center; font-weight: bold; font-size: 20px; margin-bottom: 4px;">SevenKey ERP</div>
    <div style="text-align: center; font-size: 12px; color: #555;">{{ $sale->store->name }}</div>
    @if($sale->store->address)
    <div style="text-align: center; font-size: 11px; color: #777;">{{ $sale->store->address }}</div>
    @endif
    
    <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>
    
    <div style="display: flex; justify-content: space-between; margin-bottom: 3px;"><span>No:</span><span style="font-weight: bold;">{{ $sale->sale_no }}</span></div>
    <div style="display: flex; justify-content: space-between; margin-bottom: 3px;"><span>Tgl:</span><span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>
    <div style="display: flex; justify-content: space-between; margin-bottom: 3px;"><span>Kasir:</span><span>{{ substr($sale->creator?->name, 0, 15) }}</span></div>
    
    <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>
    
    @foreach($sale->items as $item)
    <div style="margin-bottom:8px">
        <div style="font-weight: bold; font-size: 13px;">{{ substr($item->variant->product->name, 0, 48) }}</div>
        <div style="display: flex; justify-content: space-between; margin-top: 2px;">
            <span style="font-size: 12px;">Rp {{ number_format($item->unit_price, 0, ',', '.') }} x{{ $item->qty }}</span>
            <span style="font-weight: bold;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
    </div>
    @endforeach
    
    <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>
    
    <div style="display: flex; justify-content: space-between;"><span>Subtotal</span><span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span></div>
    @if($sale->discount_amount > 0)
    <div style="display: flex; justify-content: space-between;"><span>Diskon</span><span style="color:#d00">− Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span></div>
    @endif
    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 16px; margin-top: 6px;">
        <span>TOTAL</span><span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
    </div>
    <div style="display: flex; justify-content: space-between; margin-top: 8px;"><span>Bayar</span><span>Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}</span></div>
    
    <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>
    <div style="text-align: center; font-size: 11px; margin-top: 10px;">Terima kasih atas kunjungan Anda!</div>
    <div style="height: 2.5cm;"></div> </div>