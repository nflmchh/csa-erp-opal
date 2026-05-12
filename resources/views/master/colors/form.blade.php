@extends('layouts.app')
@section('title', isset($color) ? 'Edit Warna' : 'Tambah Warna')
@section('page-title', isset($color) ? 'Edit Warna' : 'Tambah Warna')
@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ isset($color) ? route('master.colors.update', $color) : route('master.colors.store') }}" class="space-y-4">
            @csrf @if(isset($color)) @method('PUT') @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Warna <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $color->name ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $color->code ?? '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500" maxlength="10">
                <p class="text-xs text-gray-400 mt-1">Digunakan untuk generate SKU. Contoh: BLK, WHT, RED</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hex Color</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="hex_code" value="{{ old('hex_code', $color->hex_code ?? '#000000') }}" class="h-10 w-20 border border-gray-300 rounded-lg p-1 cursor-pointer">
                    <input type="text" id="hex_text" value="{{ old('hex_code', $color->hex_code ?? '#000000') }}" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="#000000" readonly>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $color->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-gray-300">
                <label for="is_active" class="text-sm font-medium text-gray-700">Aktif</label>
            </div>
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg">{{ isset($color) ? 'Simpan' : 'Tambah' }}</button>
                <a href="{{ route('master.colors.index') }}" class="bg-gray-100 text-gray-700 text-sm font-medium px-5 py-2 rounded-lg">Batal</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
document.querySelector('[name="hex_code"]').addEventListener('input', function() {
    document.getElementById('hex_text').value = this.value;
});
</script>
@endpush
@endsection
