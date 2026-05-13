@extends('layouts.app')
@section('title', 'Buat Retur Konsumen')
@section('page-title', 'Buat Retur Konsumen')
@section('breadcrumb', 'Retur / Konsumen / Buat')

@section('content')
<div class="max-w-3xl mx-auto"
    x-data="returnBuilder()">

    <form method="POST" action="{{ route('returns.customer.store') }}" class="space-y-5" @submit="processing = true">
        @csrf

        {{-- Scan Barcode Struk --}}
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-indigo-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Scan Barcode Struk
                </h2>
                <p class="text-xs text-indigo-700 mt-1">Scan barcode struk atau ketik nomor struk di kolom kanan, tekan Enter untuk muat otomatis.</p>
            </div>
            <div class="w-full sm:w-64 relative">
                <input type="text" x-model="scannedBarcode" @keydown.enter.prevent="fetchSaleByBarcode()" placeholder="Contoh: SAL-202605-0027"
                    class="w-full border border-indigo-300 rounded-lg pl-10 pr-3 py-2.5 text-sm font-mono shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-indigo-300">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
                </div>
            </div>
        </div>

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

                {{-- DROPDOWN STRUK: Searchable AJAX --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Transaksi Penjualan (opsional)</label>
                    <input type="hidden" name="sale_id" :value="selectedSaleId">

                    <div class="relative" @click.outside="showSaleDrop = false">
                        <input type="text"
                            x-model="saleSearch"
                            @input.debounce.300ms="doSaleSearch()"
                            @focus="doSaleSearch()"
                            placeholder="Ketik nomor struk untuk mencari…"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div x-show="selectedSaleLabel" class="text-xs text-green-600 mt-1 px-1 font-medium" x-text="'✓ Terpilih: ' + selectedSaleLabel"></div>

                        <div x-show="showSaleDrop && saleResults.length > 0" x-transition
                            class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-30 max-h-52 overflow-y-auto"
                            style="display:none">
                            <template x-for="s in saleResults" :key="s.id">
                                <button type="button" @mousedown.prevent="pickSale(s)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 border-b border-gray-50 last:border-0">
                                    <span class="font-mono text-xs text-indigo-700 font-semibold" x-text="s.sale_no"></span>
                                    <span class="text-xs text-gray-400 ml-2" x-text="s.date"></span>
                                    <span class="text-xs text-gray-500 ml-2" x-text="'Rp ' + Number(s.total).toLocaleString('id-ID')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
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
            <div class="px-5 py-3 border-b border-gray-100 relative" @click.outside="showDrop = false">
                <input type="text" x-model="search" @input.debounce.300ms="doSearch()"
                    @focus="if(search === '') doSearch(true); else showDrop = true"
                    @keydown.enter.prevent="scanProduct()"
                    placeholder="Cari SKU atau nama produk…"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <div x-show="loading" class="absolute right-8 top-5">
                    <svg class="animate-spin h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </div>
                <div x-show="showDrop" x-transition
                    class="absolute left-5 right-5 w-[calc(100%-2.5rem)] top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-20 max-h-52 overflow-y-auto"
                    style="display:none">
                    <template x-for="v in results" :key="v.id">
                        <button type="button" @mousedown.prevent="addRow(v)"
                            class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 text-sm border-b border-gray-50 last:border-0">
                            <span class="font-mono text-xs text-indigo-600" x-text="v.sku"></span>
                            <span class="ml-2 text-gray-700" x-text="v.label"></span>
                            <span class="ml-2 text-gray-400 text-xs" x-text="'Rp ' + v.price.toLocaleString('id-ID')"></span>
                        </button>
                    </template>
                    <div x-show="results.length === 0 && !loading" class="px-4 py-3 text-gray-400 text-xs">Tidak ada hasil</div>
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
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada item — scan struk atau cari produk di atas</td>
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
function returnBuilder() {
    return {
        search: '',
        results: [],
        showDrop: false,
        loading: false,
        rows: [],
        processing: false,
        _key: 0,

        scannedBarcode: '',
        selectedSaleId: '',
        selectedSaleLabel: '',

        // Sale dropdown (searchable)
        saleSearch: '',
        saleResults: [],
        showSaleDrop: false,

        async doSaleSearch() {
            try {
                const res = await fetch(`/returns/customer/search-sales?q=${encodeURIComponent(this.saleSearch)}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    this.saleResults = await res.json();
                    this.showSaleDrop = this.saleResults.length > 0;
                }
            } catch (e) {
                console.error('Gagal cari struk', e);
            }
        },

        async pickSale(s) {
            this.selectedSaleId    = s.id;
            this.selectedSaleLabel = s.sale_no + ' — ' + s.date;
            this.saleSearch        = s.sale_no;
            this.showSaleDrop      = false;
            // Auto-load items dari struk ini
            await this.fetchSaleByBarcode(s.sale_no);
        },

        async fetchSaleByBarcode(overrideSaleNo = null) {
            const saleNo = overrideSaleNo || this.scannedBarcode;
            if (!saleNo) return;

            try {
                const res = await fetch(`/returns/customer/search-sale?sale_no=${encodeURIComponent(saleNo)}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                if (!res.ok) {
                    alert(data.error || 'Transaksi tidak ditemukan.');
                    return;
                }

                // Update pilihan struk
                this.selectedSaleId    = data.sale_id;
                this.selectedSaleLabel = data.sale_no + ' — ' + data.date;
                this.saleSearch        = data.sale_no;

                // Muat semua item dari struk ke keranjang retur
                this.rows = [];
                data.items.forEach(item => {
                    this.rows.push({
                        _key:      this._key++,
                        id:        item.id,
                        sku:       item.sku,
                        label:     item.label,
                        price:     item.price,
                        qty:       item.qty,
                        condition: 'good'
                    });
                });

                this.scannedBarcode = '';

            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan. Pastikan koneksi jaringan Anda stabil dan coba lagi.');
            }
        },

        async doSearch(isInit = false) {
            if (!isInit && this.search.length > 0 && this.search.length < 2) {
                this.results = [];
                return;
            }
            this.loading = true;
            try {
                const response = await fetch(`/api/v1/variants/search?q=${encodeURIComponent(this.search)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    this.results = await response.json();
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
                const response = await fetch(`/api/v1/variants/search?q=${encodeURIComponent(q)}&exact=1`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const data = await response.json();
                    if (data.length > 0) {
                        this.addRow(data[0]);
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
        }
    };
}
</script>
@endpush
@endsection
