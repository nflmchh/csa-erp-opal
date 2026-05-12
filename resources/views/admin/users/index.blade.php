@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')
@section('breadcrumb', 'Administrasi / Pengguna')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Total {{ $users->total() }} pengguna</p>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </a>
    </div>
    
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Nama</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Role</th>
                        <!-- Kolom Baru: Alokasi -->
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Alokasi Penugasan</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Login Terakhir</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $users->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-xs font-bold uppercase shrink-0">{{ substr($user->name, 0, 2) }}</div>
                                <div>
                                    <span class="font-medium text-gray-900 block">{{ $user->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $user->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @foreach($user->getRoleNames() as $role)
                            <span class="bg-indigo-100 text-indigo-700 text-[10px] px-2 py-0.5 rounded-md font-medium uppercase tracking-wide">{{ $role }}</span>
                            @endforeach
                        </td>
                        
                        <!-- Data Alokasi Penugasan -->
                        <td class="px-4 py-3">
                            @if($user->store_id)
                                <span class="bg-blue-50 text-blue-700 border border-blue-200 text-xs px-2 py-1 rounded">Toko: {{ $user->store->name ?? '-' }}</span>
                            @elseif($user->warehouses->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->warehouses as $w)
                                        <span class="bg-purple-50 text-purple-700 border border-purple-200 text-xs px-2 py-1 rounded">Gudang: {{ $w->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 text-xs italic">— Pusat/Tanpa Lokasi —</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <span class="{{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} text-xs px-2 py-1 rounded-full font-medium">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $user->last_login_at?->diffForHumans() ?? 'Belum pernah' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium px-2 py-1 rounded hover:bg-indigo-50">Edit</a>
                                @if($user->id !== Auth::id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus user {{ addslashes($user->name) }}?')">@csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium px-2 py-1 rounded hover:bg-red-50">Hapus</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada pengguna</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())<div class="border-t border-gray-200 px-4 py-3">{{ $users->links() }}</div>@endif
    </div>
</div>
@endsection