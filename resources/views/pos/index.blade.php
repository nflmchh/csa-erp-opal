@extends('layouts.app')
@section('title', 'Kasir — ' . $store->name)
@section('page-title', 'Terminal Kasir')

@section('content')

<!-- Menyuntikkan data katalog dengan enkripsi khusus (JSON_HEX) agar tanda kutip di nama produk tidak merusak sistem -->
<script>
    window.POS_CATALOG_DATA = {!! json_encode($catalog, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
</script>

<!-- WRAPPER UTAMA: Diubah paksa menjadi flex-row (Selalu berdampingan) -->
<div class="flex flex-row gap-4 xl:gap-6 h-[calc(100vh-6.5rem)] w-full overflow-hidden -mt-2"
     x-data="posApp({{ $session->id }}, {{ $store->id }})"
     @keydown.window="handleGlobalScan($event)"
     @click.window="handleGlobalClick($event)">

     
    {{-- ==========================================
         KIRI: KATALOG PRODUK & PENCARIAN 
         ========================================== --}}
    <!-- flex-1 min-w-0 memastikan panel ini fleksibel tapi tidak akan pernah hilang -->
    <div class="flex-1 min-w-0 flex flex-col bg-transparent overflow-hidden h-full">
        {{-- 1. Search Bar & Scanner --}}
        <div class="shrink-0 mb-4">
            <div class="relative group">
                <!-- <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                    <svg class="w-6 h-6 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div> -->
                <input type="text" id="searchInput" x-model="search"
                    @keydown.enter.prevent="handleEnterScan()"
                    placeholder="Scan Barcode di sini, atau ketik nama/SKU produk..."
                    class="w-full bg-white border border-gray-200 rounded-2xl pl-14 pr-6 py-4 text-base shadow-sm focus:ring-4 focus:ring-indigo-500/20 text-gray-800 placeholder-gray-400 font-medium transition-all">
                
                <div x-show="search.length > 0" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                    <button @click="search = ''; document.getElementById('searchInput').focus()" class="bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-full p-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- 2. Grid Produk (Scrollable Area) --}}
        <div class="flex-1 overflow-y-auto pb-6 pr-2 custom-scrollbar">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                
                <template x-for="p in filteredCatalog" :key="p.id">
                    <!-- KARTU PRODUK -->
                    <div @click="addToCart(p)"
                         class="relative bg-white rounded-2xl transition-all duration-200 flex flex-col h-full overflow-hidden cursor-pointer select-none group shadow-sm hover:shadow-md"
                         :class="[
                            p.stock <= 0 ? 'opacity-50 grayscale' : 'hover:-translate-y-1',
                            getCartQty(p.id) > 0 ? 'ring-4 ring-indigo-500 ring-offset-2' : 'border border-gray-100'
                         ]">
                        
                        <!-- Gambar Produk -->
                        <div class="h-32 xl:h-40 w-full bg-gray-50 relative shrink-0 border-b border-gray-100">
                            <img :src="p.image" :alt="p.name" class="w-full h-full object-cover">
                            
                            <!-- Overlay Jika Stok Habis -->
                            <div x-show="p.stock <= 0" class="absolute inset-0 bg-white/60 flex items-center justify-center backdrop-blur-[1px]">
                                <span class="bg-red-500 text-white text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wider">Habis</span>
                            </div>

                            <!-- HIGHLIGHT: Badge Jumlah di Keranjang (Menyala jika dibeli) -->
                            <div x-show="getCartQty(p.id) > 0" x-transition.scale
                                 class="absolute top-2 right-2 bg-indigo-600 text-white w-9 h-9 flex items-center justify-center rounded-full font-black text-sm shadow-lg border-2 border-white">
                                <span x-text="getCartQty(p.id)"></span>
                            </div>

                            <!-- Badge Sisa Stok (Kiri Bawah Gambar) -->
                            <div x-show="p.stock > 0" class="absolute bottom-2 left-2 bg-black/60 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-1 rounded-md">
                                Sisa: <span x-text="p.stock"></span>
                            </div>
                        </div>

                        <!-- Info Produk -->
                        <div class="p-3 flex flex-col flex-1">
                            <h3 class="text-xs xl:text-sm font-bold text-gray-800 leading-snug mb-1 line-clamp-2" x-text="p.name"></h3>
                            <p class="text-[10px] text-gray-500 font-mono mb-2" x-text="p.sku"></p>
                            
                            <div class="mt-auto pt-2 border-t border-gray-50 flex items-center justify-between">
                                <p class="text-indigo-600 font-black text-sm xl:text-base" x-text="p.price_formatted"></p>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Jika tidak ada produk -->
                <div x-show="filteredCatalog.length === 0" class="col-span-full flex flex-col items-center justify-center py-20 text-gray-400 bg-white rounded-2xl border-2 border-dashed border-gray-200">
                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <p class="text-base font-medium text-gray-500">Produk atau SKU tidak ditemukan.</p>
                </div>

            </div>
        </div>
    </div>

    {{-- ==========================================
         KANAN: TAGIHAN & PEMBAYARAN 
         ========================================== --}}
    <!-- Lebar dikunci mutlak (shrink-0) agar tidak bisa merusak katalog di sebelahnya -->
    <div class="w-[360px] xl:w-[420px] shrink-0 flex flex-col bg-white rounded-3xl overflow-hidden shadow-xl h-full border border-gray-200">

        {{-- Header Tagihan --}}
        <div class="px-5 py-4 border-b border-gray-200 shrink-0 flex justify-between items-center bg-gray-50">
            <div>
                <h2 class="text-lg font-black text-gray-800">Detail Pesanan</h2>
                <p class="text-[10px] font-bold text-gray-500 mt-1 uppercase tracking-wider">KASIR: {{ Auth::user()->name }}</p>
            </div>
            
            {{-- Kumpulan Tombol Aksi (Sebelah Kanan) --}}
            <div class="flex items-center gap-2">
                
                <button x-show="isFocusMode" @click="exitFocusMode()" style="display: none;"
                        class="p-2 text-orange-600 bg-orange-50 hover:bg-orange-500 hover:text-white border border-orange-200 rounded-xl transition-colors shadow-sm" 
                        title="Keluar Mode Fokus">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                </button>

                <div class="relative" x-data="{ openExport: false }">
                    <button @click="openExport = !openExport" 
                            class="p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-500 hover:text-white border border-indigo-200 rounded-xl transition-colors shadow-sm" 
                            title="Export Laporan Penjualan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </button>
                    <div x-show="openExport" @click.outside="openExport = false" style="display: none;"
                         class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden">
                        <a href="{{ route('pos.report.export', ['period' => 'today']) }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 border-b border-gray-50 font-medium">Laporan Hari Ini</a>
                        <a href="{{ route('pos.report.export', ['period' => 'weekly']) }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 border-b border-gray-50 font-medium">Laporan Minggu Ini</a>
                        <a href="{{ route('pos.report.export', ['period' => 'monthly']) }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 font-medium">Laporan Bulan Ini</a>
                    </div>
                </div>

                <button @click="if(cart.length>0 && confirm('Yakin ingin mereset pesanan?')) cart=[]" 
                        class="p-2 text-red-500 bg-red-50 hover:bg-red-500 hover:text-white border border-red-200 rounded-xl transition-colors shadow-sm" 
                        title="Kosongkan Keranjang">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>

            </div>
        </div>

        {{-- List Barang (Minimalis) --}}
        <div class="flex-1 overflow-y-auto p-2 bg-white custom-scrollbar">
            
            <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-center px-6">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-3 border-2 border-dashed border-gray-200">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="text-sm text-gray-500 font-semibold">Belum ada pesanan</p>
                <p class="text-[10px] text-gray-400 mt-1">Pilih produk di katalog atau scan barcode.</p>
            </div>

            <template x-for="(item, idx) in cart" :key="item.variant_id">
                <div class="flex items-center gap-3 p-2 border-b border-gray-100 hover:bg-gray-50 transition-colors group rounded-xl">
                    <!-- Foto Thumbnail di Cart -->
                    <img :src="item.image" class="w-12 h-12 object-cover rounded-lg bg-gray-100 shrink-0 border border-gray-200">
                    
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-gray-800 truncate" x-text="item.name"></p>
                        <p class="text-[10px] text-indigo-600 font-semibold" x-text="'Rp ' + item.price.toLocaleString('id-ID')"></p>
                    </div>

                    <!-- Qty Control Minimalis -->
                    <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-lg p-0.5 shrink-0 shadow-sm">
                        <button @click="qtyDown(idx)" class="w-6 h-6 flex items-center justify-center text-gray-600 hover:text-red-500 hover:bg-red-50 rounded font-bold transition-colors">−</button>
                        <span class="w-5 text-center text-xs font-bold text-gray-800" x-text="item.qty"></span>
                        <button @click="qtyUp(idx)" :disabled="item.qty >= item.maxQty" class="w-6 h-6 flex items-center justify-center text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded font-bold disabled:opacity-30 transition-colors">+</button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Area Pembayaran (Statis di Bawah - Direvisi menjadi Tema Terang) --}}
        <div class="bg-gray-50 shrink-0 rounded-t-3xl shadow-[0_-4px_20px_rgba(0,0,0,0.05)] border-t border-gray-200 p-5">
            
            <!-- Rincian -->
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-gray-500 text-xs font-medium">
                    <span>Subtotal (<span class="font-bold text-gray-800" x-text="cart.reduce((s,i)=>s+i.qty,0)"></span> barang)</span>
                    <span class="font-bold text-gray-800" x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between items-center text-gray-500 text-xs font-medium">
                    <span>Diskon (Rp)</span>
                    <input type="number" x-model.number="discountAmount" min="0" step="1000" class="w-28 bg-white border border-gray-300 rounded-lg px-2 py-1.5 text-right font-bold text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm">
                </div>
                
                <div class="flex justify-between items-end border-t border-gray-200 pt-3 mt-3">
                    <span class="text-gray-500 text-[10px] font-bold uppercase tracking-widest mb-1">Total Tagihan</span>
                    <span class="text-2xl xl:text-3xl font-black text-indigo-600" x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                </div>
            </div>

            <form method="POST" action="{{ route('pos.sale') }}" @submit.prevent="submitSale($event)">
                @csrf
                <input type="hidden" name="payment_method_id" :value="paymentMethodId">
                <input type="hidden" name="amount_paid" :value="amountPaid">
                <input type="hidden" name="discount_amount" :value="discountAmount">
                <template x-for="(item, i) in cart" :key="i">
                    <span>
                        <input type="hidden" :name="`items[${i}][variant_id]`" :value="item.variant_id">
                        <input type="hidden" :name="`items[${i}][qty]`" :value="item.qty">
                        <input type="hidden" :name="`items[${i}][unit_price]`" :value="item.price">
                    </span>
                </template>

                <!-- Input Uang Cepat (Tema Terang) -->
                <div class="bg-white rounded-2xl p-3 mb-4 border border-gray-200 shadow-sm">
                    <div class="flex gap-3 items-center">
                        <div class="flex-1">
                            <label class="text-[9px] text-gray-500 font-bold uppercase tracking-wider block mb-1">Uang Diterima</label>
                            <input type="number" x-model.number="amountPaid" min="0" step="1000" class="w-full bg-transparent border-0 text-xl font-black text-gray-900 p-0 focus:ring-0 placeholder-gray-300" placeholder="0">
                        </div>
                        <div class="w-px h-10 bg-gray-200"></div>
                        <div class="flex-1 text-right">
                            <label class="text-[9px] text-gray-500 font-bold uppercase tracking-wider block mb-1">Kembalian</label>
                            <div class="text-lg font-black" :class="change >= 0 ? 'text-green-600' : 'text-red-500'" x-text="'Rp ' + Math.max(0, change).toLocaleString('id-ID')"></div>
                        </div>
                    </div>
                </div>

                <!-- Metode Bayar & Tombol Proses -->
                <div class="flex gap-2">
                    <select x-model="paymentMethodId" @change="if(paymentMethodId && !isCashSelected()) amountPaid = total" required class="w-1/3 bg-white border border-gray-300 text-gray-800 text-[11px] font-bold rounded-xl px-2 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none cursor-pointer appearance-none text-center shadow-sm">
                        <option value="" disabled>Pilih Metode</option>
                        @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->id }}" data-type="{{ $pm->type }}">{{ strtoupper($pm->name) }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                        :disabled="cart.length === 0 || !paymentMethodId || amountPaid < total || processing"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed text-white font-black py-3 rounded-xl text-base shadow-md hover:shadow-indigo-500/30 transition-all active:scale-95 flex items-center justify-center">
                        <span x-show="!processing">BAYAR</span>
                        <svg x-show="processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div x-show="showReceiptModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div @click.outside="showReceiptModal = false" class="bg-white w-full max-w-sm rounded-3xl overflow-hidden shadow-2xl flex flex-col h-[85vh]">
            <div class="bg-indigo-600 px-5 py-4 flex justify-between items-center shrink-0">
                <h3 class="text-white font-bold text-lg">Transaksi Berhasil!</h3>
                <button @click="showReceiptModal = false" class="text-indigo-200 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            
            <div class="flex-1 bg-gray-200 overflow-y-auto p-4 flex justify-center custom-scrollbar">
                <div id="print-area" x-html="receiptHtmlHtml" class="bg-white shadow-md p-2"></div>
            </div>

            <div class="p-4 bg-white border-t border-gray-100 flex flex-col gap-2 shrink-0">
                <button @click="executePrint()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-3.5 rounded-xl text-lg shadow-md transition-colors flex items-center justify-center gap-2">
                    🖨️ CETAK STRUK SEKARANG
                </button>
                <button @click="showReceiptModal = false" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 rounded-xl transition-colors">
                    Tutup & Lanjut Transaksi Baru
                </button>
            </div>
        </div>
    </div>
