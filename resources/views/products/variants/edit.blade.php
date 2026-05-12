@extends('layouts.app')
@section('title', 'Edit Varian')
@section('page-title', 'Edit Varian — ' . $variant->sku)
@section('breadcrumb', 'Produk / ' . $variant->product->model_code . ' / Varian / Edit')

@section('content')
<div class="max-w-lg mx-auto">
<form method="POST" action="{{ route('products.variants.update', $variant) }}" class="space-y-5">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Edit Varian</h2>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">SKU</p>
                <p class="font-mono font-semibold text-gray-700">{{ $variant->sku }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Produk</p>
                <p class="text-gray-700">{{ $variant->product->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Warna</p>
                <div class="flex items-center gap-2">
                    @if($variant->color->hex_code)
                    <div class="w-4 h-4 rounded-full border border-gray-300" style="background-color: {{ $variant->color->hex_code }}"></div>
                    @endif
                    <span>{{ $variant->color->name }}</span>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Ukuran</p>
                <p class="text-gray-700">{{ $variant->size->name }}</p>
            </div>
        </div>

        <!-- <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Penyesuaian Harga</label>
            <div class="relative">
                <span class="absolute left-3 top-2.5 text-sm text-gray-400">Rp</span>
                <input type="number" name="price_adjustment"
                    value="{{ old('price_adjustment', $variant->price_adjustment) }}"
                    step="500"
                    class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <p class="text-xs text-gray-400 mt-1">Ditambahkan ke harga jual produk induk. Bisa negatif.</p>
        </div> -->

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                {{ $variant->is_active ? 'checked' : '' }}
                class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
            <label for="is_active" class="text-sm text-gray-700">Varian Aktif</label>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('products.show', $variant->product_id) }}" class="text-sm text-gray-600 hover:underline">← Batal</a>
        <div class="flex gap-3">
            @can('update product')
            <form method="POST" action="{{ route('products.variants.destroy', $variant) }}" class="inline"
                onsubmit="return confirm('Hapus varian {{ $variant->sku }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 text-sm px-4 py-2 rounded-lg">Hapus</button>
            </form>
            @endcan
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">
                Simpan
            </button>
        </div>
    </div>
</form>
</div>
@endsection
