@extends('layouts.app')
@section('title', 'Laporan Stok')
@section('page-title', 'Laporan Stok')
@section('breadcrumb', 'Laporan / Stok')

@section('content')
<div class="space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Lokasi</label>
            <select name="location_type" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="warehouse" {{ $locationType === 'warehouse' ? 'selected' : '' }}>Gudang</option>
                <option value="store"     {{ $locationType === 'store'     ? 'selected' : '' }}>Toko</option>
            </select>
        </div>
        @if($locationType === 'warehouse')
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Gudang</label>
            <select name="location_id" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Gudang</option>
                @foreach($warehouses as $w)
                <option value="{{ $w->id }}" {{ $locationId == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
        @else
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Toko</label>
            <select name="location_id" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Toko</option>
                @foreach($stores as $s)
                <option value="{{ $s->id }}" {{ $locationId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <a href="{{ route('reports.stock') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end">Reset</a>
    </form>

    {{-- Export buttons --}}
    <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs text-gray-500 font-medium">Export:</span>
        <a href="{{ route('exports.stock.pdf', request()->query()) }}" target="_blank"
            class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-2 rounded-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            PDF
        </a>
        <a href="{{ route('exports.stock.excel', request()->query()) }}"
            class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-2 rounded-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Excel
        </a>
        <a href="{{ route('exports.stock.csv', request()->query()) }}"
            class="inline-flex items-center gap-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium px-3 py-2 rounded-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            CSV
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-6">
        <div>
            <p class="text-xs text-gray-400">Total SKU</p>
            <p class="text-xl font-bold text-gray-900">{{ $stocks->total() }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">Total Qty</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($totalQty) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Brand</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Warna / Ukuran</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($stocks as $stock)
                    @php $v = $stock->variant; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $v->sku }}</td>
                        <td class="px-4 py-3 text-xs text-gray-800 font-medium">{{ $v->product->name }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $v->product->brand?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $v->color->name }} / {{ $v->size->name }}</td>
                        <td class="px-4 py-3 text-right text-xs font-semibold text-gray-800">{{ number_format($stock->qty) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">Tidak ada stok</td></tr>
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
