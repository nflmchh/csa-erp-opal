@extends('layouts.app')
@section('title', 'Retur ' . $return->return_no)
@section('page-title', 'Retur Konsumen — ' . $return->return_no)
@section('breadcrumb', 'Retur / Konsumen / ' . $return->return_no)

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400">No. Retur</p>
                <p class="font-mono font-semibold text-indigo-600">{{ $return->return_no }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Toko</p>
                <p class="font-medium text-gray-700">{{ $return?->store?->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Status</p>
                @if($return->status === 'processed')
                <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Diproses</span>
                @else
                <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700">Menunggu</span>
                @endif
            </div>
            <div>
                <p class="text-xs text-gray-400">Alasan</p>
                <p class="text-sm text-gray-700">{{ $return->reason?->name ?? '—' }}</p>
            </div>
            @if($return->sale)
            <div>
                <p class="text-xs text-gray-400">Referensi Penjualan</p>
                <p class="font-mono text-xs text-gray-700">{{ $return->sale->sale_no }}</p>
            </div>
            @endif
            <div>
                <p class="text-xs text-gray-400">Diproses oleh</p>
                <p class="text-sm text-gray-700">{{ $return->processor?->name ?? $return->creator?->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Tanggal</p>
                <p class="text-sm text-gray-700">{{ $return->created_at->format('d/m/Y H:i') }}</p>
            </div>
            @if($return->notes)
            <div class="col-span-2">
                <p class="text-xs text-gray-400">Catatan</p>
                <p class="text-sm text-gray-700">{{ $return->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Detail Item</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Harga</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Kondisi</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($return->items as $item)
                    @php $v = $item->variant; @endphp
                    <tr>
                        <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ $v?->sku }}</td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ $v?->product?->name }} · {{ $v?->color?->name }} / {{ $v?->size?->name }}</td>
                        <td class="px-4 py-2 text-right text-xs text-gray-700">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700">{{ $item->qty }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($item->condition === 'good')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Baik</span>
                            @else
                            <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">Rusak</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="5" class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Total Nilai Retur:</td>
                        <td class="px-4 py-2 text-right font-bold text-gray-900">Rp {{ number_format($return->totalValue(), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <a href="{{ route('returns.customer.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali</a>

</div>
@endsection
