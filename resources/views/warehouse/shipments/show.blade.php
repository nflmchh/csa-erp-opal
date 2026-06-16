@extends('layouts.app')
@section('title', $shipment->shipment_no)
@section('page-title', 'Pengiriman ' . $shipment->shipment_no)
@section('breadcrumb', 'Gudang / Pengiriman / ' . $shipment->shipment_no)

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <h2 class="font-mono text-lg font-bold text-indigo-600">{{ $shipment->shipment_no }}</h2>
                    <span class="text-sm px-3 py-1 rounded-full font-medium {{ $shipment->statusColor() }}">
                        {{ $shipment->statusLabel() }}
                    </span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400">Dari Gudang</p>
                        <p class="font-medium text-gray-700">{{ optional($shipment->warehouse)->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Ke Toko</p>
                        <p class="font-medium text-gray-700">{{ optional($shipment->store)->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Total Item</p>
                        <p class="font-medium text-gray-700">{{ $shipment->totalQtySent() }} pcs</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Dibuat</p>
                        <p class="font-medium text-gray-700">{{ $shipment->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($shipment->shipped_at)
                    <div>
                        <p class="text-xs text-gray-400">Dikirim</p>
                        <p class="font-medium text-gray-700">{{ $shipment->shipped_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                    @if($shipment->received_at)
                    <div>
                        <p class="text-xs text-gray-400">Diterima</p>
                        <p class="font-medium text-gray-700">{{ $shipment->received_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="flex flex-wrap gap-2">
                @can('print shipment')
                <a href="{{ route('warehouse.shipments.print', $shipment) }}" target="_blank"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-3 py-1.5 rounded-lg flex items-center gap-1">
                    🖨️ Print
                </a>
                @endcan

                @can('update shipment')
                @php $nextStatus = null;
                $statuses = \App\Models\Shipment::STATUSES;
                $idx = array_search($shipment->status, $statuses);
                if ($idx !== false && $idx < count($statuses) - 1) $nextStatus = $statuses[$idx + 1];
                @endphp
                @if($nextStatus && $nextStatus !== 'received')
                <form method="POST" action="{{ route('warehouse.shipments.status', $shipment) }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $nextStatus }}">
                    <button type="submit"
                        onclick="return confirm('Ubah status ke {{ \App\Models\Shipment::STATUS_LABELS[$nextStatus] }}?')"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded-lg">
                        → {{ \App\Models\Shipment::STATUS_LABELS[$nextStatus] }}
                    </button>
                </form>
                @endif
                @endcan
            </div>
        </div>

        {{-- Status timeline --}}
        <div class="mt-6 flex items-center gap-0 overflow-x-auto">
            @foreach(\App\Models\Shipment::STATUSES as $s)
            @php
                $statuses = \App\Models\Shipment::STATUSES;
                $curIdx  = array_search($shipment->status, $statuses);
                $thisIdx = array_search($s, $statuses);
                $done    = $thisIdx <= $curIdx;
            @endphp
            <div class="flex items-center">
                <div class="flex flex-col items-center">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                        {{ $done ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-400' }}">
                        {{ $done ? '✓' : ($thisIdx + 1) }}
                    </div>
                    <span class="text-xs mt-1 whitespace-nowrap {{ $done ? 'text-indigo-600 font-medium' : 'text-gray-400' }}">
                        {{ \App\Models\Shipment::STATUS_LABELS[$s] }}
                    </span>
                </div>
                @if(!$loop->last)
                <div class="w-12 sm:w-20 h-0.5 {{ $done && $thisIdx < $curIdx ? 'bg-indigo-400' : 'bg-gray-200' }} mx-1 mb-4"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Items table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Item Pengiriman ({{ $shipment->items->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Warna / Size</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Kirim</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Terima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($shipment->items as $item)
                    @php $v = $item->variant; @endphp
                    <tr class="hover:bg-gray-50">
                       <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ optional($v)?->sku ?? '—' }}</td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ optional($v->product)->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ optional($v->color)->name ?? '—' }} / {{ optional($v->size)->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700">{{ $item->qty_sent }}</td>
                        <td class="px-4 py-2 text-right text-xs font-semibold
                            {{ $item->qty_received == 0 ? 'text-gray-400' : ($item->qty_received < $item->qty_sent ? 'text-yellow-600' : 'text-green-600') }}">
                            {{ $item->qty_received ?: '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
