@extends('layouts.app')
@section('title', isset($paymentMethod) ? 'Edit Metode Bayar' : 'Tambah Metode Bayar')
@section('page-title', isset($paymentMethod) ? 'Edit Metode Bayar' : 'Tambah Metode Bayar')
@section('content')
<div class="max-w-sm">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ isset($paymentMethod) ? route('master.payment-methods.update', $paymentMethod) : route('master.payment-methods.store') }}" class="space-y-4">
            @csrf @if(isset($paymentMethod)) @method('PUT') @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $paymentMethod->name ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $paymentMethod->code ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500" maxlength="20">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe <span class="text-red-500">*</span></label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach(['cash' => 'Tunai', 'transfer' => 'Transfer Bank', 'qris' => 'QRIS', 'card' => 'Kartu', 'other' => 'Lainnya'] as $val => $label)
                    <option value="{{ $val }}" {{ old('type', $paymentMethod->type ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $paymentMethod->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-gray-300">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg">{{ isset($paymentMethod) ? 'Simpan' : 'Tambah' }}</button>
                <a href="{{ route('master.payment-methods.index') }}" class="bg-gray-100 text-gray-700 text-sm font-medium px-5 py-2 rounded-lg">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
