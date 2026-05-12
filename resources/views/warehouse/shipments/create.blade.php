@extends('layouts.app')
@section('title', 'Buat Pengiriman')
@section('page-title', 'Buat Pengiriman ke Toko')
@section('breadcrumb', 'Gudang / Pengiriman / Baru')

@section('content')
<div class="max-w-5xl mx-auto" x-data="shipmentForm()">

    <form method="POST" action="{{ route('warehouse.shipments.store') }}" class="space-y-5" @submit="validateForm($event)">
        @csrf

        {{-- Informasi Gudang & Toko --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Detail Pengiriman</h2>
                <span class="text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-md">{{ $shipNo }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Gudang <span class="text-red-500">*</span></label>
                    <select name="warehouse_id" x-model="warehouse_id" @change="resetRows()" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('warehouse_id') border-red-500 @enderror">
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-[10px] text-gray-500 mt-1">Mengubah gudang akan mereset daftar item di bawah.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ke Toko <span class="text-red-500">*</span></label>
                    <select name="store_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('store_id') border-red-500 @enderror">
                        <option value="">-- Pilih Toko --</option>
                        @foreach($stores as $s)
                        <option value="{{ $s->id }}" {{ old('store_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('store_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2" placeholder="Catatan pengiriman (opsional)…"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Daftar Item --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h2 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Daftar Produk Kiriman</h2>
                <button type="button" @click="addRow()"
                    class="text-sm bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-3 py-1.5 rounded-md font-medium transition-colors">
                    + Tambah Baris
                </button>
            </div>

            <div class="space-y-3">
                {{-- Table Header --}}
                <div class="hidden md:grid grid-cols-12 gap-3 text-xs font-bold text-gray-500 uppercase px-2">
                    <div class="col-span-6">Cari Produk / SKU</div>
                    <div class="col-span-2 text-center">Stok Gudang</div>
                    <div class="col-span-3">Qty Kirim</div>
                    <div class="col-span-1"></div>
                </div>

                {{-- Baris Item --}}
                <template x-for="(row, idx) in rows" :key="idx">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start bg-gray-50 md:bg-transparent p-3 md:p-0 rounded-lg border md:border-0 border-gray-100 relative">
                        
                        {{-- Autocomplete Pencarian --}}
                        <div class="col-span-1 md:col-span-6 relative" @click.outside="row.showDropdown = false">
                            <label class="block md:hidden text-xs font-semibold text-gray-500 mb-1">Produk</label>
                            
                            <!-- Input Search -->
                            <div class="relative">
                                <input type="text" x-model="row.search" 
                                    @input.debounce.300ms="searchProduct(idx)"
                                    @focus="if(row.search.length >= 2) row.showDropdown = true"
                                    placeholder="Ketik nama / SKU..." 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors"
                                    :class="row.variant_id ? 'bg-indigo-50 border-indigo-200 text-indigo-900 font-medium' : ''">
                                
                                <!-- Loading Indicator -->
                                <div x-show="row.loading" class="absolute right-3 top-2.5">
                                    <svg class="animate-spin h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                </div>
                            </div>
                            
                            <!-- Hidden input untuk dikirim ke backend -->
                            <input type="hidden" :name="`items[${idx}][sku]`" :value="row.sku">

                            <!-- Dropdown Hasil -->
                            <div x-show="row.showDropdown && row.results.length > 0" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto" style="display: none;">
                                <template x-for="res in row.results" :key="res.sku">
                                    <div @click="selectProduct(idx, res)" 
                                         class="p-3 border-b border-gray-50 hover:bg-indigo-50 cursor-pointer flex justify-between items-center transition-colors">
                                        <div>
                                            <p class="text-xs font-mono text-indigo-600 font-bold" x-text="res.sku"></p>
                                            <p class="text-sm text-gray-800" x-text="res.name"></p>
                                        </div>
                                        <div class="text-right shrink-0 ml-2">
                                            <span class="text-[10px] uppercase text-gray-400 font-bold">Stok</span>
                                            <p class="text-sm font-bold" :class="res.stock > 0 ? 'text-green-600' : 'text-red-500'" x-text="res.stock"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="row.showDropdown && row.results.length === 0 && row.search.length >= 2 && !row.loading" 
                                 class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl p-3 text-sm text-gray-500 text-center" style="display: none;">
                                Produk tidak ditemukan di gudang ini.
                            </div>
                        </div>

                        {{-- Display Stok --}}
                        <div class="col-span-1 md:col-span-2 flex flex-col justify-center items-center h-full">
                            <label class="block md:hidden text-xs font-semibold text-gray-500 mb-1 w-full">Stok Tersedia</label>
                            <span class="text-sm font-bold w-full md:text-center p-2 rounded-lg"
                                  :class="row.stock === null ? 'text-gray-300' : (row.stock > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700')"
                                  x-text="row.stock === null ? '-' : row.stock"></span>
                        </div>

                        {{-- Input Qty --}}
                        <div class="col-span-1 md:col-span-3 relative">
                            <label class="block md:hidden text-xs font-semibold text-gray-500 mb-1">Qty Kirim</label>
                            <input type="number" :name="`items[${idx}][qty_sent]`" x-model.number="row.qty"
                                min="1" :max="row.stock" required placeholder="0"
                                :disabled="!row.variant_id"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                :class="(row.qty > row.stock && row.stock !== null) ? 'border-red-500 focus:ring-red-500' : 'border-gray-300'">
                            
                            <p x-show="row.qty > row.stock && row.stock !== null" class="absolute -bottom-4 left-0 text-[10px] text-red-500 font-medium">Melebihi stok!</p>
                        </div>

                        {{-- Tombol Hapus --}}
                        <div class="col-span-1 md:col-span-1 flex justify-end md:justify-center items-center h-full pt-4 md:pt-0">
                            <button type="button" @click="removeRow(idx)"
                                class="text-red-400 hover:text-white bg-white hover:bg-red-500 border border-red-200 rounded-md p-1.5 transition-colors"
                                :disabled="rows.length === 1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            @if($errors->has('items'))
            <p class="text-red-500 text-sm mt-2 border border-red-200 bg-red-50 p-2 rounded-lg">{{ $errors->first('items') }}</p>
            @endif
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('warehouse.shipments.index') }}" class="text-sm text-gray-600 hover:text-gray-900 hover:underline font-medium">← Kembali</a>
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-3 rounded-xl text-sm shadow-md transition-transform active:scale-95">
                Buat Pengiriman
            </button>
        </div>
    </form>

</div>

<script>
function shipmentForm() {
    return {
        warehouse_id: '{{ old("warehouse_id") }}',
        rows: [],

        init() {
            this.addRow(); // Tambah 1 baris kosong di awal
        },

        emptyRow() {
            return { 
                variant_id: null, 
                sku: '', 
                name: '',
                stock: null, 
                qty: 1,
                search: '',
                results: [],
                showDropdown: false,
                loading: false
            };
        },

        addRow() { 
            this.rows.push(this.emptyRow()); 
        },

        removeRow(idx) { 
            if (this.rows.length > 1) this.rows.splice(idx, 1); 
        },

        resetRows() {
            // Hapus semua baris dan sisakan 1 baris kosong jika gudang diganti
            this.rows = [this.emptyRow()];
        },

        async searchProduct(idx) {
            let row = this.rows[idx];
            
            // Hapus seleksi jika user mengetik ulang
            if(row.variant_id) {
                row.variant_id = null;
                row.sku = '';
                row.stock = null;
            }

            if (row.search.length < 2) {
                row.results = [];
                return;
            }
            if (!this.warehouse_id) {
                alert('Silakan pilih Gudang Asal terlebih dahulu!');
                row.search = '';
                return;
            }

            row.loading = true;
            try {
                // Fetch ke API dengan mengirim param query dan warehouse_id
                const response = await fetch(`/api/v1/variants/search?q=${encodeURIComponent(row.search)}&warehouse_id=${this.warehouse_id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if(response.ok) {
                    row.results = await response.json();
                }
            } catch (error) {
                console.error("Gagal mengambil data produk", error);
            } finally {
                row.loading = false;
                row.showDropdown = true;
            }
        },

        selectProduct(idx, product) {
            let row = this.rows[idx];
            
            // Cek jika produk sudah ada di baris lain
            let isDuplicate = this.rows.some((r, i) => i !== idx && r.sku === product.sku);
            if(isDuplicate) {
                alert('Produk ini sudah ada di daftar kiriman!');
                row.search = '';
                row.showDropdown = false;
                return;
            }

            row.variant_id = product.id;
            row.sku = product.sku;
            row.name = product.name;
            row.stock = product.stock;
            row.search = product.sku + ' — ' + product.name; // Tampilkan nama indah di input
            row.showDropdown = false;
            row.qty = 1;
        },

        validateForm(e) {
            // Validasi final sebelum disubmit ke backend
            let isValid = true;
            for(let i=0; i < this.rows.length; i++) {
                let row = this.rows[i];
                if(!row.sku) {
                    alert('Terdapat baris produk yang masih kosong / belum dipilih.');
                    isValid = false;
                    break;
                }
                if(row.qty > row.stock) {
                    alert(`Kuantitas kirim untuk SKU ${row.sku} melebihi stok gudang!`);
                    isValid = false;
                    break;
                }
            }
            
            if(!isValid) {
                e.preventDefault(); // Batalkan submit
            }
        }
    }
}
</script>
@endsection