<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk — {{ date('d F Y') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js"></script>
    <style>
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
            @page { margin: 1cm; size: A4 landscape; }
        }
        .product-card {
            break-inside: avoid;
        }
        .barcode-svg {
            width: 100%;
            height: auto;
            max-height: 20px;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900">

    {{-- Toolbar Print --}}
    <div class="no-print sticky top-0 bg-indigo-900 text-white p-4 shadow-lg flex justify-between items-center z-50">
        <div>
            <h1 class="font-bold text-lg">Pratinjau Katalog Produk (Landscape A4)</h1>
            <p class="text-xs text-indigo-200">Total: {{ $products->count() }} Produk</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 px-6 py-2 rounded-lg font-bold text-sm shadow-md transition-all">
                Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg text-sm transition-all border border-white/20">
                Tutup
            </button>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-[297mm] mx-auto bg-white p-8 my-8 shadow-2xl min-h-screen">
        
        {{-- Header Katalog --}}
        <div class="flex justify-between items-end border-b-4 border-indigo-900 pb-4 mb-8">
            <div>
                <h2 class="text-3xl font-black text-indigo-900 uppercase tracking-tighter">Product Catalog</h2>
                <p class="text-sm text-gray-500 font-medium italic">Gallery & SKU Guide</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-bold text-gray-400 uppercase">Dicetak pada</p>
                <p class="text-sm font-black text-gray-800">{{ date('d M Y, H:i') }}</p>
            </div>
        </div>

        {{-- Grid Produk (3 Kolom untuk Landscape) --}}
        <div class="grid grid-cols-3 gap-x-4 gap-y-8">
            @foreach($products as $product)
                <div class="product-card border border-gray-100 rounded-2xl overflow-hidden shadow-sm flex flex-col bg-slate-50/30">
                    {{-- Product Image --}}
                    <div class="aspect-video bg-white flex items-center justify-center overflow-hidden border-b border-gray-100">
                        @php $img = $product->primaryImage(); @endphp
                        @if($img)
                            <img src="{{ $img->url() }}" alt="{{ $product->name }}" class="w-full h-full object-contain p-2">
                        @else
                            <div class="text-gray-200 text-6xl">👕</div>
                        @endif
                    </div>

                    {{-- Product Details --}}
                    <div class="p-3 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-[9px] font-black bg-indigo-900 text-white px-2 py-0.5 rounded-full uppercase">
                                {{ $product?->brand?->name }}
                            </span>
                            <span class="text-xs font-bold text-indigo-600">
                                Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                            </span>
                        </div>

                        <h3 class="font-bold text-xs text-gray-800 leading-tight mb-0.5 line-clamp-1">
                            {{ $product->name }}
                        </h3>
                        <p class="text-[9px] text-gray-400 font-mono mb-2">{{ $product->model_code }}</p>

                        {{-- Barcodes for ALL Variants --}}
                        <div class="mt-auto space-y-1">
                            <p class="text-[8px] font-bold text-gray-500 border-b border-gray-100 pb-0.5 uppercase tracking-widest">Variants</p>
                            <div class="grid grid-cols-1 gap-1 mt-1">
                                @foreach($product->variants as $variant)
                                    <div class="flex items-center gap-2 bg-white p-1 rounded border border-gray-50">
                                        <div class="shrink-0 text-center border-r border-gray-100 pr-1.5 min-w-[35px]">
                                            <p class="text-[8px] font-black text-gray-800 leading-none">{{ $variant?->size?->name }}</p>
                                            <p class="text-[7px] text-gray-400 uppercase mt-0.5 truncate max-w-[30px]">{{ $variant?->color?->name }}</p>
                                        </div>
                                        <div class="flex-1 overflow-hidden flex flex-col items-center">
                                            <svg class="barcode-svg" data-sku="{{ $variant?->sku }}"></svg>
                                            <p class="text-[7px] font-mono font-semibold text-gray-500 leading-none mt-0.5">{{ $variant?->sku }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="mt-12 pt-6 border-t border-gray-200 text-center">
            <p class="text-[10px] text-gray-400 uppercase tracking-[0.2em] font-bold">Generated by CSA ERP System</p>
        </div>
    </div>

    <script>
        // Render all barcodes
        document.querySelectorAll('.barcode-svg').forEach(function(el) {
            const sku = el.getAttribute('data-sku');
            if(sku) {
                JsBarcode(el, sku, {
                    format: 'CODE128',
                    width: 1.5,
                    height: 25,
                    displayValue: false,
                    margin: 0
                });
            }
        });
    </script>
</body>
</html>
