@extends('layouts.app')
@section('title', 'Stok Gudang')
@section('page-title', 'Stok Gudang')
@section('breadcrumb', 'Gudang / Stok')

@section('content')
<div class="space-y-4">

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Gudang</label>
            <select name="warehouse_id" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Brand</label>
            <select name="brand_id" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Brand</option>
                @foreach($brands as $b)
                <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Cari SKU / Produk</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="SKU atau nama produk…"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-52 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
        <a href="{{ route('warehouse.stock.index') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg">Reset</a>

        <div class="ml-auto flex gap-2">
            @can('create warehouse stock')
            <a href="{{ route('warehouse.inbound.create') }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg font-medium">
                + Penerimaan Barang
            </a>
            @endcan
        </div>
    </form>

    @if($warehouse)
    <div class="text-sm text-gray-500">
        Stok aktif di <span class="font-semibold text-gray-700">{{ optional($warehouse)->name ?? '—' }}</span>
        — <span class="font-semibold text-gray-700">{{ $stocks->total() }}</span> SKU
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Warna</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Ukuran</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($stocks as $stock)
                    @php
                        $v = $stock->variant;
                        $p = optional($v)->product;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ optional($v)?->sku ?? '—' }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('products.show', $p) }}" class="text-xs text-indigo-600 hover:underline font-medium">{{ optional($p)->name ?? '—' }}</a>
                            <p class="text-xs text-gray-400">{{ optional($p->brand)->code ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-1.5">
                                @if($v->color->hex_code)
                                <div class="w-3.5 h-3.5 rounded-full border border-gray-300" style="background-color: {{ $v->color->hex_code }}"></div>
                                @endif
                                <span class="text-xs text-gray-700">{{ $v?->color?->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ $v?->size?->name }}</td>
                        <td class="px-4 py-2 text-right">
                            <span class="text-sm font-bold {{ $stock->qty <= 3 ? 'text-red-600' : ($stock->qty <= 10 ? 'text-yellow-600' : 'text-gray-900') }}">
                                {{ $stock->qty }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">Tidak ada stok ditemukan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $stocks->links() }}</div>
        @endif
    </div>

</div>
@endsection
