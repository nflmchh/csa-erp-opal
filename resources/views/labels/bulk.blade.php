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

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            background-color: #f1f5f9;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        /* Custom Scrollbar untuk Sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 10px;
        }

        /* Mencegah barcode ditarik-tarik */
        .barcode-svg {
            max-width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* CSS Khusus Saat Cetak (Print) */
        @media print {
            body {
                background: transparent;
            }

            .no-print {
                display: none !important;
            }

            .print-area {
                padding: 0 !important;
                overflow: visible !important;
            }

            .sheet {
                box-shadow: none !important;
                margin: 0 !important;
                border: none !important;
                page-break-after: always;
                page-break-inside: avoid;
            }

            .label-box {
                border: none !important;
                background: transparent !important;
                margin: 0 !important;
            }

            .label-empty {
                display: none !important;
            }

            /* Sembunyikan tanda silang saat print */
        }
    </style>
</head>

@php
    // Menyiapkan data produk unik ke Javascript agar bisa diatur jumlahnya di sini
    $uniqueProducts = [];
    foreach ($items as $item) {
        $uniqueProducts[] = [
            'sku' => $item['variant']?->sku,
            'name' => $item['variant']?->product?->name,
            'size' => $item['variant']?->size?->name,
            'color' => optional($item['variant']->color)->name ?? '-',
            'price' => $item['variant']->sellPrice(),
            'copies' => $item['copies']
        ];
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
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
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
                        <input type="number" x-model.number="c.paperW"
                            class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Tinggi (mm)</span>
                        <input type="number" x-model.number="c.paperH"
                            class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                </div>
                <div class="flex gap-2 text-xs">
                    <button @click="c.paperW=210; c.paperH=297"
                        class="bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded border">Set A4</button>
                    <button @click="c.paperW=100; c.paperH=150"
                        class="bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded border">Thermal A6</button>
                    <button @click="resetConfig()"
                        class="bg-red-50 text-red-600 hover:bg-red-100 px-2 py-1 rounded border border-red-200 ml-auto font-bold">Reset
                        Default</button>
                </div>
            </div>

            <!-- Dimensi & Bentuk Label -->
            <div class="space-y-3">
                <h3 class="font-bold text-gray-800 border-b pb-1">2. Ukuran per Label</h3>
                <div class="flex gap-3 mb-2">
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Lebar (mm)</span>
                        <input type="number" x-model.number="c.labelW"
                            class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Tinggi (mm)</span>
                        <input type="number" x-model.number="c.labelH"
                            class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                </div>
                <label class="block w-full">
                    <span class="text-[10px] uppercase text-gray-500 font-bold">Lengkung Sudut / Rounded (mm)</span>
                    <input type="number" x-model.number="c.rounded" step="1"
                        class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                </label>
            </div>

            <!-- Grid & Jarak -->
            <div class="space-y-3">
                <h3 class="font-bold text-gray-800 border-b pb-1">3. Grid & Jarak Antar Label</h3>
                <div class="flex gap-3 mb-2">
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Jml Kolom ↔️</span>
                        <input type="number" x-model.number="c.col" min="1"
                            class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Jml Baris ↕️</span>
                        <input type="number" x-model.number="c.row" min="1"
                            class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                </div>
                <div class="flex gap-3">
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Gap Kanan ↔️</span>
                        <input type="number" x-model.number="c.gapX" step="0.5"
                            class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Gap Bawah ↕️</span>
                        <input type="number" x-model.number="c.gapY" step="0.5"
                            class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                </div>
            </div>

            <!-- Margin Kertas -->
            <div class="space-y-3">
                <h3 class="font-bold text-gray-800 border-b pb-1">4. Margin Kertas</h3>
                <div class="flex gap-3">
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Margin Kiri</span>
                        <input type="number" x-model.number="c.marginLeft" step="0.5"
                            class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                    <label class="block flex-1">
                        <span class="text-[10px] uppercase text-gray-500 font-bold">Margin Atas</span>
                        <input type="number" x-model.number="c.marginTop" step="0.5"
                            class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-indigo-500">
                    </label>
                </div>
            </div>

            <!-- Konten Label -->
            <div class="space-y-3">
                <h3 class="font-bold text-gray-800 border-b pb-1">5. Konten Label</h3>
                <label class="flex items-center gap-2 cursor-pointer bg-indigo-50 p-2 rounded border border-indigo-100">
                    <input type="checkbox" x-model="c.showPrice" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 rounded">
                    <span class="text-sm font-bold text-indigo-900">Tampilkan Harga</span>
                </label>
            </div>

            <!-- Daftar Barang & Jumlah (BARU) -->
            <div class="space-y-3">
                <h3 class="font-bold text-gray-800 border-b pb-1">6. Jumlah Label per Produk</h3>
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1 custom-scrollbar">
                    <template x-for="(item, index) in items" :key="item.sku">
                        <div class="bg-gray-50 p-2 rounded border border-gray-200">
                            <div class="flex justify-between items-start gap-2 mb-1">
                                <div class="flex-1">
                                    <p class="text-[10px] font-bold text-indigo-600 font-mono" x-text="item.sku"></p>
                                    <p class="text-[11px] font-bold text-gray-800 truncate" x-text="item.name"></p>
                                </div>
                                <input type="number" x-model.number="item.copies" min="0"
                                    class="w-12 text-center border border-gray-300 rounded text-xs py-1 focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <p class="text-[9px] text-gray-400" x-text="`Ukuran: ${item.size}`"></p>
                        </div>
                    </template>
                </div>
            </div>

        </div>

        <!-- Tombol Aksi (Bawah Sidebar) -->
        <div class="p-5 bg-gray-50 border-t border-gray-200 shrink-0 space-y-3">
            <button onclick="window.print()"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-3 rounded-xl shadow-lg transition-colors flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                CETAK LABEL
            </button>
            <a href="{{ route('labels.picker') }}"
                class="block w-full text-center bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold py-2.5 rounded-xl transition-colors">
                Batal / Kembali
            </a>
        </div>
    </div>


    {{-- =======================================================
    2. AREA PREVIEW KERTAS (KANAN)
    ======================================================= --}}
    <div class="flex-1 bg-slate-200 overflow-y-auto print-area relative">

        <!-- Bar Informasi Petunjuk -->
        <div
            class="no-print sticky top-0 bg-yellow-50 text-yellow-800 p-3 text-center text-sm font-semibold shadow-sm z-10 border-b border-yellow-200">
            💡 Tips: Klik label di atas kertas jika Anda ingin melewatinya (Misal: Stiker sudah terpakai/sobek).
            Produk akan otomatis bergeser ke stiker berikutnya!
        </div>

        <div class="p-8 pb-24 flex flex-col items-center gap-8 print-area">

            <!-- Looping Halaman Kertas (Pages) -->
            <template x-for="(page, pIdx) in calculatedPages" :key="pIdx">

                <!-- Lembaran Kertas -->
                <div class="sheet bg-white shadow-xl relative flex justify-center"
                    :style="`width: ${c.paperW}mm; height: ${c.paperH}mm; padding-top: ${c.marginTop}mm; font-family: sans-serif; text-align: center;`">

                    <!-- Grid Label di atas kertas -->
                    <div class="grid justify-center"
                        :style="`grid-template-columns: repeat(${c.col}, ${c.labelW}mm); gap: ${c.gapY}mm ${c.gapX}mm;`">

                        <!-- Looping Slot dalam Halaman -->
                        <template x-for="slot in page.slots" :key="slot.absIdx">

                            <!-- Kotak Label (Stiker) -->
                            <div @click="toggleSkip(slot.absIdx)"
                                class="label-box relative border overflow-hidden transition-all hover:ring-2 hover:ring-indigo-400 cursor-pointer flex flex-col"
                                :style="`width: ${c.labelW}mm; height: ${c.labelH}mm; border-radius: ${c.rounded}mm;`"
                                :class="slot.skipped ? 'bg-red-50 border-red-300' : 'bg-white border-gray-300 border-dashed'">

                                {{-- Jika Slot Aktif & Ada Data Barang --}}
                                <template x-if="!slot.skipped && slot.item">
                                        <div class="flex h-full w-full items-center p-1 overflow-hidden">
                                            <!-- Sisi Kiri (QR Code + SKU jika ada harga) -->
                                            <div class="shrink-0 flex flex-col items-center justify-center" style="width: 35%;">
                                                <div x-init="renderQRCode($el, slot.item.sku)"></div>
                                                <template x-if="c.showPrice">
                                                    <div class="font-mono font-bold text-gray-600 mt-0.5 text-center leading-none"
                                                        style="font-size: 6pt;" x-text="slot.item.sku">
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Sisi Kanan (Informasi Produk + Harga jika ada) -->
                                            <div class="flex-1 flex flex-col justify-center pl-3 overflow-hidden text-left">
                                                <div class="font-black text-black leading-tight mb-1" style="font-size: 11pt;"
                                                    x-text="`${slot.item.name}`">
                                                </div>
                                                <div class="font-bold text-gray-700 leading-tight mb-1"
                                                    style="font-size: 8.5pt;" x-text="`${slot.item.color} / ${slot.item.size}`">
                                                </div>

                                                {{-- Harga (Jika diaktifkan) --}}
                                                <template x-if="c.showPrice">
                                                    <div class="font-black text-indigo-700 mt-1 pt-1 border-t border-dashed border-gray-400"
                                                        style="font-size: 12pt;" x-text="formatCurrency(slot.item.price)">
                                                    </div>
                                                </template>

                                                {{-- SKU (Jika harga TIDAK diaktifkan, SKU di sini) --}}
                                                <template x-if="!c.showPrice">
                                                    <div class="font-mono font-bold text-indigo-700 border-t border-gray-300 pt-1"
                                                        style="font-size: 9pt;" x-text="slot.item.sku">
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                </template>

                                {{-- Jika Slot Di-Skip / Dilewati (Tampilkan X merah) --}}
                                <template x-if="slot.skipped">
                                    <div
                                        class="label-empty absolute inset-0 flex items-center justify-center text-red-300 bg-[repeating-linear-gradient(45deg,transparent,transparent_10px,#fee2e2_10px,#fee2e2_20px)]">
                                        <svg class="w-1/3 h-1/3 opacity-70" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
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
        // Data barang yang mau diprint dari PHP (Unik per SKU)
        const rawItems = {!! json_encode($uniqueProducts) !!};

        function labelStudio() {
            return {
                // Konfigurasi Default (Di-setting ke T&J 129 di kertas A4)
                c: {
                    paperW: 210, paperH: 150,    // Ukuran Kertas
                    labelW: 62, labelH: 32,      // Ukuran Label
                    rounded: 1,                  // Rounded border label
                    col: 3, row: 4,              // Grid Kolom x Baris
                    gapX: 2, gapY: 1,            // Jarak antar label
                    marginLeft: 4, marginTop: 7, // Margin luar kertas
                    showPrice: false             // Tampilkan harga atau tidak
                },

                items: rawItems,
                skippedSlots: [], // Menyimpan ID slot kertas mana saja yang di-skip (tidak diprint)

                // Nilai default untuk reset
                defaults: {
                    paperW: 210, paperH: 150,
                    labelW: 62, labelH: 32,
                    rounded: 1,
                    col: 3, row: 4,
                    gapX: 2, gapY: 1,
                    marginLeft: 4, marginTop: 7,
                    showPrice: false
                },

                formatCurrency(val) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(val);
                },

                // Getter untuk meratakan (flatten) items berdasarkan jumlah copies
                get flatItems() {
                    let flat = [];
                    this.items.forEach(item => {
                        for (let i = 0; i < item.copies; i++) {
                            flat.push(item);
                        }
                    });
                    return flat;
                },

                initApp() {
                    // Memuat konfigurasi dari localStorage jika ada
                    const savedConfig = localStorage.getItem('label_studio_config');
                    if (savedConfig) {
                        try {
                            const parsed = JSON.parse(savedConfig);
                            // Gunakan Object.assign agar reactivity Alpine tetap terjaga dengan baik
                            Object.assign(this.c, parsed);
                        } catch (e) {
                            console.error("Gagal memuat konfigurasi label:", e);
                        }
                    }

                    // Simpan setiap kali ada perubahan pada objek 'c'
                    this.$watch('c', (value) => {
                        localStorage.setItem('label_studio_config', JSON.stringify(value));
                    }, { deep: true });
                },

                resetConfig() {
                    if (confirm('Reset semua pengaturan ke default?')) {
                        this.c = JSON.parse(JSON.stringify(this.defaults));
                        localStorage.removeItem('label_studio_config');
                    }
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
                    let itemsQueue = [...this.flatItems]; // Gunakan flatItems hasil perataan
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

                // Render QR Code
                renderQRCode(el, sku) {
                    setTimeout(() => {
                        if (sku && typeof QRCode !== 'undefined') {
                            el.innerHTML = "";
                            new QRCode(el, {
                                text: sku,
                                width: 80,
                                height: 80,
                                colorDark: "#000000",
                                colorLight: "#ffffff",
                                correctLevel: QRCode.CorrectLevel.M
                            });

                            // Opsional: Pastikan canvas/img di dalam QRCode responsive
                            const img = el.querySelector('img');
                            if (img) {
                                img.style.maxWidth = '100%';
                                img.style.height = 'auto';
                            }
                            const canvas = el.querySelector('canvas');
                            if (canvas) {
                                canvas.style.maxWidth = '100%';
                                canvas.style.height = 'auto';
                            }
                        }
                    }, 10);
                }
            }
        }
    </script>

    </script>
</body>

</html>