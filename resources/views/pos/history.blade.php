@extends('layouts.app')
@section('title', 'Riwayat Transaksi')
@section('page-title', 'Riwayat Transaksi POS')
@section('breadcrumb', 'POS / Riwayat')

@section('content')
<div class="space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        @if($stores->count() > 0)
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
            <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg self-end">Filter</button>
        <a href="{{ route('pos.history') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end">Reset</a>

        @php
            $totalAmount = $sales->sum('total_amount');
            $totalItems  = $sales->sum(fn($s) => $s->items->sum('qty'));
        @endphp
        <div class="ml-auto self-end text-sm text-gray-600">
            <span class="font-semibold text-gray-900">{{ $sales->total() }}</span> transaksi ·
            Total: <span class="font-semibold text-indigo-700">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">No. Transaksi</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Toko</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Kasir</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Pembayaran</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Item</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Total</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $sale->sale_no }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">{{ $sale->store->name }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">{{ $sale->creator?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">{{ $sale->paymentMethod?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-xs text-gray-700">{{ $sale->items->sum('qty') }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-800">
                            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                            @if($sale->discount_amount > 0)
                            <span class="block text-xs font-normal text-red-500">Diskon: Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('pos.receipt', $sale) }}" target="_blank"
                                class="text-xs text-indigo-600 hover:underline">Struk</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">Tidak ada transaksi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $sales->links() }}</div>
        @endif
    </div>

</div>
@endsection
