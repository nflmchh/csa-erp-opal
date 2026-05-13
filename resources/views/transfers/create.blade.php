@extends('layouts.app')
@section('title', 'Buat Transfer Antar Toko')
@section('page-title', 'Buat Transfer Antar Toko')
@section('breadcrumb', 'Transfer / Buat')

@section('content')
<div class="max-w-4xl mx-auto" x-data="transferBuilder(@json($variants))">

    <form method="POST" action="{{ route('transfers.store') }}" class="space-y-5">
        @csrf

        {{-- Header info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">Informasi Transfer</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @php
                    $primaryStore = auth()->user()->primaryStore();
                @endphp
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Toko Asal <span class="text-red-500">*</span></label>
                    @if($primaryStore)
                        <div class="relative">
                            <select disabled
                                class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-500 cursor-not-allowed appearance-none">
                                <option selected>{{ $primaryStore->name }}</option>
                            </select>
                            <input type="hidden" name="from_store_id" value="{{ $primaryStore->id }}">
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/></svg>
                            </div>
                        </div>
                    @else
                        <select name="from_store_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— Pilih toko asal —</option>
                            @foreach($stores as $s)
                            <option value="{{ $s->id }}" {{ old('from_store_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Toko Tujuan <span class="text-red-500">*</span></label>
                    <select name="to_store_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Pilih toko tujuan —</option>
                        @foreach($stores as $s)
                        @if($primaryStore && $s->id == $primaryStore->id) @continue @endif
                        <option value="{{ $s->id }}" {{ old('to_store_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                <textarea name="notes" rows="2" placeholder="Opsional…"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Item Transfer</h2>
                <button type="button" @click="addRow()"
                    class="text-xs text-indigo-600 font-medium hover:text-indigo-800">+ Tambah Baris</button>
            </div>

            {{-- Variant search --}}
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 relative" @click.outside="showDrop = false">
                <input type="text" x-model="search" 
                    @input.debounce.300ms="doSearch()"
                    @focus="if(search === '') doSearch(true); else showDrop = true"
                    @keydown.enter.prevent="scanProduct()"
                    placeholder="Cari SKU atau nama produk untuk ditambahkan…"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <div x-show="loading" class="absolute right-8 top-5">
                    <svg class="animate-spin h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </div>
                <div x-show="showDrop" class="mt-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg bg-white shadow text-sm absolute w-[calc(100%-2.5rem)] z-50" style="display:none">
                    <template x-for="v in results" :key="v.id">
                        <div @click="selectVariant(v)"
                            class="px-3 py-2 hover:bg-indigo-50 cursor-pointer border-b border-gray-100 last:border-0 flex justify-between items-center">
                            <div class="flex flex-col">
                                <div class="flex items-center">
                                    <span class="font-mono text-xs text-indigo-600 font-bold" x-text="v.sku"></span>
                                    <span class="text-gray-600 ml-2 text-xs" x-text="v.label"></span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md" 
                                    :class="v.stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-500'">
                                    Stok: <span x-text="v.stock"></span>
                                </span>
                            </div>
                        </div>
                    </template>
                    <div x-show="results.length === 0 && !loading" class="px-3 py-3 text-gray-400 text-xs">Tidak ada hasil</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Qty Diminta</th>
                            <th class="px-4 py-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(row, idx) in rows" :key="idx">
                            <tr>
                                <td class="px-4 py-2">
                                    <input type="hidden" :name="`items[${idx}][variant_id]`" :value="row.variant_id">
                                    <span class="font-mono text-xs text-gray-700" x-text="row.sku || '—'"></span>
                                </td>
                                <td class="px-4 py-2 text-xs text-gray-600" x-text="row.label || '—'"></td>
                                <td class="px-4 py-2 text-right">
                                    <input type="number" :name="`items[${idx}][qty_requested]`"
                                        x-model.number="row.qty" min="1"
                                        class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button" @click="removeRow(idx)"
                                        class="text-red-400 hover:text-red-600 text-xs">✕</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="rows.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400 text-xs">Belum ada item. Cari produk di atas atau klik "+ Tambah Baris".</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('transfers.index') }}" class="text-sm text-gray-600 hover:underline">← Batal</a>
            <button type="submit" :disabled="rows.length === 0"
                class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">
                Buat Permintaan Transfer
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
function transferBuilder(variants) {
    return {
        rows: [],
        search: '',
        results: [],
        showDrop: false,
        loading: false,

        async doSearch(isInit = false) {
            if (!isInit && this.search.length > 0 && this.search.length < 2) {
                this.results = [];
                return;
            }

            this.loading = true;
            try {
                const storeId = document.querySelector('[name=from_store_id]')?.value || '';
                const response = await fetch(`/api/v1/variants/search?q=${encodeURIComponent(this.search)}&store_id=${storeId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const data = await response.json();
                    const used = this.rows.map(r => r.variant_id);
                    this.results = data.filter(v => !used.includes(v.id));
                }
            } catch (e) {
                console.error("Gagal mengambil data", e);
            } finally {
                this.loading = false;
                this.showDrop = true;
            }
        },

        async scanProduct() {
            const q = this.search.trim();
            if (!q) return;

            this.loading = true;
            try {
                const storeId = document.querySelector('[name=from_store_id]')?.value || '';
                const response = await fetch(`/api/v1/variants/search?q=${encodeURIComponent(q)}&exact=1&store_id=${storeId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const data = await response.json();
                    const used = this.rows.map(r => r.variant_id);
                    const filtered = data.filter(v => !used.includes(v.id));
                    
                    if (filtered.length > 0) {
                        this.selectVariant(filtered[0]);
                    } else {
                        this.results = [];
                        this.showDrop = true;
                    }
                }
            } catch (e) {
                console.error("Gagal scan produk", e);
            } finally {
                this.loading = false;
            }
        },

        selectVariant(v) {
            this.rows.push({ variant_id: v.id, sku: v.sku, label: v.label, qty: 1 });
            this.search = '';
            this.showDrop = false;
        },

        addRow() {
            // Focus on search input since we use global search
            const el = document.querySelector('input[x-model="search"]');
            if (el) el.focus();
        },

        removeRow(idx) {
            this.rows.splice(idx, 1);
        },
    };
}
</script>
@endpush
