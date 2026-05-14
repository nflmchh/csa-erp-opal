@extends('layouts.app')
@section('title', 'Penerimaan Barang Baru')
@section('page-title', 'Penerimaan Barang')
@section('breadcrumb', 'Gudang / Penerimaan / Baru')

@section('content')
<div class="max-w-4xl mx-auto" x-data="inboundForm()">

    <form method="POST" action="{{ route('warehouse.inbound.store') }}" class="space-y-5"
        @submit.prevent="submitForm($el)">
        @csrf

        {{-- Header info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Informasi Penerimaan</h2>
                <span class="text-xs font-mono text-indigo-600 bg-indigo-50 px-2 py-1 rounded">{{ $refNo }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gudang Tujuan <span class="text-red-500">*</span></label>
                    @if(auth()->user()->hasRole('admin gudang') && $warehouses->count() === 1)
                        <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                            <span class="text-sm font-semibold text-gray-700">{{ $warehouses->first()->name }}</span>
                            <input type="hidden" name="warehouse_id" value="{{ $warehouses->first()->id }}">
                        </div>
                    @else
                        <select name="warehouse_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('warehouse_id') border-red-500 @enderror">
                            <option value="">-- Pilih Gudang --</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (old('warehouse_id') ?? ($warehouses->count() === 1 ? $warehouses->first()->id : '')) == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                            @endforeach
                        </select>
                    @endif
                    @error('warehouse_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier</label>
                    <input type="text" name="supplier_name" value="{{ old('supplier_name') }}"
                        placeholder="Nama supplier (opsional)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" placeholder="Catatan penerimaan (opsional)…"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Item Barang</h2>
                <button type="button" @click="addRow()"
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Tambah baris</button>
            </div>

            <div class="grid grid-cols-12 gap-2 text-xs font-semibold text-gray-500 uppercase px-1">
                <div class="col-span-5">Produk / SKU</div>
                <div class="col-span-3">Qty</div>
                <!-- <div class="col-span-3">Harga Modal</div> -->
                <div class="col-span-1"></div>
            </div>

            <div class="space-y-3">
                <template x-for="(row, idx) in rows" :key="idx">
                    <div>
                        <div class="grid grid-cols-12 gap-2 items-start">

                            {{-- Autocomplete search --}}
                            <div class="col-span-5 relative">
                                <input type="hidden" :name="`items[${idx}][sku]`" :value="row.sku">

                                <div class="relative">
                                    <input type="text"
                                        x-model="row.query"
                                        @input.debounce.350ms="search(idx)"
                                        @focus="if(row.query === '' && !row.sku) { search(idx, true) }"
                                        @keydown.enter.prevent="scanSku(idx)"
                                        @keydown.escape="row.results = []"
                                        @blur.delay.200ms="row.results = []"
                                        :placeholder="row.sku ? '' : 'Cari nama / SKU…'"
                                        autocomplete="off"
                                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-7"
                                        :class="row.sku ? 'border-green-400 bg-green-50 font-mono text-xs' : 'border-gray-300'">

                                    <button x-show="row.sku" type="button" @click="clearRow(idx)"
                                        class="absolute right-2 top-2.5 text-gray-400 hover:text-red-500 leading-none text-base"
                                        title="Ganti produk">✕</button>

                                    <div x-show="row.searching"
                                        class="absolute right-2 top-2.5 text-gray-400 text-xs">…</div>
                                </div>

                                {{-- Dropdown results --}}
                                <div x-show="row.results.length > 0"
                                    class="absolute z-50 left-0 right-0 top-full mt-0.5 bg-white border border-gray-200 rounded-lg shadow-xl max-h-64 overflow-y-auto">
                                    <template x-for="v in row.results" :key="v.sku">
                                        <button type="button" @mousedown.prevent="selectVariant(idx, v)"
                                            class="w-full text-left px-3 py-2 hover:bg-indigo-50 border-b border-gray-100 last:border-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono text-xs text-indigo-600 shrink-0" x-text="v.sku"></span>
                                                <span class="text-xs text-gray-600 truncate" x-text="v.label"></span>
                                                <span x-show="v.brand" class="text-xs text-gray-400 ml-auto shrink-0" x-text="v.brand"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>

                                {{-- No results hint --}}
                                <div x-show="row.noResults"
                                    class="absolute z-50 left-0 right-0 top-full mt-0.5 bg-white border border-gray-200 rounded-lg shadow px-3 py-2 text-xs text-gray-400">
                                    Produk tidak ditemukan
                                </div>
                            </div>

                            {{-- Qty --}}
                            <div class="col-span-3">
                                <input type="number" :name="`items[${idx}][qty]`" x-model="row.qty"
                                    min="1" required placeholder="0"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>

                            <!-- {{-- Unit cost --}}
                            <div class="col-span-3">
                                <div class="relative">
                                    <span class="absolute left-2.5 top-2.5 text-xs text-gray-400">Rp</span>
                                    <input type="number" :name="`items[${idx}][unit_cost]`" x-model="row.cost"
                                        min="0" step="500" placeholder="0"
                                        class="w-full border border-gray-300 rounded-lg pl-8 pr-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div> -->

                            {{-- Remove --}}
                            <div class="col-span-1 text-center pt-1.5">
                                <button type="button" @click="removeRow(idx)"
                                    class="text-red-400 hover:text-red-600 font-bold text-xl leading-none"
                                    :disabled="rows.length === 1">×</button>
                            </div>
                        </div>

                        {{-- Selected product confirmation --}}
                        <div x-show="row.sku" class="mt-1 pl-1">
                            <p class="text-xs text-green-600">
                                <span class="font-mono" x-text="row.sku"></span>
                                <span class="ml-1 text-gray-500" x-text="'— ' + row.label"></span>
                            </p>
                        </div>

                        {{-- Validation error --}}
                        <div x-show="row.error" class="mt-1 pl-1">
                            <p class="text-xs text-red-500" x-text="row.error"></p>
                        </div>
                    </div>
                </template>
            </div>

            @if($errors->has('items'))
            <p class="text-red-500 text-xs">{{ $errors->first('items') }}</p>
            @endif
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('warehouse.inbound.index') }}" class="text-sm text-gray-600 hover:underline">← Batal</a>
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">
                Simpan & Terima Barang
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
function inboundForm() {
    return {
        rows: [{ sku: '', label: '', query: '', qty: 1, cost: 0, searching: false, results: [], noResults: false, error: '' }],

        newRow() {
            return { sku: '', label: '', query: '', qty: 1, cost: 0, searching: false, results: [], noResults: false, error: '' };
        },

        addRow() {
            this.rows.push(this.newRow());
        },

        removeRow(idx) {
            if (this.rows.length > 1) this.rows.splice(idx, 1);
        },

        async search(idx, isInit = false) {
            const row = this.rows[idx];
            const q   = row.query.trim();
            row.noResults = false;
            row.results   = [];

            if (!isInit && q.length > 0 && q.length < 2) return;

            row.searching = true;
            try {
                const res  = await fetch(`/api/v1/variants/search?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                row.results   = data;
                row.noResults = data.length === 0;
            } catch (e) {
                // silently ignore network errors
            } finally {
                row.searching = false;
            }
        },

        async scanSku(idx) {
            const row = this.rows[idx];
            const q = row.query.trim();
            if (!q) return;

            row.searching = true;
            try {
                const res = await fetch(`/api/v1/variants/search?q=${encodeURIComponent(q)}&exact=1`);
                const data = await res.json();
                if (data.length > 0) {
                    this.selectVariant(idx, data[0]);
                } else {
                    row.noResults = true;
                    row.results = [];
                }
            } catch (e) {
                // silently ignore network errors
            } finally {
                row.searching = false;
            }
        },

        selectVariant(idx, v) {
            const row    = this.rows[idx];
            row.sku      = v.sku;
            row.label    = v.label;
            row.query    = v.sku;
            row.results  = [];
            row.noResults = false;
            row.error    = '';
        },

        clearRow(idx) {
            const row    = this.rows[idx];
            row.sku      = '';
            row.label    = '';
            row.query    = '';
            row.results  = [];
            row.noResults = false;
            row.error    = '';
        },

        submitForm(formEl) {
            let valid = true;
            this.rows.forEach((row, idx) => {
                if (!row.sku) {
                    row.error = 'Pilih produk dari daftar pencarian.';
                    valid = false;
                } else {
                    row.error = '';
                }
            });
            if (valid) formEl.submit();
        },
    };
}
</script>
@endpush
@endsection
