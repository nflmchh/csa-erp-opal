@extends('layouts.app')

@section('title', isset($brand) ? 'Edit Brand' : 'Tambah Brand')
@section('page-title', isset($brand) ? 'Edit Brand' : 'Tambah Brand')
@section('breadcrumb', 'Master Data / Brand / ' . (isset($brand) ? 'Edit' : 'Tambah'))

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">

        <form method="POST"
            action="{{ isset($brand) ? route('master.brands.update', $brand) : route('master.brands.store') }}"
            enctype="multipart/form-data"
            class="space-y-4">
            @csrf
            @if(isset($brand)) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Brand <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $brand->name ?? '') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                    placeholder="Contoh: SevenKey Original">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Brand <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $brand->code ?? '') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('code') border-red-500 @enderror font-mono uppercase"
                    placeholder="Contoh: SKO" maxlength="10">
                <p class="text-xs text-gray-400 mt-1">Maks 10 karakter, digunakan untuk generate SKU produk</p>
                @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Deskripsi singkat brand (opsional)">{{ old('description', $brand->description ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logo Brand</label>
                <input type="file" name="logo" accept="image/*"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, WebP. Maks 2MB</p>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                    {{ old('is_active', $brand->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Brand Aktif</label>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                    {{ isset($brand) ? 'Simpan Perubahan' : 'Tambah Brand' }}
                </button>
                <a href="{{ route('master.brands.index') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>
@endsection
