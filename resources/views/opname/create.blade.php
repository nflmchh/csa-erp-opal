@extends('layouts.app')
@section('title', 'Buat Stock Opname')
@section('page-title', 'Buat Stock Opname')
@section('breadcrumb', 'Opname / Buat')

@section('content')
<div class="max-w-lg mx-auto">
    <form method="POST" action="{{ route('opname.store') }}" class="space-y-5">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">Informasi Opname</h2>
            <p class="text-xs text-gray-500">
                Sistem akan mengambil snapshot jumlah stok saat ini sebagai <strong>qty_system</strong>.
                Anda akan memasukkan jumlah fisik aktual pada tahap berikutnya.
            </p>

            <div x-data="{ locType: '{{ old('location_type', 'warehouse') }}' }">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Lokasi <span class="text-red-500">*</span></label>
                <div class="flex gap-3 mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="location_type" value="warehouse" x-model="locType"
                            class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Gudang</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="location_type" value="store" x-model="locType"
                            class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Toko</span>
                    </label>
                </div>

                <div x-show="locType === 'warehouse'" x-transition>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Gudang <span class="text-red-500">*</span></label>
                    <select name="location_id"
                        :required="locType === 'warehouse'"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih gudang…</option>
                        @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ old('location_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="locType === 'store'" x-transition style="display:none">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Toko <span class="text-red-500">*</span></label>
                    <select name="location_id"
                        :required="locType === 'store'"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih toko…</option>
                        @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ old('location_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @error('location_id')<p class="text-xs text-red-500">{{ $message }}</p>@enderror

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
                <textarea name="notes" rows="2" maxlength="500"
                    placeholder="Keterangan opname…"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('opname.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali</a>
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">
                Buat Opname
            </button>
        </div>
    </form>
</div>
@endsection
