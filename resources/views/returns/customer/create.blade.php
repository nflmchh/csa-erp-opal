@extends('layouts.app')
@section('title', 'Buat Retur Konsumen')
@section('page-title', 'Buat Retur Konsumen')
@section('breadcrumb', 'Retur / Konsumen / Buat')

@section('content')
<div class="max-w-3xl mx-auto"
    x-data="returnBuilder({{ json_encode($variants) }})">

    <form method="POST" action="{{ route('returns.customer.store') }}" class="space-y-5" @submit="processing = true">
        @csrf

        {{-- Header info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">Informasi Retur</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Toko</label>
                    <p class="text-sm font-medium text-gray-800 border border-gray-200 bg-gray-50 rounded-lg px-3 py-2">
                        {{ $store?->name ?? '—' }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Transaksi Penjualan (opsional)</label>
                    <select name="sale_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Tanpa referensi —</option>
                        @foreach($recentSales as $sale)
                        <option value="{{ $sale->id }}" {{ old('sale_id') == $sale->id ? 'selected' : '' }}>
                            {{ $sale->sale_no }} — {{ $sale->created_at->format('d/m/Y H:i') }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Alasan Retur <span class="text-red-500">*</span></label>
                    <select name="return_reason_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('return_reason_id') border-red-400 @enderror">
                        <option value="">Pilih alasan…</option>
                        @foreach($reasons as $reason)
                        <option value="{{ $reason->id }}" {{ old('return_reason_id') == $reason->id ? 'selected' : '' }}>
                            {{ $reason->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('return_reason_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" maxlength="500"
                        placeholder="Keterangan tambahan…"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        {{-- Item builder --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Item Retur</h2>
                <span class="text-xs text-gray-400" x-text="rows.length + ' item'"></span>
            </div>

            {{-- Search --}}
            <div class="px-5 py-3 border-b border-gray-100 relative">
                <input type="text" x-model="search" @input.debounce.200="doSearch()"
                    @focus="showDrop = true" @blur.window="showDrop = false"
                    placeholder="Cari SKU atau nama produk…"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <div x-show="showDrop && results.length > 0" x-transition
                    class="absolute left-5 right-5 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-20 max-h-52 overflow-y-auto"
                    style="display:none">
                    <template x-for="v in results" :key="v.id">
                        <button type="button" @mousedown.prevent="addRow(v)"
                            class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 text-sm border-b border-gray-50 last:border-0">
                            <span class="font-mono text-xs text-indigo-600" x-text="v.sku"></span>
                            <span class="ml-2 text-gray-700" x-text="v.label"></span>
                            <span class="ml-2 text-gray-400 text-xs" x-text="'Rp ' + v.price.toLocaleString('id-ID')"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Harga Jual</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Kondisi</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(row, idx) in rows" :key="row._key">
                            <tr>
                                <input type="hidden" :name="'items[' + idx + '][variant_id]'" :value="row.id">
                                <input type="hidden" :name="'items[' + idx + '][unit_price]'" :value="row.price">
                                <td class="px-4 py-2 font-mono text-xs text-gray-600" x-text="row.sku"></td>
                                <td class="px-4 py-2 text-xs text-gray-700" x-text="row.label"></td>
                                <td class="px-4 py-2 text-right text-xs text-gray-700"
                                    x-text="'Rp ' + row.price.toLocaleString('id-ID')"></td>
                                <td class="px-4 py-2 text-right">
                                    <input type="number" :name="'items[' + idx + '][qty]'"
                                        x-model.number="row.qty" min="1"
                                        class="w-16 border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <select :name="'items[' + idx + '][condition]'" x-model="row.condition"
                                        class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="good">Baik</option>
                                        <option value="damaged">Rusak</option>
                                    </select>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button" @click="rows.splice(idx, 1)"
                                        class="text-red-400 hover:text-red-600 text-xs">✕</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="rows.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada item — cari produk di atas</td>
                        </tr>
                    </tbody>
                    <tfoot x-show="rows.length > 0" class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-xs text-gray-500 text-right font-semibold">Total Nilai Retur:</td>
                            <td class="px-4 py-2 text-right text-sm font-bold text-gray-800"
                                x-text="'Rp ' + totalValue().toLocaleString('id-ID')"></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('returns.customer.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali</a>
            <button type="submit" :disabled="rows.length === 0 || processing"
                class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">
                <span x-show="!processing">Proses Retur</span>
                <span x-show="processing">Memproses…</span>
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
function returnBuilder(variants) {
    return {
        variants,
        search: '',
        results: [],
        showDrop: false,
        rows: [],
        processing: false,
        _key: 0,

        doSearch() {
            const q = this.search.toLowerCase().trim();
            if (!q) { this.results = []; return; }
            this.results = this.variants.filter(v =>
                v.sku.toLowerCase().includes(q) || v.label.toLowerCase().includes(q)
            ).slice(0, 10);
        },

        addRow(v) {
            const existing = this.rows.find(r => r.id === v.id);
            if (existing) { existing.qty++; }
            else {
                this.rows.push({ _key: this._key++, id: v.id, sku: v.sku, label: v.label, price: v.price, qty: 1, condition: 'good' });
            }
            this.search = '';
            this.results = [];
        },

        totalValue() {
            return this.rows.reduce((s, r) => s + r.price * r.qty, 0);
        },
    };
}
</script>
@endpush
@endsection
