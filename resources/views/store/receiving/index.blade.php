@extends('layouts.app')
@section('title', 'Penerimaan Kiriman')
@section('page-title', 'Penerimaan Kiriman dari Gudang')
@section('breadcrumb', 'Toko / Penerimaan')

@section('content')
<div class="space-y-4">

    <form method="GET" id="filter-form" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        {{-- Kolom Pencarian Resi Khusus Scanner --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Cari / Scan No. Resi</label>
            <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                placeholder="Scan barcode resi..." autofocus
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-56">
        </div>
        
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
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Dalam Perjalanan</option>
                <option value="arrived" {{ request('status') === 'arrived' ? 'selected' : '' }}>Tiba</option>
                <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Diterima</option>
            </select>
        </div>
        <a href="{{ route('store.receiving.index') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end hover:bg-gray-200">Reset</a>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No. Pengiriman</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Dari Gudang</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Toko</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Kirim</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Tanggal Kirim</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($shipments as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $s->shipment_no }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">{{ $s?->warehouse?->name }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">{{ $s?->store?->name }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $s->statusColor() }}">{{ $s->statusLabel() }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-700">{{ $s->items->sum('qty_sent') }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $s->shipped_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            @if(in_array($s->status, ['shipped', 'arrived']))
                            <a href="{{ route('store.receiving.show', $s) }}"
                                class="text-xs text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded-lg">Terima</a>
                            @else
                            <a href="{{ route('store.receiving.show', $s) }}" class="text-xs text-indigo-600 hover:underline">Detail</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Tidak ada kiriman</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shipments->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $shipments->links() }}</div>
        @endif
    </div>

</div>

{{-- SCRIPT GLOBAL SCANNER BARCODE --}}
<script>
    let barcodeBuffer = '';
    let barcodeTimer = null;

    document.addEventListener('keydown', function(e) {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) && e.target.id !== 'searchInput') {
            return;
        }

        if (e.key === 'Enter') {
            if (barcodeBuffer.length > 2) {
                e.preventDefault();
                
                let searchInput = document.getElementById('searchInput');
                let filterForm = document.getElementById('filter-form');
                
                if (searchInput && filterForm) {
                    searchInput.value = barcodeBuffer;
                    filterForm.submit();
                }
            }
            barcodeBuffer = '';
        } else if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
            barcodeBuffer += e.key;
            clearTimeout(barcodeTimer);
            barcodeTimer = setTimeout(() => { barcodeBuffer = ''; }, 50);
        }
    });
</script>
@endsection