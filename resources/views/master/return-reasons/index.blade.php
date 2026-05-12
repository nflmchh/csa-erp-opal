@extends('layouts.app')
@section('title', 'Alasan Retur')
@section('page-title', 'Alasan Retur')
@section('breadcrumb', 'Master Data / Alasan Retur')
@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Total {{ $returnReasons->total() }} alasan</p>
        @can('create master')<a href="{{ route('master.return-reasons.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Tambah</a>@endcan
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200"><tr>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Kode</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Nama</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Berlaku Untuk</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @php $typeLabels = ['customer' => 'Konsumen', 'store' => 'Toko', 'both' => 'Keduanya'] @endphp
                @forelse($returnReasons as $rr)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $returnReasons->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3"><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono text-xs">{{ $rr->code }}</span></td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $rr->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $typeLabels[$rr->type] ?? $rr->type }}</td>
                    <td class="px-4 py-3"><span class="{{ $rr->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} text-xs px-2 py-0.5 rounded-full">{{ $rr->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @can('update master')<a href="{{ route('master.return-reasons.edit', $rr) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium px-2 py-1 rounded hover:bg-indigo-50">Edit</a>@endcan
                            @can('delete master')<form method="POST" action="{{ route('master.return-reasons.destroy', $rr) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium px-2 py-1 rounded hover:bg-red-50">Hapus</button></form>@endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada alasan retur</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($returnReasons->hasPages())<div class="border-t border-gray-200 px-4 py-3">{{ $returnReasons->links() }}</div>@endif
    </div>
</div>
@endsection
