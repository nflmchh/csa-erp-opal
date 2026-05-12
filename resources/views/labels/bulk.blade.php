<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

@php
    // Logika Pintar untuk Judul Dokumen (Nama File PDF)
    $uniqueSkus = collect($items)->pluck('variant.sku')->unique();
    // Jika hanya 1 jenis SKU, gunakan nama SKU tersebut. Jika banyak, gunakan nama "Label_Massal_Tgl"
    $pdfName = $uniqueSkus->count() === 1 ? $uniqueSkus->first() : 'Label_Massal_' . date('Ymd_His');
@endphp
<title>{{ $pdfName }}</title>

<!-- Menggunakan Tailwind & Alpine JS -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Menggunakan Tailwind & Alpine JS -->
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js"></script>
<style>
    body { background-color: #f1f5f9; margin: 0; }
    
    /* Custom Scrollbar untuk Sidebar */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

    /* Mencegah barcode ditarik-tarik */
    .barcode-svg { max-width: 100%; height: 100%; object-fit: contain; }

    /* CSS Khusus Saat Cetak (Print) */
    @media print {
        body { background: transparent; }
        .no-print { display: none !important; }
        .print-area { padding: 0 !important; overflow: visible !important; }
        .sheet { 
            box-shadow: none !important; 
            margin: 0 !important; 
            border: none !important; 
            page-break-after: always;
            page-break-inside: avoid;
        }
        .label-box { border: none !important; background: transparent !important; }
        .label-empty { display: none !important; } /* Sembunyikan tanda silang saat print */
    }
</style>
</head>

@php
    // Menyiapkan data flat items dari backend ke Javascript
    $flatItems = [];
    foreach($items as $item) {
        for($i = 0; $i < $item['copies']; $i++) {
            $flatItems[] = [
                'sku' => $item['variant']->sku,
                'name' => $item['variant']->product->name,
                'size' => $item['variant']->size->name,
                'price' => 'Rp' . number_format($item['variant']->sellPrice(), 0, ',', '.')
            ];
        }
    }
@endphp

<!-- Inisialisasi Alpine.js -->
<body x-data="labelStudio()" x-init="initApp()" class="flex h-screen overflow-hidden">

    <!-- CSS DINAMIS UNTUK UKURAN KERTAS PRINTER (Disuntikkan otomatis oleh Alpine) -->
    <style x-text="`@media print { @page { size: ${c.paperW}mm ${c.paperH}mm; margin: 0; } }`"></style>

    {{-- =======================================================
         1. SIDEBAR PENGATURAN (KIRI)
         ======================================================= --}}
    <div class="no-print w-80 bg-white shadow-2xl flex flex-col z-20 h-full border-r border-gray-200">
        
        <!-- Header Sidebar -->
        <div class="p-5 bg-indigo-900 text-white shrink-0">
            <h1 class="text-lg font-black tracking-wide flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Label Studio
            </h1>
            <p class="text-xs text-indigo-300 mt-1">Konfigurasi tata letak cetak</p>
        </div>

        <!-- Form Pengaturan (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-5 space-y-6 custom-scrollbar text-sm">
            
            <!-- Ukuran Kertas -->
            <div class="space-y-3">
                <h3 class="font-bold text-gray-800 border-b pb-1">1. Ukuran Kertas Dasar</h3>
                <div class="flex gap-3">
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Lebar (mm)</span>
                        <input type="number" x-model.number="c.paperW" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Tinggi (mm)</span>
                        <input type="number" x-model.number="c.paperH" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                </div>
                <div class="flex gap-2 text-xs">
                    <button @click="c.paperW=210; c.paperH=297" class="bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded border">Set A4</button>
                    <button @click="c.paperW=100; c.paperH=150" class="bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded border">Thermal A6</button>
                </div>
            </div>

            <!-- Dimensi & Bentuk Label -->
            <div class="space-y-3">
                <h3 class="font-bold text-gray-800 border-b pb-1">2. Ukuran per Label</h3>
                <div class="flex gap-3 mb-2">
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Lebar (mm)</span>
                        <input type="number" x-model.number="c.labelW" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Tinggi (mm)</span>
                        <input type="number" x-model.number="c.labelH" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                </div>
                <label class="block w-full">
                    <span class="text-[10px] uppercase text-gray-500 font-bold">Lengkung Sudut / Rounded (mm)</span>
                    <input type="number" x-model.number="c.rounded" step="1" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                </label>
            </div>

            <!-- Grid & Jarak -->
            <div class="space-y-3">
                <h3 class="font-bold text-gray-800 border-b pb-1">3. Grid & Jarak Antar Label</h3>
                <div class="flex gap-3 mb-2">
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Jml Kolom ↔️</span>
                        <input type="number" x-model.number="c.col" min="1" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Jml Baris ↕️</span>
                        <input type="number" x-model.number="c.row" min="1" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                </div>
                <div class="flex gap-3">
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Gap Kanan ↔️</span>
                        <input type="number" x-model.number="c.gapX" step="0.5" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Gap Bawah ↕️</span>
                        <input type="number" x-model.number="c.gapY" step="0.5" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                </div>
            </div>

            <!-- Margin Kertas -->
            <div class="space-y-3">
                <h3 class="font-bold text-gray-800 border-b pb-1">4. Margin Kertas</h3>
                <div class="flex gap-3">
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Margin Kiri</span>
                        <input type="number" x-model.number="c.marginLeft" step="0.5" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Margin Atas</span>
                        <input type="number" x-model.number="c.marginTop" step="0.5" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                </div>
            </div>

        </div>

        <!-- Tombol Aksi (Bawah Sidebar) -->
        <div class="p-5 bg-gray-50 border-t border-gray-200 shrink-0 space-y-3">
            <button onclick="window.print()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-3 rounded-xl shadow-lg transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                CETAK LABEL
            </button>
            <a href="{{ route('labels.picker') }}" class="block w-full text-center bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold py-2.5 rounded-xl transition-colors">
                Batal / Kembali
            </a>
        </div>
    </div>


    {{-- =======================================================
         2. AREA PREVIEW KERTAS (KANAN)
         ======================================================= --}}
    <div class="flex-1 bg-slate-200 overflow-y-auto print-area relative">
        
        <!-- Bar Informasi Petunjuk -->
        <div class="no-print sticky top-0 bg-yellow-50 text-yellow-800 p-3 text-center text-sm font-semibold shadow-sm z-10 border-b border-yellow-200">
            💡 Tips: Klik label di atas kertas jika Anda ingin melewatinya (Misal: Stiker sudah terpakai/sobek). 
            Produk akan otomatis bergeser ke stiker berikutnya!
        </div>

        <div class="p-8 pb-24 flex flex-col items-center gap-8 print-area">
            
            <!-- Looping Halaman Kertas (Pages) -->
            <template x-for="(page, pIdx) in calculatedPages" :key="pIdx">
                
                <!-- Lembaran Kertas -->
                <div class="sheet bg-white shadow-xl relative"
                     :style="`width: ${c.paperW}mm; height: ${c.paperH}mm; padding-top: ${c.marginTop}mm; padding-left: ${c.marginLeft}mm;`">
                    
                    <!-- Grid Label di atas kertas -->
                    <div class="grid" :style="`grid-template-columns: repeat(${c.col}, ${c.labelW}mm); gap: ${c.gapY}mm ${c.gapX}mm;`">
                        
                        <!-- Looping Slot dalam Halaman -->
                        <template x-for="slot in page.slots" :key="slot.absIdx">
                            
                            <!-- Kotak Label (Stiker) -->
                            <div @click="toggleSkip(slot.absIdx)"
                                 class="label-box relative border overflow-hidden transition-all hover:ring-2 hover:ring-indigo-400 cursor-pointer flex flex-col"
                                 :style="`width: ${c.labelW}mm; height: ${c.labelH}mm; border-radius: ${c.rounded}mm;`"
                                 :class="slot.skipped ? 'bg-red-50 border-red-300' : 'bg-white border-gray-300 border-dashed'">
                                
                                {{-- Jika Slot Aktif & Ada Data Barang --}}
                                <template x-if="!slot.skipped && slot.item">
                                    <div class="flex flex-col h-full w-full justify-between" style="padding: 1mm">
                                        <!-- Header Text -->
                                        <div class="flex justify-between w-full font-bold text-black" style="font-size: 6.5pt; line-height: 1.1;">
                                            <div class="truncate max-w-[65%]" x-text="`${slot.item.name} (${slot.item.size})`"></div>
                                            <div class="text-right shrink-0" x-text="slot.item.price"></div>
                                        </div>
                                        
                                        <!-- Barcode Container -->
                                        <div class="flex-1 w-full flex items-center justify-center overflow-hidden">
                                            <!-- Alpine me-render barcode dengan init khusus -->
                                            <svg class="barcode-svg" x-init="renderBarcode($el, slot.item.sku)"></svg>
                                        </div>
                                    </div>
                                </template>

                                {{-- Jika Slot Di-Skip / Dilewati (Tampilkan X merah) --}}
                                <template x-if="slot.skipped">
                                    <div class="label-empty absolute inset-0 flex items-center justify-center text-red-300 bg-[repeating-linear-gradient(45deg,transparent,transparent_10px,#fee2e2_10px,#fee2e2_20px)]">
                                        <svg class="w-1/3 h-1/3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </div>
                                </template>

                            </div>
                        </template>

                    </div>
                </div>
            </template>
            
        </div>
    </div>

<script>
// Data barang yang mau diprint dari PHP
const rawItems = {!! json_encode($flatItems) !!};

function labelStudio() {
    return {
        // Konfigurasi Default (Di-setting ke T&J 129 di kertas A4)
        c: {
            paperW: 210, paperH: 297,   // Ukuran Kertas
            labelW: 58, labelH: 17,     // Ukuran Label
            rounded: 1,                 // Rounded border label
            col: 3, row: 12,            // Grid Kolom x Baris
            gapX: 3, gapY: 3,           // Jarak antar label
            marginLeft: 14, marginTop: 15 // Margin luar kertas
        },
        
        items: rawItems,
        skippedSlots: [], // Menyimpan ID slot kertas mana saja yang di-skip (tidak diprint)

        initApp() {
            // Jika ada event listener yang dibutuhkan, bisa ditaruh disini
        },

        // Toggle label skip saat diklik
        toggleSkip(absIdx) {
            const index = this.skippedSlots.indexOf(absIdx);
            if (index > -1) {
                this.skippedSlots.splice(index, 1); // Batal skip
            } else {
                this.skippedSlots.push(absIdx); // Tandai skip
            }
        },

        // Fungsi canggih untuk menyusun barang ke kertas (Termasuk menghitung yang di-skip)
        get calculatedPages() {
            let pages = [];
            let itemsQueue = [...this.items]; // Duplikat array barang
            let absIdx = 0;
            let slotsPerPage = this.c.col * this.c.row;

            // Terus membuat halaman selama masih ada barang yang antri, ATAU kertas belum penuh (visualisasi 1 lembar utuh)
            while (itemsQueue.length > 0 || (absIdx % slotsPerPage !== 0 && pages.length > 0) || (pages.length === 0)) {
                
                let pageIdx = Math.floor(absIdx / slotsPerPage);
                
                // Buat kertas baru jika belum ada
                if (!pages[pageIdx]) {
                    pages.push({ slots: [] });
                }

                let isSkipped = this.skippedSlots.includes(absIdx);
                let currentItem = null;

                // Jika slot ini TIDAK diskip, dan masih ada barang, tarik 1 barang dari antrian
                if (!isSkipped && itemsQueue.length > 0) {
                    currentItem = itemsQueue.shift();
                }

                pages[pageIdx].slots.push({
                    absIdx: absIdx,
                    skipped: isSkipped,
                    item: currentItem
                });

                absIdx++;
            }

            return pages;
        },

        // Render Barcode
        renderBarcode(el, sku) {
            // Gunakan setTimeout agar SVG sudah di-render DOM sebelum JSBarcode memanggilnya
            setTimeout(() => {
                if(sku) {
                    JsBarcode(el, sku, {
                        format: 'CODE128',
                        width: 1.4,
                        height: 25,
                        displayValue: true, 
                        fontSize: 10,
                        textMargin: 1,
                        margin: 2
                    });
                }
            }, 10);
        }
    }
}
</script>
</body>
</html>