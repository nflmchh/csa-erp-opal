@extends('layouts.app')
@section('title', isset($size) ? 'Edit Ukuran' : 'Tambah Ukuran')
@section('page-title', isset($size) ? 'Edit Ukuran' : 'Tambah Ukuran')
@section('content')
<div class="max-w-sm">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ isset($size) ? route('master.sizes.update', $size) : route('master.sizes.store') }}" class="space-y-4">
            @csrf @if(isset($size)) @method('PUT') @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ukuran <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $size->name ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" maxlength="10" placeholder="Contoh: S, M, L, XL, 30">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $size->code ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500" maxlength="10">
                <p class="text-xs text-gray-400 mt-1">Digunakan pada SKU produk</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Urutan Tampil</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $size->sort_order ?? 0) }}" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $size->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-gray-300">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg">{{ isset($size) ? 'Simpan' : 'Tambah' }}</button>
                <a href="{{ route('master.sizes.index') }}" class="bg-gray-100 text-gray-700 text-sm font-medium px-5 py-2 rounded-lg">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
