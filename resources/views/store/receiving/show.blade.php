@extends('layouts.app')
@section('title', 'Terima Kiriman')
@section('page-title', 'Penerimaan — ' . $shipment->shipment_no)
@section('breadcrumb', 'Toko / Penerimaan / ' . $shipment->shipment_no)

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- Info --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400">No. Pengiriman</p>
                <p class="font-mono font-bold text-indigo-600 text-lg">{{ $shipment->shipment_no }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Dari Gudang</p>
                <p class="font-medium text-gray-700">{{ $shipment->warehouse->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Ke Toko</p>
                <p class="font-medium text-gray-700">{{ $shipment->store->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Status</p>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $shipment->statusColor() }}">{{ $shipment->statusLabel() }}</span>
            </div>
        </div>
    </div>

    @if(in_array($shipment->status, ['shipped', 'arrived']))
    {{-- Receipt form (Ditambahkan ID receipt-form) --}}
    <form method="POST" id="receipt-form" action="{{ route('store.receiving.confirm', $shipment) }}" class="space-y-4">
        @csrf
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700">Konfirmasi Penerimaan Item</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Sesuaikan Qty Terima jika ada selisih, lalu Scan Resi untuk menyimpan.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Kirim</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Terima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($shipment->items as $i => $item)
                        @php $v = $item->variant; @endphp
                        <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ $v->sku }}</td>
                            <td class="px-4 py-2 text-xs text-gray-700">{{ $v->product->name }} · {{ $v->color->name }} / {{ $v->size->name }}</td>
                            <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700">{{ $item->qty_sent }}</td>
                            <td class="px-4 py-2 text-right">
                                <input type="number" name="items[{{ $i }}][qty_received]"
                                    value="{{ $item->qty_sent }}" min="0" max="{{ $item->qty_sent }}"
                                    class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-yellow-50 font-bold">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-between mt-4">
            <a href="{{ route('store.receiving.index') }}" class="text-sm text-gray-500 hover:text-gray-700 hover:underline">← Batal</a>
            
            {{-- Tombol diubah menjadi Indikator Scanner --}}
            <div class="bg-indigo-50 border border-indigo-200 text-indigo-700 px-6 py-3 rounded-xl text-sm font-bold flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                Menunggu Scan Barcode Resi...
            </div>
        </div>
    </form>

    {{-- SCRIPT VALIDASI SCANNER RESI --}}
    <script>
        let barcodeBuffer = '';
        let barcodeTimer = null;
        // Ambil nomor resi dari database sebagai kunci validasi
        const expectedResi = "{{ $shipment->shipment_no }}";

        document.addEventListener('keydown', function(e) {
            // Jika user menekan tombol Enter saat sedang mengubah Qty, 
            // cegah form tersubmit agar tidak terkonfirmasi tanpa sengaja.
            if (e.target.tagName === 'INPUT' && e.key === 'Enter') {
                e.preventDefault();
                return;
            }

            // Abaikan ketikan manusia jika sedang fokus di dalam kolom input Qty
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;

            // Logika Scanner Barcode
            if (e.key === 'Enter') {
                if (barcodeBuffer.length > 2) {
                    e.preventDefault();
                    
                    // Cek apakah hasil scan SAMA PERSIS dengan nomor resi halaman ini
                    if (barcodeBuffer === expectedResi) {
                        document.getElementById('receipt-form').submit();
                    } else {
                        alert('GAGAL! \n\nBarcode yang discan (' + barcodeBuffer + ') TIDAK COCOK dengan dokumen ini (' + expectedResi + '). \nPastikan Anda memindai surat jalan yang benar.');
                    }
                }
                barcodeBuffer = '';
            } else if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                barcodeBuffer += e.key;
                clearTimeout(barcodeTimer);
                // Jeda 50ms untuk membedakan scanner mesin dan ketikan manusia
                barcodeTimer = setTimeout(() => { barcodeBuffer = ''; }, 50);
            }
        });
    </script>

    @else
    {{-- View-only mode when already received --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Detail Item</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Kirim</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Terima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($shipment->items as $item)
                    @php $v = $item->variant; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ $v->sku }}</td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ $v->product->name }} · {{ $v->color->name }} / {{ $v->size->name }}</td>
                        <td class="px-4 py-2 text-right text-xs text-gray-700">{{ $item->qty_sent }}</td>
                        <td class="px-4 py-2 text-right text-xs font-bold {{ $item->qty_received < $item->qty_sent ? 'text-red-600' : 'text-green-600' }}">{{ $item->qty_received }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        <a href="{{ route('store.receiving.index') }}" class="text-sm text-gray-600 hover:text-gray-900 hover:underline">← Kembali ke Daftar</a>
    </div>
    @endif

</div>
@endsection