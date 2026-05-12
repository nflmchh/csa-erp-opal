@extends('layouts.app')
@section('title', 'Tambah Role')
@section('page-title', 'Tambah Role Baru')
@section('breadcrumb', 'Administrasi / Role & Permission / Tambah')

@section('content')
<div class="max-w-3xl mx-auto">
    <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-5">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">Informasi Role</h2>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Role <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    placeholder="contoh: supervisor, operator"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-gray-400 mt-1">Gunakan huruf kecil dan spasi. Contoh: "kepala gudang"</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Permission</h2>
                <div class="flex gap-2">
                    <button type="button" onclick="document.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = true)"
                        class="text-xs text-indigo-600 hover:underline">Pilih Semua</button>
                    <span class="text-gray-300">·</span>
                    <button type="button" onclick="document.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false)"
                        class="text-xs text-gray-500 hover:underline">Hapus Semua</button>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($permissions as $group => $perms)
                <div class="border border-gray-100 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-600 capitalize">{{ $group }}</p>
                        <button type="button"
                            onclick="this.closest('.border').querySelectorAll('input').forEach(c => c.checked = !c.checked)"
                            class="text-xs text-gray-400 hover:text-gray-600">toggle</button>
                    </div>
                    <div class="px-4 py-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($perms->sortBy('name') as $perm)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                {{ in_array($perm->name, old('permissions', [])) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs text-gray-700">{{ $perm->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('admin.roles.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali</a>
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">
                Simpan Role
            </button>
        </div>
    </form>
</div>
@endsection
