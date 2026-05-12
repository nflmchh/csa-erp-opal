@extends('layouts.app')
@section('title', isset($returnReason) ? 'Edit Alasan Retur' : 'Tambah Alasan Retur')
@section('page-title', isset($returnReason) ? 'Edit Alasan Retur' : 'Tambah Alasan Retur')
@section('content')
<div class="max-w-sm">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ isset($returnReason) ? route('master.return-reasons.update', $returnReason) : route('master.return-reasons.store') }}" class="space-y-4">
            @csrf @if(isset($returnReason)) @method('PUT') @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Alasan <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $returnReason->name ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $returnReason->code ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500" maxlength="20">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Untuk <span class="text-red-500">*</span></label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="both" {{ old('type', $returnReason->type ?? 'both') == 'both' ? 'selected' : '' }}>Konsumen & Toko</option>
                    <option value="customer" {{ old('type', $returnReason->type ?? '') == 'customer' ? 'selected' : '' }}>Konsumen Saja</option>
                    <option value="store" {{ old('type', $returnReason->type ?? '') == 'store' ? 'selected' : '' }}>Toko Saja</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $returnReason->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-gray-300">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg">{{ isset($returnReason) ? 'Simpan' : 'Tambah' }}</button>
                <a href="{{ route('master.return-reasons.index') }}" class="bg-gray-100 text-gray-700 text-sm font-medium px-5 py-2 rounded-lg">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
