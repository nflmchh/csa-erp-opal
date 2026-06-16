@extends('layouts.app')
@section('title', 'Edit Varian')
@section('page-title', 'Edit Varian — ' . $variant?->sku)
@section('breadcrumb', 'Produk / ' . $variant->product->model_code . ' / Varian / Edit')

@section('content')
<div class="max-w-lg mx-auto">
<form method="POST" action="{{ route('products.variants.update', $variant) }}" class="space-y-5">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Edit Varian</h2>

        <div class="grid grid-cols-2 gap-4 text-sm pb-4 border-b border-gray-50">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">SKU</p>
                <p class="font-mono font-semibold text-gray-700">{{ $variant?->sku }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Produk</p>
                <p class="text-gray-700">{{ $variant?->product?->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Warna</p>
                <div class="flex items-center gap-2">
                    @if($variant->color->hex_code)
                        <div class="w-4 h-4 rounded-full border border-gray-300" style="background-color: {{ $variant->color->hex_code }}"></div>
                    @endif
                    <span>{{ $variant?->color?->name }}</span>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Ukuran</p>
                <p class="text-gray-700">{{ $variant?->size?->name }}</p>
            </div>
        </div>

        {{-- Image Picker --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Gambar Varian</label>
            <div class="grid grid-cols-6 gap-2" x-data="{ selectedId: '{{ $variant->product_image_id }}' }">
                @foreach($variant->product->images as $image)
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="product_image_id" value="{{ $image->id }}" 
                            x-model="selectedId" class="sr-only">
                        <div class="w-full aspect-square rounded-lg border-2 overflow-hidden transition-all duration-200"
                            :class="selectedId == '{{ $image->id }}' ? 'border-blue-600 ring-4 ring-blue-50 scale-[1.03] shadow-md' : 'border-transparent bg-gray-50 group-hover:border-gray-200'">
                            <img src="{{ $image->url() }}" class="w-full h-full object-cover">
                        </div>
                        <div class="absolute -top-1 -right-1 z-10" x-show="selectedId == '{{ $image->id }}'" x-transition>
                            <span class="bg-blue-600 text-white rounded-full p-1 shadow-lg flex items-center justify-center">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        </div>
                    </label>
                @endforeach
            </div>
            <p class="text-[10px] text-gray-400 mt-2">* Gambar diambil dari foto produk yang sudah diunggah. <br> 
            <span class="text-indigo-500 font-medium">INFO: Mengubah gambar ini akan otomatis memperbarui gambar semua varian warna {{ $variant?->color?->name }} di produk ini.</span></p>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                {{ $variant->is_active ? 'checked' : '' }}
                class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
            <label for="is_active" class="text-sm text-gray-700">Varian Aktif</label>
        </div>
    </div>

    <div class="flex items-center justify-between mt-6">
        <a href="{{ route('products.show', $variant->product_id) }}" class="text-sm text-gray-600 hover:underline">← Batal</a>
        <div class="flex gap-3">
            @can('update product')
            <button type="button" @click="if(confirm('Hapus varian {{ $variant?->sku }}?')) document.getElementById('delete-form').submit()" 
                class="bg-red-50 hover:bg-red-100 text-red-600 text-sm px-4 py-2 rounded-lg">
                Hapus
            </button>
            @endcan
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">
                Simpan
            </button>
        </div>
    </div>
</form>

<form id="delete-form" method="POST" action="{{ route('products.variants.destroy', $variant) }}" class="hidden">
    @csrf
    @method('DELETE')
</form>
</div>
@endsection
