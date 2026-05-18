@extends('layouts.app')
@section('title', 'Laporan Stok')
@section('page-title', 'Laporan Stok')
@section('breadcrumb', 'Laporan / Stok')

@section('content')
    <div class="space-y-4">

        {{-- Info Lokasi (read-only, sesuai role) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-4 items-center">
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500">Tipe Lokasi:</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold
                    {{ $locationType === 'warehouse' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                    {{ $locationType === 'warehouse' ? '🏭 Gudang' : '🏪 Toko' }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500">Lokasi:</span>
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-700">
                    @if($currentLocation)
                        {{ $currentLocation->name }}
                    @else
                        Semua {{ $locationType === 'warehouse' ? 'Gudang' : 'Toko' }}
                    @endif
                </span>
            </div>
        </div>

        {{-- Filter Produk / Brand / Warna / Ukuran --}}
        <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4">
            {{-- Pertahankan parameter lokasi yang sudah ada (hidden) --}}
            <input type="hidden" name="location_type" value="{{ $locationType }}">
            @if($locationId)
                <input type="hidden" name="location_id" value="{{ $locationId }}">
            @endif

            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Filter Export</p>
            <div class="flex flex-wrap gap-3 items-end">
                {{-- Filter Produk --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Produk</label>
                    <input type="text" name="search_product" value="{{ request('search_product') }}"
                        placeholder="Cari nama produk..."
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-44">
                </div>

                {{-- Filter Brand --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Brand</label>
                    <select name="brand_id"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Warna --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Warna</label>
                    <select name="color_id"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Warna</option>
                        @foreach($colors as $color)
                            <option value="{{ $color->id }}" {{ request('color_id') == $color->id ? 'selected' : '' }}>
                                {{ $color->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Ukuran --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Ukuran</label>
                    <select name="size_id"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Ukuran</option>
                        @foreach($sizes as $size)
                            <option value="{{ $size->id }}" {{ request('size_id') == $size->id ? 'selected' : '' }}>
                                {{ $size->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Filter</button>
                <a href="{{ route('reports.stock') }}"
                    class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg hover:bg-gray-200 transition">Reset</a>
            </div>
        </form>

        {{-- Export buttons --}}
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs text-gray-500 font-medium">Export:</span>
            <a href="{{ route('exports.stock.pdf', array_merge(request()->query(), ['filename' => 'laporan-stok-' . now()->format('Ymd-His') . '.pdf'])) }}" download="laporan-stok.pdf"
                class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-2 rounded-lg">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                PDF
            </a>
            <a href="{{ route('exports.stock.excel', array_merge(request()->query(), ['filename' => 'laporan-stok-' . now()->format('Ymd-His') . '.xlsx'])) }}"
                class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-2 rounded-lg">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Excel
            </a>
            <a href="{{ route('exports.stock.csv', array_merge(request()->query(), ['filename' => 'laporan-stok-' . now()->format('Ymd-His') . '.csv'])) }}"
                class="inline-flex items-center gap-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium px-3 py-2 rounded-lg">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
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
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Warna / Ukuran
                            </th>
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
                                <td class="px-4 py-3 text-right text-xs font-semibold text-gray-800">
                                    {{ number_format($stock->qty) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-gray-400">Tidak ada stok</td>
                            </tr>
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