@extends('layouts.app')
@section('title', 'Master Ukuran')
@section('page-title', 'Master Ukuran')
@section('breadcrumb', 'Master Data / Ukuran')
@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Total {{ $sizes->total() }} ukuran</p>
        @can('create master')<a href="{{ route('master.sizes.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Tambah Ukuran</a>@endcan
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="w-10"></th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Kode</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Nama</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Urutan</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="sortable-tbody">
                @forelse($sizes as $size)
                <tr class="hover:bg-gray-50" data-id="{{ $size->id }}">
                    <td class="px-4 py-3 text-center">
                        <svg class="w-5 h-5 text-gray-400 cursor-move sort-handle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $sizes->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3"><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono text-xs">{{ $size->code }}</span></td>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $size->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $size->sort_order }}</td>
                    <td class="px-4 py-3"><span class="{{ $size->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} text-xs px-2 py-0.5 rounded-full">{{ $size->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @can('update master')<a href="{{ route('master.sizes.edit', $size) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium px-2 py-1 rounded hover:bg-indigo-50">Edit</a>@endcan
                            @can('delete master')<form method="POST" action="{{ route('master.sizes.destroy', $size) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium px-2 py-1 rounded hover:bg-red-50">Hapus</button></form>@endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada data ukuran</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($sizes->hasPages())<div class="border-t border-gray-200 px-4 py-3">{{ $sizes->links() }}</div>@endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('sortable-tbody');
        if (el) {
            Sortable.create(el, {
                handle: '.sort-handle',
                animation: 150,
                onEnd: function (evt) {
                    let orderedIds = Array.from(el.children).map(tr => tr.getAttribute('data-id')).filter(id => id);
                    if (orderedIds.length === 0) return;

                    fetch('{{ route('master.sizes.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ordered_ids: orderedIds })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            // Optional: reload the page to refresh "No" and "Urutan" columns, or let them just stay as is.
                            window.location.reload();
                        }
                    })
                    .catch(error => console.error('Error reordering:', error));
                }
            });
        }
    });
</script>
@endpush
