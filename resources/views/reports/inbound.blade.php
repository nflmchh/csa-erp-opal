@extends('layouts.app')
@section('title', 'Laporan Barang Masuk')
@section('page-title', 'Laporan Barang Masuk')
@section('breadcrumb', 'Laporan / Barang Masuk')

@section('content')
<div class="space-y-4">

    {{-- Filter Form --}}
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
            <select name="warehouse_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Gudang</option>
                @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}" {{ $warehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
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

        <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Filter</button>
        <a href="{{ route('reports.inbound') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg hover:bg-gray-200 transition">Reset</a>
    </form>

    {{-- Export buttons --}}
    <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs text-gray-500 font-medium">Export:</span>
        <a href="{{ route('exports.inbound.pdf', request()->query()) }}" target="_blank"
            class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-2 rounded-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            PDF
        </a>
        <a href="{{ route('exports.inbound.excel', request()->query()) }}"
            class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-2 rounded-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Excel
        </a>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No. Ref</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Gudang</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Supplier</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Items</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Total Qty</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inbounds as $inbound)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $inbound->reference_no }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">{{ $inbound->warehouse->name }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $inbound->supplier_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ $inbound->status === 'received' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $inbound->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-700">{{ $inbound->items->count() }}</td>
                        <td class="px-4 py-3 text-right text-xs font-semibold text-gray-800">{{ number_format($inbound->items->sum('qty')) }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $inbound->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('warehouse.inbound.show', $inbound) }}" class="text-xs text-indigo-600 hover:underline">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">Tidak ada data penerimaan barang</td></tr>
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
