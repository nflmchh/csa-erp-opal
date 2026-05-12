@extends('layouts.app')
@section('title', isset($productType) ? 'Edit Jenis Produk' : 'Tambah Jenis Produk')
@section('page-title', isset($productType) ? 'Edit Jenis Produk' : 'Tambah Jenis Produk')
@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ isset($productType) ? route('master.product-types.update', $productType) : route('master.product-types.store') }}" class="space-y-4">
            @csrf @if(isset($productType)) @method('PUT') @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $productType->name ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $productType->code ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500" maxlength="10">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Tanpa Kategori —</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ old('category_id', $productType->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $productType->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-gray-300">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg">{{ isset($productType) ? 'Simpan' : 'Tambah' }}</button>
                <a href="{{ route('master.product-types.index') }}" class="bg-gray-100 text-gray-700 text-sm font-medium px-5 py-2 rounded-lg">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
