@extends('layouts.app')
@section('title', 'Master Warna')
@section('page-title', 'Master Warna')
@section('breadcrumb', 'Master Data / Warna')
@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Total {{ $colors->total() }} warna</p>
        @can('create master')
        <a href="{{ route('master.colors.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Tambah Warna
        </a>
        @endcan
    </div>
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex gap-3">
        <div class="flex-1"><input type="text" name="search" value="{{ request('search') }}" placeholder="Cari warna..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
        <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
        <a href="{{ route('master.colors.index') }}" class="bg-gray-100 text-gray-700 text-sm px-4 py-2 rounded-lg text-center">Reset</a>
    </form>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Swatch</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Kode</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Nama</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Hex</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($colors as $color)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $colors->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3">
                        <div class="w-6 h-6 rounded-full border border-gray-300" style="background-color: {{ $color->hex_code ?? '#ccc' }}"></div>
                    </td>
                    <td class="px-4 py-3"><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono text-xs">{{ $color->code }}</span></td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $color->name }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $color->hex_code ?? '-' }}</td>
                    <td class="px-4 py-3"><span class="{{ $color->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} text-xs px-2 py-0.5 rounded-full">{{ $color->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @can('update master')<a href="{{ route('master.colors.edit', $color) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium px-2 py-1 rounded hover:bg-indigo-50">Edit</a>@endcan
                            @can('delete master')
                            <form method="POST" action="{{ route('master.colors.destroy', $color) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium px-2 py-1 rounded hover:bg-red-50">Hapus</button>
                            </form>@endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada data warna</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($colors->hasPages())<div class="border-t border-gray-200 px-4 py-3">{{ $colors->links() }}</div>@endif
    </div>
</div>
@endsection
