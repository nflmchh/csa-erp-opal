@extends('layouts.app')
@section('title', 'Retur ' . $return->return_no)
@section('page-title', 'Retur Toko — ' . $return->return_no)
@section('breadcrumb', 'Retur / Toko / ' . $return->return_no)

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400">No. Retur</p>
                <p class="font-mono font-semibold text-indigo-600">{{ $return->return_no }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Dari Toko</p>
                <p class="font-medium text-gray-700">{{ $return->store->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Ke Gudang</p>
                <p class="font-medium text-gray-700">{{ $return->warehouse->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Status</p>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $return->statusColor() }}">{{ $return->statusLabel() }}</span>
            </div>
            <div>
                <p class="text-xs text-gray-400">Alasan</p>
                <p class="text-sm text-gray-700">{{ $return->reason?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Dibuat oleh</p>
                <p class="text-sm text-gray-700">{{ $return->creator?->name }}</p>
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

        @if($return->received_at || $return->inspected_at)
        <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-x-6 gap-y-2 text-xs text-gray-500">
            @if($return->received_at)
            <span>✓ Diterima oleh <strong>{{ $return->receiver?->name }}</strong> · {{ $return->received_at->format('d/m/Y H:i') }}</span>
            @endif
            @if($return->inspected_at)
            <span class="text-green-600">✓ Diinspeksi oleh <strong>{{ $return->inspector?->name }}</strong> · {{ $return->inspected_at->format('d/m/Y H:i') }}</span>
            @endif
        </div>
        @endif
    </div>

    {{-- RECEIVE action --}}
    @if($return->isPending())
        @can('receive store return')
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Konfirmasi Penerimaan</h2>
            <p class="text-xs text-gray-500 mb-4">Tandai bahwa gudang telah menerima barang retur dari toko.</p>
            <form method="POST" action="{{ route('returns.store.receive', $return) }}">
                @csrf
                <button type="submit" onclick="return confirm('Konfirmasi penerimaan retur {{ $return->return_no }}?')"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg text-sm">
                    Konfirmasi Diterima
                </button>
            </form>
        </div>
        @endcan
    @endif

    {{-- INSPECT form --}}
    @if($return->isReceived())
        @can('inspect return')
        <form method="POST" action="{{ route('returns.store.inspect', $return) }}" class="space-y-4">
            @csrf
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-700">Inspeksi Kondisi Barang</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Masukkan jumlah baik dan rusak. Barang baik akan ditambahkan ke stok gudang.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Diretur</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Baik</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Rusak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($return->items as $i => $item)
                            @php $v = $item->variant; @endphp
                            <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                            <tr>
                                <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ $v->sku }}</td>
                                <td class="px-4 py-2 text-xs text-gray-700">{{ $v->product->name }} · {{ $v->color->name }} / {{ $v->size->name }}</td>
                                <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700">{{ $item->qty_returned }}</td>
                                <td class="px-4 py-2 text-right">
                                    <input type="number" name="items[{{ $i }}][qty_good]"
                                        value="{{ $item->qty_returned }}" min="0" max="{{ $item->qty_returned }}"
                                        class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <input type="number" name="items[{{ $i }}][qty_damaged]"
                                        value="0" min="0" max="{{ $item->qty_returned }}"
                                        class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-3 border-t border-gray-100">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Catatan Inspeksi</label>
                    <textarea name="inspection_notes" rows="2" maxlength="500"
                        placeholder="Catatan hasil inspeksi…"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <a href="{{ route('returns.store.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali</a>
                <button type="submit" onclick="return confirm('Selesaikan inspeksi {{ $return->return_no }}?')"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">
                    Selesaikan Inspeksi
                </button>
            </div>
        </form>
        @endcan
    @endif

    {{-- View-only items --}}
    @if($return->isInspected())
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Hasil Inspeksi</h2>
            @if($return->inspection_notes)
            <p class="text-xs text-gray-500 mt-1">{{ $return->inspection_notes }}</p>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Diretur</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Baik</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Rusak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($return->items as $item)
                    @php $v = $item->variant; @endphp
                    <tr>
                        <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ $v->sku }}</td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ $v->product->name }} · {{ $v->color->name }} / {{ $v->size->name }}</td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700">{{ $item->qty_returned }}</td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-green-700">{{ $item->qty_good }}</td>
                        <td class="px-4 py-2 text-right text-xs font-semibold {{ $item->qty_damaged > 0 ? 'text-red-600' : 'text-gray-400' }}">
                            {{ $item->qty_damaged ?: '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <a href="{{ route('returns.store.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali</a>
    @endif

    {{-- Pending view-only items --}}
    @if($return->isPending() && !auth()->user()->can('receive store return'))
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
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Retur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($return->items as $item)
                    @php $v = $item->variant; @endphp
                    <tr>
                        <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ $v->sku }}</td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ $v->product->name }} · {{ $v->color->name }} / {{ $v->size->name }}</td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700">{{ $item->qty_returned }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <a href="{{ route('returns.store.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali</a>
    @endif

</div>
@endsection
