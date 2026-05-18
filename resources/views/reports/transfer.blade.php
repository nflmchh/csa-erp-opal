@extends('layouts.app')
@section('title', 'Laporan Transfer Toko')
@section('page-title', 'Laporan Transfer Toko')
@section('breadcrumb', 'Laporan / Transfer')

@section('content')
    <div class="space-y-4">

        <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Toko Asal</label>
                <select name="from_store_id"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ request('from_store_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Toko Tujuan</label>
                <select name="to_store_id"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua</option>
                    @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ request('to_store_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua</option>
                    @foreach(\App\Models\Transfer::STATUS_LABELS as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
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
            <a href="{{ route('reports.transfer') }}"
                class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end">Reset</a>
        </form>

        {{-- Export buttons --}}
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs text-gray-500 font-medium">Export:</span>
            <a href="{{ route('exports.transfer.pdf', array_merge(request()->query(), ['filename' => 'laporan-transfer-toko-' . now()->format('Ymd-His') . '.pdf'])) }}" download="laporan-transfer-toko.pdf"
                class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-2 rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                PDF
            </a>
            <a href="{{ route('exports.transfer.excel', array_merge(request()->query(), ['filename' => 'laporan-transfer-toko-' . now()->format('Ymd-His') . '.xlsx'])) }}"
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
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No. Transfer</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Dari Toko</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Ke Toko</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Diminta</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Diterima</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($transfers as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $t->transfer_no }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700">{{ $t->fromStore->name }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700">{{ $t->toStore->name }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="text-xs px-2 py-0.5 rounded-full {{ $t->statusColor() }}">{{ $t->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-xs text-gray-700">{{ $t->items->sum('qty_requested') }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right text-xs {{ $t->isReceived() ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                                    {{ $t->isReceived() ? $t->items->sum('qty_received') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('transfers.show', $t) }}"
                                        class="text-xs text-indigo-600 hover:underline">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400">Tidak ada data transfer</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transfers->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">{{ $transfers->links() }}</div>
            @endif
        </div>

    </div>
@endsection