@extends('layouts.app')
@section('title', 'Tambah Varian')
@section('page-title', 'Tambah Varian — ' . $product->name)
@section('breadcrumb', 'Produk / ' . $product->model_code . ' / Varian Baru')

@section('content')
<div class="max-w-4xl mx-auto">

    <form method="POST" action="{{ route('products.variants.store', $product) }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Pilih Kombinasi Varian</h2>
                <span class="text-xs font-mono text-gray-400 bg-gray-100 px-2 py-1 rounded">{{ $product->name }}</span>
            </div>

            <p class="text-sm text-gray-500">Centang warna dan ukuran yang ingin dibuat. Sistem akan otomatis membuat semua kombinasi yang dipilih dan men-generate SKU.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Colors --}}
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center justify-between">
                        Warna
                        <button type="button" @click="document.querySelectorAll('.color-cb').forEach(c => c.checked = true)" class="text-[10px] text-indigo-600 font-normal hover:underline">Pilih Semua</button>
                    </h3>
                    <div class="grid grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($colors as $color)
                        <label class="flex items-center gap-3 p-2 border border-gray-100 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors group">
                            <input type="checkbox" name="color_ids[]" value="{{ $color->id }}" class="color-cb w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <div class="flex items-center gap-2">
                                @if($color->hex_code)
                                <div class="w-3 h-3 rounded-full border border-gray-200" style="background-color: {{ $color->hex_code }}"></div>
                                @endif
                                <span class="text-xs text-gray-700 group-hover:text-gray-900">{{ $color->name }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Sizes --}}
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2 flex items-center justify-between">
                        Ukuran
                        <button type="button" @click="document.querySelectorAll('.size-cb').forEach(c => c.checked = true)" class="text-[10px] text-indigo-600 font-normal hover:underline">Pilih Semua</button>
                    </h3>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($sizes as $size)
                        <label class="flex items-center gap-3 p-2 border border-gray-100 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors group">
                            <input type="checkbox" name="size_ids[]" value="{{ $size->id }}" class="size-cb w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="text-xs text-gray-700 group-hover:text-gray-900 font-bold uppercase">{{ $size->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between bg-white p-6 rounded-xl border border-gray-200">
            <a href="{{ route('products.show', $product) }}" class="text-sm text-gray-600 hover:underline">← Kembali ke Produk</a>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-3 rounded-xl text-sm shadow-lg shadow-indigo-200 transition-all active:scale-95">
                    BUAT VARIAN TERPILIH
                </button>
            </div>
        </div>
    </form>

</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #ccc; }
</style>
@endsection
