@extends('layouts.app')
@section('title', 'Pengiriman')
@section('page-title', 'Pengiriman ke Toko')
@section('breadcrumb', 'Gudang / Pengiriman')

@section('content')
<div class="space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Gudang</label>
            <select name="warehouse_id" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Gudang</option>
                @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
        </div>
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
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                @foreach(\App\Models\Shipment::STATUS_LABELS as $val => $label)
                <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <a href="{{ route('warehouse.shipments.index') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end">Reset</a>

        @can('create shipment')
        <a href="{{ route('warehouse.shipments.create') }}"
            class="ml-auto bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg font-medium self-end">
            + Buat Pengiriman
        </a>
        @endcan
    </form>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No. Pengiriman</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Dari Gudang</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Ke Toko</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($shipments as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $s->shipment_no }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">{{ optional($s->warehouse)->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">{{ optional($s->store)->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $s->statusColor() }}">{{ $s->statusLabel() }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-700">{{ $s->items->sum('qty_sent') }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $s->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('warehouse.shipments.show', $s) }}" class="text-xs text-indigo-600 hover:underline">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada pengiriman</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shipments->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $shipments->links() }}</div>
        @endif
    </div>

</div>
@endsection
