@extends('layouts.app')
@section('title', 'Nilai Stok')
@section('page-title', 'Nilai Stok')
@section('breadcrumb', 'Keuangan / Nilai Stok')

@section('content')
<div class="space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        @if($isGlobal || (!empty($storeIds) && !empty($warehouseIds)))
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Lokasi</label>
            <select name="location_type" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="store"     {{ $locationType === 'store'     ? 'selected' : '' }}>Toko</option>
                <option value="warehouse" {{ $locationType === 'warehouse' ? 'selected' : '' }}>Gudang</option>
            </select>
        </div>
        @else
            <input type="hidden" name="location_type" value="{{ $locationType }}">
        @endif
        @if($locationType === 'store')
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
        <a href="{{ route('finance.stock-value') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end">Reset</a>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-8">
        <div>
            <p class="text-xs text-gray-400">Total Nilai Stok</p>
            <p class="text-2xl font-bold text-indigo-600 mt-1">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">Lokasi</p>
            <p class="text-sm font-medium text-gray-700 mt-1">
                {{ $locationType === 'store' ? 'Toko' : 'Gudang' }}
                @if($locationId)
                    — {{ $stores->find($locationId)?->name ?? 'ID ' . $locationId }}
                @endif
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Warna / Ukuran</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Harga Jual</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($stocks as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $s?->sku }}</td>
                        <td class="px-4 py-3 text-xs font-medium text-gray-800">{{ $s->product_name }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $s->color_name }} / {{ $s->size_name }}</td>
                        <td class="px-4 py-3 text-right text-xs text-gray-700">{{ number_format($s->qty) }}</td>
                        <td class="px-4 py-3 text-right text-xs text-gray-700">
                            Rp {{ number_format($s->sell_price + $s->price_adjustment, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-xs font-semibold text-gray-900">
                            Rp {{ number_format($s->total_value, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Tidak ada stok</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
        <div class="border-t border-gray-200 px-4 py-3 flex items-center justify-between">
            <p class="text-xs text-gray-400">Menampilkan {{ $stocks->firstItem() }}–{{ $stocks->lastItem() }} dari {{ $stocks->total() }} item</p>
            {{ $stocks->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
