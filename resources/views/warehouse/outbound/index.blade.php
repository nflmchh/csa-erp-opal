@extends('layouts.app')
@section('title', 'Histori Keluar Stok')
@section('page-title', 'Histori Keluar Stok')
@section('breadcrumb', 'Gudang / Pengeluaran')

@section('content')
<div class="space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Gudang</label>
            <select name="warehouse_id" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Gudang</option>
                @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tipe</label>
            <select name="type" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua</option>
                <option value="transfer_out" {{ request('type') === 'transfer_out' ? 'selected' : '' }}>Transfer ke Toko</option>
                <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Pengeluaran Manual</option>
                <option value="adjust" {{ request('type') === 'adjust' ? 'selected' : '' }}>Penyesuaian</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Cari SKU / Produk</label>
            <input type="text" name="search" value="{{ request('search') }}"
                {{ !request('warehouse_id') ? 'disabled' : '' }}
                placeholder="{{ !request('warehouse_id') ? 'Pilih gudang dahulu…' : 'SKU atau nama produk…' }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-52 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
        </div>
        <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg self-end">Filter</button>
        <a href="{{ route('warehouse.outbound.index') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end">Reset</a>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Tipe</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Sisa</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($ledgers as $l)
                    @php $v = $l->variant; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-xs text-gray-400 whitespace-nowrap">{{ $l->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ optional($v)?->sku ?? '—' }}</td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ optional($v->product)->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-center">
                            @php $typeColors = ['transfer_out' => 'bg-orange-100 text-orange-700', 'out' => 'bg-red-100 text-red-700', 'adjust' => 'bg-purple-100 text-purple-700']; @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $typeColors[$l->type] ?? 'bg-gray-100 text-gray-600' }}">{{ $l->type }}</span>
                        </td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-red-600">{{ $l->qty }}</td>
                        <td class="px-4 py-2 text-right text-xs text-gray-600">{{ $l->qty_after }}</td>
                        <td class="px-4 py-2 text-xs text-gray-500 max-w-xs truncate">{{ $l->note ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Tidak ada data pengeluaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ledgers->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $ledgers->links() }}</div>
        @endif
    </div>

</div>
@endsection
