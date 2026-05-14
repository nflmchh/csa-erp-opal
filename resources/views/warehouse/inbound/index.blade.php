@extends('layouts.app')
@section('title', 'Penerimaan Barang')
@section('page-title', 'Penerimaan Barang')
@section('breadcrumb', 'Gudang / Penerimaan')

@section('content')
<div class="space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        @if(auth()->user()->hasRole('admin gudang'))
        <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
            <span class="block text-[10px] uppercase font-bold text-gray-400">Gudang Aktif</span>
            <span class="text-sm font-semibold text-gray-700">{{ $currentWarehouse->name ?? 'Semua Gudang' }}</span>
            <input type="hidden" name="warehouse_id" value="{{ $warehouseId }}">
        </div>
        @else
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Gudang</label>
            <select name="warehouse_id" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Gudang</option>
                @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Diterima</option>
            </select>
        </div>
        <a href="{{ route('warehouse.inbound.index') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end">Reset</a>

        @can('create warehouse stock')
        <a href="{{ route('warehouse.inbound.create') }}"
            class="ml-auto bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg font-medium self-end">
            + Penerimaan Baru
        </a>
        @endcan
    </form>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No. Referensi</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Gudang</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Supplier</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Diterima</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Dibuat</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inbounds as $ib)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $ib->reference_no }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">{{ optional($ib->warehouse)->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $ib->supplier_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $ib->status === 'received' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $ib->status === 'received' ? 'Diterima' : 'Draft' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $ib->received_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $ib->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('warehouse.inbound.show', $ib) }}" class="text-xs text-indigo-600 hover:underline">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada penerimaan barang</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($inbounds->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $inbounds->links() }}</div>
        @endif
    </div>

</div>
@endsection