</div>



<style>
/* Mempercantik Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }

/* =======================================================
   CSS MODE CETAK (PRINT) - ANTI KERTAS KOSONG
   ======================================================= */
@media print {
    /* 1. Sembunyikan Sidebar, Header, Navigasi (bawaan layout Laravel Anda) */
    header, nav, aside { display: none !important; }
    
    /* 2. Sembunyikan Area Katalog (Kiri) & Area Tagihan (Kanan) secara permanen dari kertas */
    .flex-1.min-w-0, .w-\\[360px\\], .xl\\:w-\\[420px\\] { display: none !important; }
    
    /* 3. Matikan paksaan tinggi layar (100vh) agar kertas tidak ikut panjang */
    .h-\\[calc\\(100vh-6\\.5rem\\)\\] { height: auto !important; overflow: visible !important; }
    
    /* 4. Lepaskan Pop-up Modal dari posisinya agar menempel langsung ke kertas putih */
    .fixed.inset-0 { position: static !important; background: transparent !important; padding: 0 !important; }
    .bg-white.max-w-sm { max-width: 100% !important; box-shadow: none !important; height: auto !important; }
    
    /* 5. Sembunyikan Header Biru pada Modal & Tombol "Cetak" di bawahnya */
    .bg-indigo-600.px-5, .border-t.border-gray-100 { display: none !important; }
    
    /* 6. Hilangkan margin bawaan browser */
    @page { margin: 0; }
}
</style>
@endsection

