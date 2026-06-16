@extends('layouts.app')
@section('title', 'Detail Penerimaan')
@section('page-title', $inbound->reference_no)
@section('breadcrumb', 'Gudang / Penerimaan / ' . $inbound->reference_no)

@section('content')
<div class="space-y-5">

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">No. Referensi</p>
                <p class="font-mono font-semibold text-indigo-600">{{ $inbound->reference_no }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Gudang</p>
                <p class="font-medium text-gray-700">{{ optional($inbound->warehouse)->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Supplier</p>
                <p class="text-gray-700">{{ $inbound->supplier_name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Status</p>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $inbound->isReceived() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $inbound->isReceived() ? 'Diterima' : 'Draft' }}
                </span>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Dibuat</p>
                <p class="text-gray-700">{{ $inbound->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Diterima</p>
                <p class="text-gray-700">{{ $inbound->received_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Diterima Oleh</p>
                <p class="text-gray-700">{{ $inbound->receiver?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Total Item</p>
                <p class="font-bold text-gray-700">{{ $inbound->totalQty() }} pcs</p>
            </div>
        </div>
        @if($inbound->notes)
        <div class="mt-4 border-t border-gray-100 pt-4">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Catatan</p>
            <p class="text-sm text-gray-600">{{ $inbound->notes }}</p>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Daftar Item ({{ $inbound->items->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Warna / Size</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Harga Modal</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($inbound->items as $item)
                    @php
                        $v = $item->variant;
                        $p = optional($v)->product;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ optional($v)?->sku ?? '—' }}</td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ optional($p)->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ optional($v->color)->name ?? '—' }} / {{ optional($v->size)->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700">{{ $item->qty }}</td>
                        <td class="px-4 py-2 text-right text-xs text-gray-500">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700">Rp {{ number_format($item->qty * $item->unit_cost, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-xs font-semibold text-gray-600 text-right">Total</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-800">{{ $inbound->totalQty() }}</td>
                        <td></td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-800">
                            Rp {{ number_format($inbound->items->sum(fn($i) => $i->qty * $i->unit_cost), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="flex justify-start">
        <a href="{{ route('warehouse.inbound.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali</a>
    </div>
</div>
@endsection
