@extends('layouts.app')
@section('title', 'Dashboard Gudang')
@section('page-title', 'Operasional Gudang')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    {{-- Card Pengeluaran Hari Ini --}}
    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm group relative overflow-hidden transition-all hover:shadow-md">
        <div class="absolute top-0 right-0 w-16 h-16 bg-red-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
        <p class="text-xs font-bold text-red-500 uppercase tracking-wider mb-1 relative z-10">Pengeluaran Hari Ini</p>
        <div class="flex items-center justify-between relative z-10">
            <h4 class="text-xl font-black text-gray-900">Rp {{ number_format($todayExpense, 0, ',', '.') }}</h4>
            <div class="p-2 bg-red-100 rounded-lg text-red-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
            </div>
        </div>
    </div>

    <div class="bg-white p-5 rounded-xl border border-red-100 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
            <h3 class="text-gray-500 font-bold text-xs uppercase tracking-wider">Stok Hampir Habis</h3>
            <span class="p-2 bg-red-50 text-red-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </span>
        </div>
        <p class="text-2xl font-black text-red-600 mt-3">{{ $lowStock ?? 0 }} <span class="text-xs font-medium text-gray-400 uppercase">Sku</span></p>
        <a href="{{ route('warehouse.stock.index') }}" class="text-[10px] text-red-500 font-bold mt-1 inline-block hover:underline">LIHAT DETAIL →</a>
    </div>

    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
            <h3 class="text-gray-500 font-bold text-xs uppercase tracking-wider">Total Unit Stok</h3>
            <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </span>
        </div>
        <p class="text-2xl font-black text-gray-800 mt-3">{{ number_format($totalWarehouseStock ?? 0) }} <span class="text-xs font-medium text-gray-400 uppercase">Pcs</span></p>
    </div>

    <div class="bg-indigo-600 p-5 rounded-xl shadow-lg text-white flex flex-col justify-between transition-all hover:shadow-indigo-200">
        <div>
            <h3 class="font-bold text-sm uppercase tracking-tight">Monitor Gudang</h3>
            <p class="text-indigo-100 text-[10px] mt-1">Pantau pengiriman real-time.</p>
        </div>
        <a href="{{ route('warehouse.monitor') }}" class="mt-3 bg-white text-indigo-600 text-center py-1.5 rounded-lg font-bold text-xs hover:bg-indigo-50 transition-colors uppercase">Buka Monitor</a>
    </div>
</div>
<div class="mt-8 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <div>
                <h3 class="font-bold text-gray-800">Produk Terbaru di Sistem</h3>
                <p class="text-xs text-gray-500 mt-0.5">Menampilkan 10 data produk terbaru.</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Lihat Semua Produk &rarr;</a>
        </div>
        <div class="overflow-x-auto">
           <table class="w-full text-sm text-left">
    <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
        <tr>
            <th class="px-6 py-3">Produk</th>
            <th class="px-6 py-3">Brand</th>
            <th class="px-6 py-3 text-right">Stok</th> {{-- Tambahkan Header Stok --}}
            <th class="px-6 py-3 text-right">Harga Jual</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @forelse($products as $p)
        @php
            // Hitung total stok dari semua varian (sudah terfilter di Controller)
            $totalQty = $p->variants->sum(fn($v) => $v->stocks->sum('qty'));
        @endphp
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-3">
                <div class="flex items-center gap-4">
                    {{-- Gambar Produk --}}
                    <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden border border-gray-100 shrink-0">
                        @php $primaryImg = $p->primaryImage(); @endphp
                        @if($primaryImg && $primaryImg->path)
                            <img src="{{ Storage::url($primaryImg->path) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-xl bg-gray-50 text-gray-300">👕</div>
                        @endif
                    </div>
                    <div>
                        <span class="font-bold text-gray-800">{{ $p->name }}</span><br>
                        <span class="text-[10px] text-gray-400 font-mono uppercase tracking-tight">{{ $p->model_code }}</span>
                    </div>
                </div>
            </td>
            <td class="px-6 py-3 text-xs text-gray-500">
                {{ $p->brand->name ?? '—' }}
            </td>
            {{-- Kolom Stok --}}
            <td class="px-6 py-3 text-right">
                <span class="inline-block px-2 py-1 rounded-lg font-bold text-sm {{ $totalQty <= 5 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                    {{ number_format($totalQty) }} <span class="text-[10px] font-normal uppercase">Pcs</span>
                </span>
            </td>
            <td class="px-6 py-3 text-right font-bold text-gray-900">
                Rp {{ number_format($p->sell_price, 0, ',', '.') }}
            </td>
        </tr>
        @empty
        {{-- ... empty state ... --}}
        @endforelse
    </tbody>
</table>
        </div>
    </div>
@endsection