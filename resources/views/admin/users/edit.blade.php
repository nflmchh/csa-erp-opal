@extends('layouts.app')
@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Pengguna')
@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru <span class="text-gray-400 font-normal text-xs">(kosongkan jika tidak diubah)</span></label>
                <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                <select name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg space-y-4">
                <p class="text-sm font-semibold text-gray-800">Alokasi Penugasan (Pilih Salah Satu Tipe)</p>
                
                <!-- Alokasi Toko -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Toko</label>
                    <select name="store_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Tanpa Toko —</option>
                        @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ old('store_id', $user->store_id) == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Alokasi Gudang -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gudang</label>
                    <div class="bg-white border border-gray-300 rounded-lg p-3 max-h-32 overflow-y-auto space-y-2">
                        @php
                            // Ambil ID gudang yang sedang terhubung dengan user ini
                            $userWarehouses = $user->warehouses->pluck('id')->toArray();
                        @endphp
                        
                        @forelse($warehouses as $warehouse)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="warehouse_ids[]" value="{{ $warehouse->id }}" 
                                {{ in_array($warehouse->id, old('warehouse_ids', $userWarehouses)) ? 'checked' : '' }}
                                class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">{{ $warehouse->name }}</span>
                        </label>
                        @empty
                        <p class="text-sm text-gray-400 italic">Belum ada data gudang.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 mt-4">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer">Akun Aktif</label>
            </div>
            
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">Simpan Perubahan</button>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2 rounded-lg transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection