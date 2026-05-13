@extends('layouts.app')
@section('title', 'Riwayat Transaksi')
@section('page-title', 'Riwayat Transaksi POS')
@section('breadcrumb', 'POS / Riwayat')

@section('content')
    <div class="space-y-4" x-data="posHistoryApp()">

        <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
            @if($stores->count() > 0)
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Toko</label>
                    <select name="store_id" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Toko</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ request('store_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg self-end">Filter</button>
            <a href="{{ route('pos.history') }}"
                class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end">Reset</a>

            @php
                $totalAmount = $sales->sum('total_amount');
                $totalItems = $sales->sum(fn($s) => $s->items->sum('qty'));
            @endphp
            <div class="ml-auto self-end text-sm text-gray-600">
                <span class="font-semibold text-gray-900">{{ $sales->total() }}</span> transaksi ·
                Total: <span class="font-semibold text-indigo-700">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
            </div>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No. Transaksi</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Toko</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Kasir</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Pembayaran</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Item</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Total</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($sales as $sale)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $sale->sale_no }}</td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700">{{ $sale->store->name }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700">{{ $sale->creator?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700">{{ $sale->paymentMethod?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-xs text-gray-700">{{ $sale->items->sum('qty') }}</td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-gray-800">
                                    Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                                    @if($sale->discount_amount > 0)
                                        <span class="block text-xs font-normal text-red-500">Diskon: Rp
                                            {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="openReceipt({{ $sale->id }})"
                                        class="inline-flex items-center gap-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        Cetak
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400">Tidak ada transaksi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sales->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">{{ $sales->links() }}</div>
            @endif
        </div>

    <template x-teleport="body">
        <div x-show="showReceiptModal" style="display: none; z-index: 999999;" class="fixed inset-0 flex items-center justify-center bg-gray-900/70 backdrop-blur-md p-4 transition-opacity">
            <div @click.outside="showReceiptModal = false" x-show="showReceiptModal" x-transition.scale.origin.bottom class="bg-white w-full max-w-md rounded-[2rem] overflow-hidden shadow-2xl flex flex-col max-h-[90vh] border border-white/20">
                <!-- Header Modal -->
                <div class="bg-indigo-600 px-6 py-5 flex justify-between items-center shrink-0 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                    <div class="absolute -left-4 -bottom-4 w-24 h-24 bg-black/10 rounded-full blur-xl"></div>
                    <div class="relative flex items-center gap-3 z-10">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="text-white font-black text-xl tracking-wide">Cetak Struk</h3>
                            <p class="text-indigo-100 text-xs font-medium">Preview transaksi terpilih</p>
                        </div>
                    </div>
                    <button @click="showReceiptModal = false" class="text-white/70 hover:text-white bg-black/10 hover:bg-black/20 p-2 rounded-full transition-colors z-10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <!-- Area Preview Struk -->
                <div class="flex-1 bg-gray-50 overflow-y-auto p-6 flex justify-center custom-scrollbar relative shadow-inner">
                    <!-- Decorative background elements -->
                    <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px] opacity-50"></div>
                    
                    <div id="print-area" x-html="receiptHtmlHtml" class="bg-white shadow-xl p-0 relative z-10 transform origin-top hover:scale-[1.02] transition-transform duration-300 min-w-[72mm] flex-shrink-0" style="min-height: 100px;"></div>
                </div>

                <!-- Area Aksi / Tombol -->
                <div class="p-5 bg-white border-t border-gray-100 flex flex-col gap-3 shrink-0 shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.05)]">
                    <button @click="executePrint()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 rounded-2xl text-lg shadow-[0_8px_20px_-6px_rgba(79,70,229,0.5)] hover:shadow-[0_12px_25px_-8px_rgba(79,70,229,0.7)] transition-all flex items-center justify-center gap-3 group">
                        <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        CETAK STRUK SEKARANG
                    </button>
                    <button @click="showReceiptModal = false" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3.5 rounded-2xl transition-colors flex items-center justify-center gap-2">
                        Tutup Preview
                    </button>
                </div>
            </div>
        </div>
    </template>

    <style>
        /* Mempercantik Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }

        @media print {
            header, nav, aside { display: none !important; }
            .space-y-4 { display: none !important; }
            .fixed.inset-0 { position: static !important; background: transparent !important; padding: 0 !important; }
            .bg-white.max-w-md { max-width: 100% !important; box-shadow: none !important; height: auto !important; }
            .bg-indigo-600, .p-5.bg-white.border-t { display: none !important; }
            @page { margin: 0; }
        }
    </style>

@push('scripts')
<script>
function posHistoryApp() {
    return {
        showReceiptModal: false,
        receiptHtmlHtml: '',
        currentSaleData: null,
        printMethod: localStorage.getItem('pos_print_method') || 'pc_usb',
        cachedBluetoothDevice: null,
        cachedCharacteristic: null,

        async openReceipt(saleId) {
            try {
                let res = await fetch(`/reports/sales/${saleId}/detail`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!res.ok) {
                    alert('Gagal mengambil data struk (status: ' + res.status + ').');
                    return;
                }
                let data = await res.json();
                if (data.success) {
                    this.currentSaleData = data.sale;
                    // Jika server berhasil render HTML (ada library QR/barcode), tampilkan
                    // Jika tidak (hosting tanpa library), buat preview sederhana dari data
                    if (data.html) {
                        this.receiptHtmlHtml = data.html;
                    } else {
                        this.receiptHtmlHtml = this.buildSimpleReceiptHtml(data.sale);
                    }
                    this.showReceiptModal = true;
                } else {
                    alert('Gagal mengambil data struk.');
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan: ' + err.message);
            }
        },

        buildSimpleReceiptHtml(sale) {
            const fmt = (n) => parseInt(n || 0).toLocaleString('id-ID');
            let rows = sale.items.map(item => {
                const name = item.variant?.product?.name || '-';
                const sku  = item.variant?.sku || '';
                return `<div style="margin-bottom:6px">
                    <div style="font-weight:bold">${name}</div>
                    <div style="font-size:11px;color:#555">${sku}</div>
                    <div style="display:flex;justify-content:space-between">
                        <span>@ ${fmt(item.unit_price)} x${item.qty}</span>
                        <span style="font-weight:bold">Rp ${fmt(item.subtotal)}</span>
                    </div>
                </div>`;
            }).join('');
            return `<div style="font-family:monospace;font-size:13px;width:72mm;margin:0 auto;padding:12px">
                <div style="text-align:center;font-weight:bold;font-size:16px;margin-bottom:4px">SevenKey ERP</div>
                <div style="text-align:center;font-weight:bold">${sale.store?.name || ''}</div>
                <hr style="border-top:1px dashed #000;margin:8px 0">
                <div style="display:flex;justify-content:space-between"><span>No.</span><span><b>${sale.sale_no}</b></span></div>
                <div style="display:flex;justify-content:space-between"><span>Tgl</span><span>${(sale.created_at||'').substring(0,16).replace('T',' ')}</span></div>
                <div style="display:flex;justify-content:space-between"><span>Kasir</span><span>${sale.creator?.name||'-'}</span></div>
                <hr style="border-top:1px dashed #000;margin:8px 0">
                ${rows}
                <hr style="border-top:1px solid #000;margin:8px 0">
                <div style="display:flex;justify-content:space-between"><span>Subtotal</span><span>Rp ${fmt(sale.subtotal)}</span></div>
                ${sale.discount_amount > 0 ? `<div style="display:flex;justify-content:space-between"><span>Diskon</span><span>-Rp ${fmt(sale.discount_amount)}</span></div>` : ''}
                <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:15px;margin-top:6px"><span>TOTAL</span><span>Rp ${fmt(sale.total_amount)}</span></div>
                <div style="display:flex;justify-content:space-between;margin-top:4px"><span>Bayar</span><span>Rp ${fmt(sale.amount_paid)}</span></div>
                ${sale.change_amount > 0 ? `<div style="display:flex;justify-content:space-between;font-weight:bold"><span>Kembalian</span><span>Rp ${fmt(sale.change_amount)}</span></div>` : ''}
                <hr style="border-top:1px dashed #000;margin:8px 0">
                <div style="text-align:center;font-size:12px;font-weight:bold;margin-top:10px">TERIMA KASIH ATAS KUNJUNGAN ANDA</div>
                <div style="height:2cm"></div>
            </div>`;
        },

        async executePrint() {
            if (this.printMethod === 'pc_usb') {
                let printFrame = document.createElement('iframe');
                printFrame.style.display = 'none';
                document.body.appendChild(printFrame);
                printFrame.contentDocument.write('<html><head><style>@page { margin: 0; } body { margin: 0; font-family: monospace; }</style></head><body>' + this.receiptHtmlHtml + '</body></html>');
                printFrame.contentDocument.close();
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
                setTimeout(() => document.body.removeChild(printFrame), 2000);
            } 
            else if (this.printMethod === 'android_flutter') {
                let sale = this.currentSaleData;
                let dataStruk = {
                    store_name: sale.store.name,
                    store_address: sale.store.address || '',
                    receipt_no: sale.sale_no,
                    date: sale.created_at.substring(0, 16).replace('T', ' '),
                    cashier: sale.creator ? sale.creator.name.substring(0, 15) : '-',
                    items: sale.items.map(item => ({
                        name: String(item.variant.product.name).substring(0, 30),
                        qty: item.qty,
                        price: parseInt(item.unit_price).toLocaleString('id-ID'),
                        total: parseInt(item.subtotal).toLocaleString('id-ID')
                    })),
                    subtotal: parseInt(sale.subtotal).toLocaleString('id-ID'),
                    grand_total: parseInt(sale.total_amount).toLocaleString('id-ID'),
                    paid: parseInt(sale.amount_paid).toLocaleString('id-ID'),
                    change: parseInt(sale.change_amount).toLocaleString('id-ID'),
                    bank_name: sale.store.bank_name || '',
                    bank_account: sale.store.bank_account || '',
                    bank_account_name: sale.store.bank_account_name || '',
                    qr_code: sale.sale_no,
                    barcode: sale.sale_no
                };

                if (window.PrintChannel) {
                    window.PrintChannel.postMessage(JSON.stringify(dataStruk));
                } else {
                    alert("Aplikasi Native Flutter tidak terdeteksi!");
                }
            } 
            else {
                try {
                    if (!this.cachedCharacteristic) {
                        this.cachedBluetoothDevice = await navigator.bluetooth.requestDevice({
                            acceptAllDevices: true,
                            optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                        });
                        
                        this.cachedBluetoothDevice.addEventListener('gattserverdisconnected', () => {
                            this.cachedCharacteristic = null;
                        });

                        const server = await this.cachedBluetoothDevice.gatt.connect();
                        const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                        this.cachedCharacteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                    }

                    const alignLR = (left, right, max = 48) => {
                        let spaces = max - left.toString().length - right.toString().length;
                        return left + " ".repeat(spaces > 0 ? spaces : 1) + right + "\n";
                    };
                    const alignC = (text, max = 48) => {
                        let spaces = Math.floor((max - text.length) / 2);
                        return " ".repeat(spaces > 0 ? spaces : 0) + text + "\n";
                    };

                    let sale = this.currentSaleData;
                    let text = "\n"; 
                    text += alignC("SEVENKEY ERP");
                    text += alignC(sale.store.name);
                    text += "------------------------------------------------\n";
                    text += alignLR("No:", sale.sale_no);
                    text += alignLR("Tgl:", sale.created_at.substring(0, 16).replace('T', ' '));
                    text += alignLR("Kasir:", sale.creator ? sale.creator.name.substring(0, 15) : '-');
                    text += "------------------------------------------------\n";
                    
                    sale.items.forEach(item => {
                        text += String(item.variant.product.name).substring(0, 48) + "\n";
                        let priceQty = item.qty + " x " + parseInt(item.unit_price).toLocaleString('id-ID');
                        let totalStr = "Rp " + parseInt(item.subtotal).toLocaleString('id-ID');
                        text += alignLR(priceQty, totalStr);
                    });
                    
                    text += "------------------------------------------------\n";
                    text += alignLR("Subtotal", "Rp " + parseInt(sale.subtotal).toLocaleString('id-ID'));
                    if (sale.discount_amount > 0) {
                        text += alignLR("Diskon", "-Rp " + parseInt(sale.discount_amount).toLocaleString('id-ID'));
                    }
                    text += alignLR("TOTAL", "Rp " + parseInt(sale.total_amount).toLocaleString('id-ID'));
                    text += alignLR("Bayar", "Rp " + parseInt(sale.amount_paid).toLocaleString('id-ID'));
                    if (sale.change_amount > 0) {
                        text += alignLR("Kembali", "Rp " + parseInt(sale.change_amount).toLocaleString('id-ID'));
                    }
                    text += "------------------------------------------------\n";
                    
                    if (sale.store.bank_name || sale.store.bank_account) {
                        text += alignC("PEMBAYARAN TRANSFER:");
                        if (sale.store.bank_name) text += alignC("Bank: " + sale.store.bank_name);
                        if (sale.store.bank_account) text += alignC(sale.store.bank_account);
                        if (sale.store.bank_account_name) text += alignC("A.N. " + sale.store.bank_account_name);
                        text += "------------------------------------------------\n";
                    }

                    const encoder = new TextEncoder();
                    const init = new Uint8Array([0x1B, 0x40]);
                    const contentBytes = encoder.encode(text);
                    
                    let qrData = sale.sale_no;
                    let qrBytes = encoder.encode(qrData);
                    let pL = (qrBytes.length + 3) % 256;
                    let pH = Math.floor((qrBytes.length + 3) / 256);
                    let qrCmds = new Uint8Array([
                        0x1B, 0x61, 0x01,
                        0x1D, 0x28, 0x6B, 0x04, 0x00, 0x31, 0x41, 0x32, 0x00,
                        0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x43, 0x06,
                        0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x45, 0x31,
                        0x1D, 0x28, 0x6B, pL, pH, 0x31, 0x50, 0x30, ...qrBytes,
                        0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x51, 0x30,
                        0x0A, 0x0A
                    ]);

                    let barcodeBytes = encoder.encode("{B" + sale.sale_no);
                    let barcodeCmds = new Uint8Array([
                        0x1B, 0x61, 0x01,
                        0x1D, 0x68, 60,
                        0x1D, 0x77, 2,
                        0x1D, 0x48, 2,
                        0x1D, 0x6B, 73, barcodeBytes.length, ...barcodeBytes,
                        0x0A, 0x0A
                    ]);

                    const thanksBytes = encoder.encode(alignC("TERIMA KASIH ATAS KUNJUNGAN ANDA"));
                    const feed = new Uint8Array([0x1B, 0x64, 0x05]); 

                    const payload = new Uint8Array(init.length + contentBytes.length + qrCmds.length + barcodeCmds.length + thanksBytes.length + feed.length);
                    let offset = 0;
                    payload.set(init, offset); offset += init.length;
                    payload.set(contentBytes, offset); offset += contentBytes.length;
                    payload.set(qrCmds, offset); offset += qrCmds.length;
                    payload.set(barcodeCmds, offset); offset += barcodeCmds.length;
                    payload.set(thanksBytes, offset); offset += thanksBytes.length;
                    payload.set(feed, offset);

                    for (let i = 0; i < payload.length; i += 40) {
                        await this.cachedCharacteristic.writeValue(payload.slice(i, i + 40));
                    }
                } catch (e) { 
                    alert("Koneksi Bluetooth Terputus. Mohon pilih perangkat kembali. " + e.message); 
                    this.cachedCharacteristic = null; 
                }
            }
        },
    };
}
</script>
@endpush
@endsection