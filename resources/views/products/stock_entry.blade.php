@extends('layouts.app')
@section('title', 'Tambah Stok Lokal')
@section('page-title', 'Tambah Stok Lokal')
@section('breadcrumb', 'Produk / Stok / Tambah Lokal')

@section('content')
<div class="max-w-2xl mx-auto" x-data="stockEntry()">

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="bg-indigo-600 px-6 py-8 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
            <div class="absolute -left-4 -bottom-4 w-24 h-24 bg-black/10 rounded-full blur-xl"></div>
            
            <div class="relative z-10">
                <h2 class="text-white font-bold text-xl">Input Barang Toko / Gudang</h2>
                <p class="text-indigo-100 text-sm mt-1">Gunakan form ini untuk menambah stok barang sisaan atau barang yang masuk bukan dari kiriman pusat.</p>
            </div>
        </div>

        <form action="{{ route('products.stock-entry.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            <input type="hidden" name="location_id" value="{{ $location->id }}">
            <input type="hidden" name="location_type" value="{{ $locationType }}">

            {{-- Location Info --}}
            <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold">
                    {{ strtoupper(substr($locationType, 0, 1)) }}
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Lokasi Input</p>
                    <p class="text-sm font-bold text-gray-900">{{ $location->name }} ({{ ucfirst($locationType) }})</p>
                </div>
            </div>

            {{-- Search Variant --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-gray-700">Cari Produk / SKU <span class="text-red-500">*</span></label>
                <div class="relative" @click.outside="showDrop = false">
                    <div class="relative">
                        <input type="text" 
                            x-model="search" 
                            @input.debounce.300ms="doSearch()"
                            @focus="if(search.length > 1) showDrop = true"
                            placeholder="Ketik SKU atau Nama Produk..."
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <div x-show="loading" class="absolute right-4 top-3.5">
                            <svg class="animate-spin h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </div>
                    </div>

                    {{-- Dropdown --}}
                    <div x-show="showDrop && results.length > 0" x-transition 
                        class="absolute left-0 right-0 top-full mt-2 bg-white border border-gray-200 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto">
                        <template x-for="v in results" :key="v.id">
                            <button type="button" @click="selectVariant(v)"
                                class="w-full text-left px-4 py-3 hover:bg-indigo-50 border-b border-gray-50 last:border-0 transition-colors">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-mono font-bold text-indigo-600" x-text="v.sku"></span>
                                    <span class="text-[10px] text-gray-400" x-text="'Rp ' + Number(v.price).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="text-sm text-gray-800 mt-0.5" x-text="v.text"></div>
                            </button>
                        </template>
                    </div>
                </div>
                
                {{-- Selected Preview --}}
                <div x-show="selectedVariant" x-transition class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-between">
                    <div>
                        <input type="hidden" name="product_variant_id" :value="selectedVariant?.id">
                        <p class="text-xs font-bold text-indigo-700" x-text="selectedVariant?.sku"></p>
                        <p class="text-sm font-medium text-gray-800" x-text="selectedVariant?.text"></p>
                    </div>
                    <button type="button" @click="selectedVariant = null; search = ''" class="text-gray-400 hover:text-red-500 text-sm">✕</button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Qty --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700">Jumlah Masuk <span class="text-red-500">*</span></label>
                    <input type="number" name="qty" required min="1" placeholder="Contoh: 10"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Note --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700">Asal Barang / Catatan <span class="text-red-500">*</span></label>
                    <input type="text" name="note" required placeholder="Contoh: Sisaan stok toko lama"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                <a href="{{ route('products.index') }}" class="text-sm text-gray-600 hover:underline">← Batal</a>
                <button type="submit" :disabled="!selectedVariant"
                    class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold px-8 py-3 rounded-xl text-sm shadow-lg shadow-indigo-200 transition-all active:scale-95">
                    SIMPAN STOK
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
function stockEntry() {
    return {
        search: '',
        results: [],
        loading: false,
        showDrop: false,
        selectedVariant: null,

        async doSearch() {
            if (this.search.length < 2) {
                this.results = [];
                return;
            }
            this.loading = true;
            try {
                const res = await fetch(`{{ route('products.stock-entry.search') }}?q=${encodeURIComponent(this.search)}`);
                this.results = await res.json();
                this.showDrop = true;
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        selectVariant(v) {
            this.selectedVariant = v;
            this.search = v.sku;
            this.showDrop = false;
        }
    }
}
</script>
@endpush
@endsection
