@extends('layouts.app')
@section('title', 'Retur Konsumen')
@section('page-title', 'Retur Konsumen')
@section('breadcrumb', 'Retur / Konsumen')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between">
        <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end flex-1 mr-4">
            @if($stores->count())
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Toko</label>
                <select name="store_id" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Toko</option>
                    @foreach($stores as $s)
                    <option value="{{ $s->id }}" {{ request('store_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Menunggu</option>
                    <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Diproses</option>
                </select>
            </div>
            <a href="{{ route('returns.customer.index') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end">Reset</a>
        </form>

        @can('process customer return')
        <a href="{{ route('returns.customer.create') }}"
            class="shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-sm">
            + Buat Retur
        </a>
        @endcan
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No. Retur</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Toko</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Alasan</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Total Nilai</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Dibuat</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($returns as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $r->return_no }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">{{ $r->store->name }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $r->reason?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($r->status === 'processed')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Diproses</span>
                            @else
                            <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700">Menunggu</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-700">
                            Rp {{ number_format($r->totalValue(), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('returns.customer.show', $r) }}" class="text-xs text-indigo-600 hover:underline">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Tidak ada retur konsumen</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($returns->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $returns->links() }}</div>
        @endif
    </div>

</div>
@endsection
