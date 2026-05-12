@extends('layouts.app')
@section('title', 'Katalog Produk')
@section('page-title', 'Katalog Produk')
@section('breadcrumb', 'Produk / Katalog')

@section('content')
<div class="space-y-4">

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap gap-2 flex-1" id="filter-form">
            <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                placeholder="Cari nama / kode model / SKU barcode…"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-52 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <select name="brand_id" onchange="document.getElementById('filter-form').submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Brand</option>
                @foreach($brands as $b)
                <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>

            <select name="category_id" onchange="document.getElementById('filter-form').submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @if($cat->children->count())
                    @foreach($cat->children as $sub)
                    <option value="{{ $sub->id }}" {{ request('category_id') == $sub->id ? 'selected' : '' }}>&nbsp;&nbsp;↳ {{ $sub->name }}</option>
                    @endforeach
                @endif
                @endforeach
            </select>

            <select name="product_type_id" onchange="document.getElementById('filter-form').submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Jenis</option>
                @foreach($productTypes as $pt)
                <option value="{{ $pt->id }}" {{ request('product_type_id') == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>
                @endforeach
            </select>

            <select name="status" onchange="document.getElementById('filter-form').submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
            </select>

            <button type="submit" class="bg-gray-700 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
            <a href="{{ route('products.index') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg">Reset</a>
        </form>

        @can('create product')
        <a href="{{ route('products.create') }}"
            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg font-medium whitespace-nowrap">
            + Produk Baru
        </a>
        @endcan
    </div>

    {{-- Stats bar --}}
    <div class="text-sm text-gray-500">
        Menampilkan <span class="font-medium text-gray-700">{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}</span>
        dari <span class="font-medium text-gray-700">{{ $products->total() }}</span> produk
    </div>

    {{-- Grid --}}
    @if($products->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 py-20 text-center text-gray-400">
        <div class="text-4xl mb-3">📦</div>
        <p class="font-medium">Tidak ada produk ditemukan</p>
        <p class="text-sm mt-1">Coba ubah filter atau tambah produk baru</p>
    </div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
        @foreach($products as $product)
        @php
            $img = $product->primaryImage();
            $variantCount = $product->variants->count();
        @endphp
        <a href="{{ route('products.show', $product) }}"
            class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md hover:border-indigo-300 transition-all group">
            {{-- Image --}}
            <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                @if($img)
                <img src="{{ $img->url() }}" alt="{{ $product->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                <div class="text-gray-300 text-4xl">👕</div>
                @endif
            </div>
            {{-- Info --}}
            <div class="p-3">
                <div class="flex items-start justify-between gap-1 mb-1">
                    <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">
                        {{ $product->brand->code }}
                    </span>
                    @if(!$product->is_active)
                    <span class="text-xs text-red-500 bg-red-50 px-1.5 py-0.5 rounded">Nonaktif</span>
                    @endif
                </div>
                <p class="text-xs font-semibold text-gray-800 leading-tight mt-1 line-clamp-2">{{ $product->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $product->model_code }}</p>
                <div class="flex items-center justify-between mt-2">
                    <p class="text-xs font-bold text-gray-700">Rp {{ number_format($product->sell_price, 0, ',', '.') }}</p>
                    <span class="text-xs text-gray-400">{{ $variantCount }} varian</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div class="flex justify-center">
        {{ $products->links() }}
    </div>
    @endif
    @endif

</div>
{{-- SCRIPT GLOBAL SCANNER BARCODE --}}
    <script>
        let barcodeBuffer = '';
        let barcodeTimer = null;

        document.addEventListener('keydown', function(e) {
            // Abaikan jika user sedang mengetik manual di kolom input lain (kecuali kolom pencarian kita)
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) && e.target.id !== 'searchInput') {
                return;
            }

            // Jika tombol Enter ditekan (Scanner selalu mengirim Enter di akhir scan)
            if (e.key === 'Enter') {
                if (barcodeBuffer.length > 2) {
                    e.preventDefault(); // Cegah aksi default
                    
                    // Masukkan hasil scan ke kolom pencarian dan submit form otomatis
                    let searchInput = document.getElementById('searchInput');
                    let filterForm = document.getElementById('filter-form');
                    
                    if (searchInput && filterForm) {
                        searchInput.value = barcodeBuffer;
                        filterForm.submit();
                    }
                }
                barcodeBuffer = ''; // Reset buffer
            } 
            // Tangkap karakter yang diketik
            else if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                barcodeBuffer += e.key;
                clearTimeout(barcodeTimer);
                
                // Jika jeda antar huruf lebih dari 50ms, itu ketikan manusia. Reset buffer!
                barcodeTimer = setTimeout(() => { 
                    barcodeBuffer = ''; 
                }, 50);
            }
        });
    </script>
@endsection