@push('scripts')
<script>
const posCatalogData = window.POS_CATALOG_DATA || [];

function posApp(sessionId, storeId) {
    return {
        catalog: posCatalogData, 
        cart: [],
        search: '',
        paymentMethodId: '',
        amountPaid: '',
        discountAmount: 0,
        processing: false,
        barcodeBuffer: '',
        barcodeTimer: null,
        isFocusMode: false,
        clickCount: 0,
        clickTimer: null,
        showReceiptModal: false,
        receiptHtmlHtml: '',
        currentSaleData: null,
        printMethod: localStorage.getItem('pos_print_method') || 'pc_usb',
        cachedBluetoothDevice: null, // CACHE KONEKSI UNTUK PC BLUETOOTH
        cachedCharacteristic: null,

        handleGlobalClick(e) {
            // Jangan hitung ketukan jika kasir sedang mengklik Tombol, Link, atau Input/Kolom Teks
            if (['INPUT', 'BUTTON', 'A', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) return;

            this.clickCount++;
            clearTimeout(this.clickTimer);
            
            // Waktu maksimal antar ketukan adalah 400 milidetik
            this.clickTimer = setTimeout(() => {
                if (this.clickCount >= 3) {
                    this.toggleFocusMode();
                }
                this.clickCount = 0;
            }, 400); 
        },

        enterFocusMode() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    alert(`Gagal masuk mode fokus: ${err.message}`);
                });
                this.isFocusMode = true;
            }
        },

        // Fungsi KHUSUS KELUAR Fullscreen
        exitFocusMode() {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            }
            this.isFocusMode = false;
        },
        
        get filteredCatalog() {
            if (this.search.trim() === '') return this.catalog;
            let q = this.search.toLowerCase();
            return this.catalog.filter(p => 
                p.sku.toLowerCase().includes(q) || 
                p.name.toLowerCase().includes(q)
            );
        },

        getCartQty(variantId) {
            const item = this.cart.find(i => i.variant_id === variantId);
            return item ? item.qty : 0;
        },

        get subtotal() { return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0); },
        get total() { return Math.max(0, this.subtotal - this.discountAmount); },
        get change() { return (Number(this.amountPaid) || 0) - this.total; },

        isCashSelected() {
            let select = document.querySelector('select[x-model="paymentMethodId"]');
            let option = select.options[select.selectedIndex];
            return option ? option.getAttribute('data-type') === 'cash' : true;
        },

        addToCart(product) {
            if (product.stock <= 0) {
                alert(`Stok produk ${product.sku} sudah habis!`);
                return;
            }
            
            const existing = this.cart.find(i => i.variant_id === product.id);
            if (existing) {
                if (existing.qty < existing.maxQty) existing.qty++;
                else alert('Maksimal stok tercapai!');
            } else {
                this.cart.unshift({
                    variant_id: product.id,
                    sku:        product.sku,
                    name:       product.name,
                    price:      product.price,
                    qty:        1,
                    maxQty:     product.stock,
                    image:      product.image 
                });
            }
            
            this.search = '';
            document.getElementById('searchInput').focus();
        },

        qtyUp(idx) {
            const item = this.cart[idx];
            if (item.qty < item.maxQty) item.qty++;
        },

        qtyDown(idx) {
            const item = this.cart[idx];
            if (item.qty > 1) item.qty--;
            else this.cart.splice(idx, 1);
        },

        handleEnterScan() {
            if (this.search.trim() !== '') {
                this.processScan(this.search.trim());
            }
        },

        handleGlobalScan(e) {
            // PERBAIKAN: Hapus pengecualian 'searchInput' agar fungsi global 
            // ini sepenuhnya mati jika kursor Anda sedang berada di dalam kolom input apapun
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;

            if (e.key === 'Enter') {
                if (this.barcodeBuffer.length > 0) {
                    this.processScan(this.barcodeBuffer);
                    this.barcodeBuffer = ''; 
                }
            } else {
                if(e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                    this.barcodeBuffer += e.key;
                    clearTimeout(this.barcodeTimer);
                    this.barcodeTimer = setTimeout(() => { this.barcodeBuffer = ''; }, 50);
                }
            }
        },

        toggleFocusMode() {
            if (!document.fullscreenElement) {
                // Masuk Fullscreen
                document.documentElement.requestFullscreen().catch(err => {
                    alert(`Gagal masuk mode fokus: ${err.message}`);
                });
                this.isFocusMode = true;
            } else {
                // Keluar Fullscreen
                document.exitFullscreen();
                this.isFocusMode = false;
            }
        },

        processScan(scannedSku) {
            let product = this.catalog.find(p => p.sku.toLowerCase() === scannedSku.toLowerCase());
            if (product) {
                this.addToCart(product);
            } else {
                alert('SKU "' + scannedSku + '" tidak ditemukan atau stok habis di toko ini!');
                this.search = ''; 
            }
        },

        async submitSale(e) {
            if (this.cart.length === 0 || !this.paymentMethodId || Number(this.amountPaid) < this.total) return;
            this.processing = true;

            try {
                let formData = new FormData(e.target);
                let res = await fetch("{{ route('pos.sale') }}", {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' } // Penanda AJAX
                });
                
                let data = await res.json();
                
                if (data.success) {
                    this.receiptHtmlHtml = data.html;      // Tampilkan desain di Modal
                    this.currentSaleData = data.sale;      // Simpan data mentah untuk Flutter
                    this.showReceiptModal = true;          // Munculkan Modal
                    
                    // Kosongkan form untuk transaksi berikutnya
                    this.cart = []; 
                    this.amountPaid = '';
                    this.paymentMethodId = '';
                    this.discountAmount = 0;
                } else {
                    alert('Gagal: ' + data.error);
                }
            } catch(err) {
                alert('Terjadi kesalahan jaringan.');
            }
            this.processing = false;
        },
        async executePrint() {
            if (this.printMethod === 'pc_usb') {
                // METODE IFRAME: Cetak secara tersembunyi tanpa merusak layar kasir
                let printFrame = document.createElement('iframe');
                printFrame.style.display = 'none';
                document.body.appendChild(printFrame);
                
                // Masukkan desain struk ke dalam iframe
                printFrame.contentDocument.write('<html><head><style>@page { margin: 0; } body { margin: 0; font-family: monospace; }</style></head><body>' + this.receiptHtmlHtml + '</body></html>');
                printFrame.contentDocument.close();
                
                // Fokus dan Cetak Iframe
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
                
                // Hapus iframe setelah selesai (Jeda 2 detik)
                setTimeout(() => document.body.removeChild(printFrame), 2000);
            } 
            else if (this.printMethod === 'android_flutter') {
                // FORMAT ULANG JSON UNTUK FLUTTER AGAR TIDAK ERROR "NULL"
                let sale = this.currentSaleData;
                let dataStruk = {
                    store_name: sale.store.name,
                    store_address: sale.store.address || '',
                    receipt_no: sale.sale_no,
                    date: sale.created_at.substring(0, 16).replace('T', ' '),
                    cashier: sale.creator ? sale.creator.name.substring(0, 15) : '-',
                    items: sale.items.map(item => ({
                        name: String(item.variant.product.name).substring(0, 30),
                        qty: item.qty,
                        price: parseInt(item.unit_price).toLocaleString('id-ID'),
                        total: parseInt(item.subtotal).toLocaleString('id-ID')
                    })),
                    subtotal: parseInt(sale.subtotal).toLocaleString('id-ID'),
                    grand_total: parseInt(sale.total_amount).toLocaleString('id-ID'),
                    paid: parseInt(sale.amount_paid).toLocaleString('id-ID')
                };

                if (window.PrintChannel) {
                    window.PrintChannel.postMessage(JSON.stringify(dataStruk));
                } else {
                    alert("Aplikasi Native Flutter tidak terdeteksi!");
                }
            } 
            else {
                // KELUARGA WEB BLUETOOTH (iOS, PC, Android Chrome)
                try {
                    if (!this.cachedCharacteristic) {
                        this.cachedBluetoothDevice = await navigator.bluetooth.requestDevice({
                            acceptAllDevices: true,
                            optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                        });
                        
                        this.cachedBluetoothDevice.addEventListener('gattserverdisconnected', () => {
                            this.cachedCharacteristic = null;
                        });

                        const server = await this.cachedBluetoothDevice.gatt.connect();
                        const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                        this.cachedCharacteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                    }

                    const alignLR = (left, right, max = 48) => {
                        let spaces = max - left.toString().length - right.toString().length;
                        return left + " ".repeat(spaces > 0 ? spaces : 1) + right + "\n";
                    };
                    const alignC = (text, max = 48) => {
                        let spaces = Math.floor((max - text.length) / 2);
                        return " ".repeat(spaces > 0 ? spaces : 0) + text + "\n";
                    };

                    let sale = this.currentSaleData;
                    let text = "\n"; 
                    text += alignC("SEVENKEY ERP");
                    text += alignC(sale.store.name);
                    text += "------------------------------------------------\n";
                    text += alignLR("No:", sale.sale_no);
                    text += alignLR("Tgl:", sale.created_at.substring(0, 16).replace('T', ' '));
                    text += alignLR("Kasir:", sale.creator ? sale.creator.name.substring(0, 15) : '-');
                    text += "------------------------------------------------\n";
                    
                    sale.items.forEach(item => {
                        text += String(item.variant.product.name).substring(0, 48) + "\n";
                        let priceQty = item.qty + " x " + parseInt(item.unit_price).toLocaleString('id-ID');
                        let totalStr = "Rp " + parseInt(item.subtotal).toLocaleString('id-ID');
                        text += alignLR(priceQty, totalStr);
                    });
                    
                    text += "------------------------------------------------\n";
                    text += alignLR("Subtotal", "Rp " + parseInt(sale.subtotal).toLocaleString('id-ID'));
                    if (sale.discount_amount > 0) {
                        text += alignLR("Diskon", "-Rp " + parseInt(sale.discount_amount).toLocaleString('id-ID'));
                    }
                    text += alignLR("TOTAL", "Rp " + parseInt(sale.total_amount).toLocaleString('id-ID'));
                    text += alignLR("Bayar", "Rp " + parseInt(sale.amount_paid).toLocaleString('id-ID'));
                    if (sale.change_amount > 0) {
                        text += alignLR("Kembali", "Rp " + parseInt(sale.change_amount).toLocaleString('id-ID'));
                    }
                    text += "------------------------------------------------\n";
                    text += alignC("Terima Kasih Atas Kunjungan Anda!");
                    text += "\n\n\n\n\n\n";

                    const encoder = new TextEncoder();
                    const init = new Uint8Array([0x1B, 0x40]);
                    const content = encoder.encode(text);
                    const feed = new Uint8Array([0x1B, 0x64, 0x05]); 

                    const payload = new Uint8Array(init.length + content.length + feed.length);
                    payload.set(init, 0); 
                    payload.set(content, init.length); 
                    payload.set(feed, init.length + content.length);

                    for (let i = 0; i < payload.length; i += 40) {
                        await this.cachedCharacteristic.writeValue(payload.slice(i, i + 40));
                    }
                } catch (e) { 
                    alert("Koneksi Bluetooth Terputus. Mohon pilih perangkat kembali. " + e.message); 
                    this.cachedCharacteristic = null; 
                }
            }
        },
    };
}
</script>
@endpush