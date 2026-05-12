@extends('layouts.app')
@section('title', 'Edit Role — ' . $role->name)
@section('page-title', 'Edit Role: ' . $role->name)
@section('breadcrumb', 'Administrasi / Role & Permission / Edit')

@section('content')
<div class="max-w-3xl mx-auto">
    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-5">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">Informasi Role</h2>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Role <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                    {{ $role->name === 'superadmin' ? 'readonly' : '' }}
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                        {{ $role->name === 'superadmin' ? 'bg-gray-50 text-gray-400 cursor-not-allowed' : '' }}
                        @error('name') border-red-400 @enderror">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                @if($role->name === 'superadmin')
                <p class="text-xs text-gray-400 mt-1">Nama role superadmin tidak dapat diubah.</p>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Permission</h2>
                @if($role->name !== 'superadmin')
                <div class="flex gap-2">
                    <button type="button" onclick="document.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = true)"
                        class="text-xs text-indigo-600 hover:underline">Pilih Semua</button>
                    <span class="text-gray-300">·</span>
                    <button type="button" onclick="document.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false)"
                        class="text-xs text-gray-500 hover:underline">Hapus Semua</button>
                </div>
                @else
                <p class="text-xs text-gray-400 italic">Superadmin memiliki akses ke semua fitur via Gate::before</p>
                @endif
            </div>

            @if($role->name === 'superadmin')
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-xs text-yellow-700">
                Superadmin bypass semua permission check via <code>Gate::before</code>. Permission di sini tidak berpengaruh pada akses superadmin.
            </div>
            @endif

            <div class="space-y-4">
                @foreach($permissions as $group => $perms)
                <div class="border border-gray-100 rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-600 capitalize">{{ $group }}</p>
                        @if($role->name !== 'superadmin')
                        <button type="button"
                            onclick="this.closest('.border').querySelectorAll('input').forEach(c => c.checked = !c.checked)"
                            class="text-xs text-gray-400 hover:text-gray-600">toggle</button>
                        @endif
                    </div>
                    <div class="px-4 py-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($perms->sortBy('name') as $perm)
                        <label class="flex items-center gap-2 {{ $role->name !== 'superadmin' ? 'cursor-pointer' : '' }}">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                {{ in_array($perm->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                {{ $role->name === 'superadmin' ? 'disabled' : '' }}
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
            @if($role->name !== 'superadmin')
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">
                Simpan Perubahan
            </button>
            @else
            <p class="text-xs text-gray-400">Role superadmin dikelola oleh sistem</p>
            @endif
        </div>
    </form>
</div>
@endsection
