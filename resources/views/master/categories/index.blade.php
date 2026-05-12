@extends('layouts.app')
@section('title', 'Master Kategori')
@section('page-title', 'Master Kategori')
@section('breadcrumb', 'Master Data / Kategori')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Total {{ $categories->total() }} kategori</p>
        @can('create master')
        <a href="{{ route('master.categories.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kategori
        </a>
        @endcan
    </div>
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kategori..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
        <a href="{{ route('master.categories.index') }}" class="bg-gray-100 text-gray-700 text-sm px-4 py-2 rounded-lg text-center">Reset</a>
    </form>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Kode</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Nama</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Parent</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $category)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $categories->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3"><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono text-xs">{{ $category->code }}</span></td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $category->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $category->parent?->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @if($category->is_active)
                        <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">Aktif</span>
                        @else
                        <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @can('update master')<a href="{{ route('master.categories.edit', $category) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium px-2 py-1 rounded hover:bg-indigo-50">Edit</a>@endcan
                            @can('delete master')
                            <form method="POST" action="{{ route('master.categories.destroy', $category) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium px-2 py-1 rounded hover:bg-red-50">Hapus</button>
                            </form>@endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada data kategori</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($categories->hasPages())<div class="border-t border-gray-200 px-4 py-3">{{ $categories->links() }}</div>@endif
    </div>
</div>
@endsection
