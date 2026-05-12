@extends('layouts.app')
@section('title', 'Opname ' . $opname->opname_no)
@section('page-title', 'Stock Opname — ' . $opname->opname_no)
@section('breadcrumb', 'Opname / ' . $opname->opname_no)

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        @php
            $statusColors = ['draft'=>'bg-gray-100 text-gray-600','submitted'=>'bg-blue-100 text-blue-700','approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700'];
            $statusLabels = ['draft'=>'Draft','submitted'=>'Disubmit','approved'=>'Disetujui','rejected'=>'Ditolak'];
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400">No. Opname</p>
                <p class="font-mono font-semibold text-indigo-600">{{ $opname->opname_no }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Lokasi</p>
                <p class="font-medium text-gray-700">
                    {{ $opname->location_type === 'warehouse' ? 'Gudang' : 'Toko' }}: {{ $opname->location()?->name ?? 'ID ' . $opname->location_id }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Status</p>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColors[$opname->status] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $statusLabels[$opname->status] ?? $opname->status }}
                </span>
            </div>
            <div>
                <p class="text-xs text-gray-400">Dibuat oleh</p>
                <p class="text-sm text-gray-700">{{ $opname->creator?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Tanggal</p>
                <p class="text-sm text-gray-700">{{ $opname->created_at->format('d/m/Y H:i') }}</p>
            </div>
            @if($opname->notes)
            <div class="col-span-2">
                <p class="text-xs text-gray-400">Catatan</p>
                <p class="text-sm text-gray-700">{{ $opname->notes }}</p>
            </div>
            @endif
            @if($opname->status === 'rejected' && $opname->rejection_reason)
            <div class="col-span-2">
                <p class="text-xs text-gray-400">Alasan Penolakan</p>
                <p class="text-sm text-red-600">{{ $opname->rejection_reason }}</p>
            </div>
            @endif
        </div>

        @if($opname->submitted_at || $opname->approved_at || $opname->rejected_at)
        <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-x-6 gap-y-2 text-xs text-gray-500">
            @if($opname->submitted_at)
            <span>→ Disubmit oleh <strong>{{ $opname->submitter?->name }}</strong> · {{ $opname->submitted_at->format('d/m/Y H:i') }}</span>
            @endif
            @if($opname->approved_at)
            <span class="text-green-600">✓ Disetujui oleh <strong>{{ $opname->approver?->name }}</strong> · {{ $opname->approved_at->format('d/m/Y H:i') }}</span>
            @endif
            @if($opname->rejected_at)
            <span class="text-red-500">✕ Ditolak oleh <strong>{{ $opname->rejecter?->name }}</strong> · {{ $opname->rejected_at->format('d/m/Y H:i') }}</span>
            @endif
        </div>
        @endif
    </div>

    {{-- SUBMIT form — enter actual counts --}}
    @if($opname->isDraft())
        @can('submit stock opname')
        <form method="POST" action="{{ route('opname.submit', $opname) }}" class="space-y-4">
            @csrf
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-700">Masukkan Hitungan Aktual</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Isi kolom "Qty Aktual" sesuai hasil hitungan fisik. Selisih akan dihitung otomatis.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Sistem</th>
                                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Aktual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($opname->items as $i => $item)
                            @php $v = $item->variant; @endphp
                            <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                            <tr>
                                <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ $v->sku }}</td>
                                <td class="px-4 py-2 text-xs text-gray-700">{{ $v->product->name }} · {{ $v->color->name }} / {{ $v->size->name }}</td>
                                <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700">{{ $item->qty_system }}</td>
                                <td class="px-4 py-2 text-right">
                                    <input type="number" name="items[{{ $i }}][qty_actual]"
                                        value="{{ old('items.' . $i . '.qty_actual', $item->qty_actual ?? '') }}"
                                        min="0" required
                                        class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex items-center justify-between">
                {{-- Kumpulan tombol sebelah kiri --}}
                <div class="flex items-center gap-6">
                    <a href="{{ route('opname.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali</a>
                    
                    @can('delete stock opname')
                    <button type="submit" form="delete-opname-form" onclick="return confirm('Apakah Anda yakin ingin menghapus draft Stock Opname ini secara permanen?')" 
                        class="text-sm text-red-600 font-semibold hover:text-red-800 transition-colors">
                        🗑️ Hapus Draft
                    </button>
                    @endcan
                </div>

                {{-- Tombol submit sebelah kanan --}}
                <button type="submit" onclick="return confirm('Submit opname {{ $opname->opname_no }} untuk persetujuan?')"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
                    Submit untuk Persetujuan
                </button>
            </div>
        </form>

        {{-- Form tersembunyi untuk proses Delete --}}
        @can('delete stock opname')
        <form id="delete-opname-form" method="POST" action="{{ route('opname.destroy', $opname) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
        @endcan
        @endcan
    @endif

    {{-- APPROVE / REJECT — submitted opname --}}
    @if($opname->isSubmitted())
        @can('approve stock opname')
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">Tindakan Persetujuan</h2>
            <p class="text-xs text-gray-500">Menyetujui opname ini akan mengaplikasikan seluruh selisih stok ke sistem.</p>
            <form method="POST" action="{{ route('opname.approve', $opname) }}">
                @csrf
                <input type="hidden" name="action" value="approve">
                <button type="submit" onclick="return confirm('Setujui dan terapkan penyesuaian stok?')"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2 rounded-lg text-sm">
                    Setujui & Terapkan Stok
                </button>
            </form>
            <div x-data="{ open: false }">
                <button type="button" @click="open = !open" class="text-sm text-red-500 hover:underline">Tolak opname ini…</button>
                <div x-show="open" x-transition class="mt-3" style="display:none">
                    <form method="POST" action="{{ route('opname.approve', $opname) }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="action" value="reject">
                        <textarea name="rejection_reason" rows="2" required placeholder="Alasan penolakan…"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                        <button type="submit" onclick="return confirm('Tolak opname ini?')"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2 rounded-lg text-sm">
                            Konfirmasi Penolakan
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endcan
    @endif

    {{-- Items table (submitted/approved/rejected view-only) --}}
    @if(!$opname->isDraft())
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
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Sistem</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Aktual</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Selisih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($opname->items as $item)
                    @php $v = $item->variant; $diff = $item->qty_difference; @endphp
                    <tr class="{{ $diff !== null && $diff != 0 ? 'bg-yellow-50' : '' }}">
                        <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ $v->sku }}</td>
                        <td class="px-4 py-2 text-xs text-gray-700">{{ $v->product->name }} · {{ $v->color->name }} / {{ $v->size->name }}</td>
                        <td class="px-4 py-2 text-right text-xs text-gray-700">{{ $item->qty_system }}</td>
                        <td class="px-4 py-2 text-right text-xs text-gray-700">{{ $item->qty_actual ?? '—' }}</td>
                        <td class="px-4 py-2 text-right text-xs font-semibold
                            {{ $diff === null ? 'text-gray-400' : ($diff > 0 ? 'text-green-600' : ($diff < 0 ? 'text-red-600' : 'text-gray-500')) }}">
                            @if($diff !== null)
                                {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <a href="{{ route('opname.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali</a>
    @endif

</div>
@endsection
