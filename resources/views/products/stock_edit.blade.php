@extends('layouts.app')
@section('title', 'Edit Stok')
@section('page-title', 'Edit Stok')
@section('breadcrumb', 'Produk / Edit Stok')

@section('content')
<div class="space-y-4">
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-lg font-medium border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="bg-blue-50 text-blue-600 p-4 rounded-lg font-medium border border-blue-200">
            {{ session('info') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 text-red-600 p-4 rounded-lg font-medium border border-red-200">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Toolbar Filter --}}
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3 items-end" id="filter-form">
            <div class="flex-1 min-w-[200px] max-w-sm">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Cari Produk</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama / Kode Model / SKU"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex-1 min-w-[200px] max-w-sm">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Lokasi Stok</label>
                <select name="location_combo" onchange="updateLocation(this.value)"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    {{ $locations->count() <= 1 ? 'readonly disabled' : '' }}>
                    @foreach($locations as $loc)
                        <option value="{{ $loc['type'] }}_{{ $loc['id'] }}" 
                            {{ $selectedLocationType == $loc['type'] && $selectedLocationId == $loc['id'] ? 'selected' : '' }}>
                            {{ $loc['name'] }} ({{ ucfirst($loc['type']) }})
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="location_type" id="location_type" value="{{ $selectedLocationType }}">
                <input type="hidden" name="location_id" id="location_id" value="{{ $selectedLocationId }}">
            </div>

            <div>
                <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white text-sm px-4 py-2 rounded-lg font-medium">Filter</button>
                <a href="{{ route('products.stock-edit.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-4 py-2 rounded-lg font-medium border border-gray-200 ml-2">Reset</a>
            </div>
        </form>
    </div>

    {{-- Form Edit Stok --}}
    <form action="{{ route('products.stock-edit.update') }}" method="POST" id="update-stock-form">
        @csrf
        <input type="hidden" name="location_type" value="{{ $selectedLocationType }}">
        <input type="hidden" name="location_id" value="{{ $selectedLocationId }}">

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <div class="text-sm text-gray-600 font-medium">
                    Menampilkan {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
                </button>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($products as $product)
                <div x-data="{ expanded: false }" class="hover:bg-gray-50 transition-colors">
                    {{-- Header Row (Clickable) --}}
                    <div @click="expanded = !expanded" class="p-4 cursor-pointer flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200">
                                @if($product->primaryImage())
                                    <img src="{{ $product->primaryImage()->url() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-gray-400">👕</span>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ $product->name }}</h3>
                                <p class="text-xs text-gray-500 font-mono">{{ $product->model_code }} | {{ $product->variants->count() }} Varian</p>
                            </div>
                        </div>
                        <div>
                            <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-200" 
                                :class="{ 'rotate-180': expanded }" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    {{-- Variants Expandable Section --}}
                    <div x-show="expanded" x-cloak>
                        <div class="px-4 pb-4 pt-2 border-t border-gray-100 bg-white">
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="w-full text-left text-sm whitespace-nowrap">
                                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-semibold border-b border-gray-200">
                                        <tr>
                                            <th class="px-4 py-3">SKU</th>
                                            <th class="px-4 py-3">Warna</th>
                                            <th class="px-4 py-3">Ukuran</th>
                                            <th class="px-4 py-3 w-48 text-right">Qty Tersedia</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($product->variants as $variant)
                                            @php
                                                // Because we eager loaded stocks with condition, it should only have 1 or 0
                                                $stock = $variant->stocks->first();
                                                $qty = $stock ? $stock->qty : 0;
                                            @endphp
                                            <tr>
                                                <td class="px-4 py-3 font-mono text-gray-700">{{ $variant->sku }}</td>
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-gray-100 text-xs font-medium text-gray-700">
                                                        <span class="w-3 h-3 rounded-full border border-gray-300" style="background-color: {{ $variant->color->hex_code }}"></span>
                                                        {{ $variant->color->name }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 font-medium">{{ $variant->size->name }}</td>
                                                <td class="px-4 py-3 text-right">
                                                    <input type="number" 
                                                        name="stocks[{{ $variant->id }}]" 
                                                        value="{{ $qty }}" 
                                                        min="0"
                                                        class="w-24 text-right border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-10 text-center text-gray-400">
                    Tidak ada produk yang ditemukan.
                </div>
                @endforelse
            </div>
            
            {{-- Footer Pagination --}}
            @if($products->hasPages())
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $products->links() }}
            </div>
            @endif
        </div>
    </form>
</div>

<script>
    function updateLocation(val) {
        if(!val) return;
        const parts = val.split('_');
        document.getElementById('location_type').value = parts[0];
        document.getElementById('location_id').value = parts[1];
        document.getElementById('filter-form').submit();
    }
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
