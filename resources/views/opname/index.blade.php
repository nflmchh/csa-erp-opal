@extends('layouts.app')
@section('title', 'Stock Opname')
@section('page-title', 'Stock Opname')
@section('breadcrumb', 'Opname')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between">
        <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end flex-1 mr-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Lokasi</label>
                <select name="location_type" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua</option>
                    <option value="warehouse" {{ request('location_type') === 'warehouse' ? 'selected' : '' }}>Gudang</option>
                    <option value="store"     {{ request('location_type') === 'store'     ? 'selected' : '' }}>Toko</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Disubmit</option>
                    <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <a href="{{ route('opname.index') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end">Reset</a>
        </form>

        @can('create stock opname')
            @if(auth()->user()->hasAnyRole(['superadmin', 'super admin', 'owner', 'finance']))
                {{-- Tombol Normal untuk Manajemen (Bisa pilih lokasi bebas) --}}
                <a href="{{ route('opname.create') }}"
                    class="shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-sm">
                    + Buat Opname
                </a>
            @else
                {{-- Tombol Pintasan (Auto-Create) untuk Kepala Toko & Admin Gudang --}}
                @php
                    $user = auth()->user();
                    $isStore = $user->hasRole('kepala toko');
                    $locType = $isStore ? 'store' : 'warehouse';
                    // Ambil ID lokasi pertama yang ditugaskan ke user ini secara otomatis
                    $locId = $isStore ? $user->stores->first()?->id : $user->warehouses->first()?->id;
                @endphp
                
                @if($locId)
                <form method="POST" action="{{ route('opname.store') }}" class="m-0">
                    @csrf
                    {{-- Suntikkan data lokasi secara rahasia ke Controller --}}
                    <input type="hidden" name="location_type" value="{{ $locType }}">
                    <input type="hidden" name="location_id" value="{{ $locId }}">
                    
                    <button type="submit" onclick="return confirm('Sistem akan mengambil snapshot stok {{ $locType === 'store' ? 'Toko' : 'Gudang' }} Anda saat ini. Mulai proses Stock Opname sekarang?')"
                        class="shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-sm transition-transform hover:scale-105 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Mulai Stock Opname
                    </button>
                </form>
                @else
                <span class="text-xs text-red-500 font-medium bg-red-50 px-3 py-2 rounded-lg border border-red-100">
                    Akses ditolak: Anda belum ditugaskan ke Lokasi manapun.
                </span>
                @endif
            @endif
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No. Opname</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Lokasi</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Items</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Dibuat oleh</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($opnames as $o)
                    @php
                        $statusColors = ['draft'=>'bg-gray-100 text-gray-600','submitted'=>'bg-blue-100 text-blue-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700'];
                        $statusLabels = ['draft'=>'Draft','submitted'=>'Disubmit','approved'=>'Disetujui','rejected'=>'Ditolak'];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $o->opname_no }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">
                            <span class="text-gray-400">{{ $o->location_type === 'warehouse' ? 'Gudang' : 'Toko' }}:</span>
                            {{ $o->location()?->name ?? 'ID ' . $o->location_id }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColors[$o->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabels[$o->status] ?? $o->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-700">{{ $o->items_count ?? $o->items->count() }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $o->creator?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('opname.show', $o) }}" class="text-xs text-indigo-600 hover:underline">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Tidak ada data opname</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($opnames->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $opnames->links() }}</div>
        @endif
    </div>

</div>
@endsection
