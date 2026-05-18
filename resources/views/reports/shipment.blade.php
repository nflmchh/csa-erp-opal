@extends('layouts.app')
@section('title', 'Laporan Pengiriman')
@section('page-title', 'Laporan Pengiriman')
@section('breadcrumb', 'Laporan / Pengiriman')

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
                    <select name="warehouse_id"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ ($warehouseId ?? request('warehouse_id')) == $w->id ? 'selected' : '' }}>
                                {{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if(auth()->user()->hasRole('kepala toko'))
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                    <span class="block text-[10px] uppercase font-bold text-gray-400">Toko Aktif</span>
                    <span class="text-sm font-semibold text-gray-700">{{ $currentStore->name ?? 'Semua Toko' }}</span>
                    <input type="hidden" name="store_id" value="{{ $storeId }}">
                </div>
            @else
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Toko</label>
                    <select name="store_id"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Toko</option>
                        @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ ($storeId ?? request('store_id')) == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Terkirim</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Diterima</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg self-end">Filter</button>
            <a href="{{ route('reports.shipment') }}"
                class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end">Reset</a>
        </form>

        {{-- Export buttons --}}
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs text-gray-500 font-medium">Export:</span>
            <a href="{{ route('exports.shipment.pdf', array_merge(request()->query(), ['filename' => 'laporan-pengiriman-' . now()->format('Ymd-His') . '.pdf'])) }}" download="laporan-pengiriman.pdf"
                class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-2 rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                PDF
            </a>
            <a href="{{ route('exports.shipment.excel', array_merge(request()->query(), ['filename' => 'laporan-pengiriman-' . now()->format('Ymd-His') . '.xlsx'])) }}"
                class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-2 rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Excel
            </a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No. SHP</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Gudang</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Toko</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Items</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Total Qty</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($shipments as $shipment)
                            @php
                                $sc = ['draft' => 'bg-gray-100 text-gray-600', 'sent' => 'bg-blue-100 text-blue-700', 'received' => 'bg-green-100 text-green-700'];
                                $sl = ['draft' => 'Draft', 'sent' => 'Terkirim', 'received' => 'Diterima'];
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">
                                    {{ $shipment->shipment_no }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700">{{ $shipment->warehouse->name }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700">{{ $shipment->store->name }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="text-xs px-2 py-0.5 rounded-full {{ $sc[$shipment->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $sl[$shipment->status] ?? $shipment->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-xs text-gray-700">{{ $shipment->items->count() }}</td>
                                <td class="px-4 py-3 text-right text-xs font-semibold text-indigo-600">
                                    {{ $shipment->totalQtySent() }}</td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ $shipment->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('warehouse.shipments.show', $shipment) }}"
                                        class="text-xs text-indigo-600 hover:underline">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400">Tidak ada data pengiriman</td>
                            </tr>
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