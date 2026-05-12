@extends('layouts.app')
@section('title', 'Dashboard Toko')
@section('page-title', 'Performa Toko Hari Ini')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 p-6 rounded-2xl shadow-lg text-white">
            <p class="text-indigo-100 font-bold text-xs uppercase tracking-wider">Penjualan Hari Ini</p>
            <h2 class="text-3xl font-black mt-2">Rp {{ number_format($todaySales ?? 0) }}</h2>
            <p class="mt-4 text-xs bg-white/20 inline-block px-3 py-1 rounded-full">{{ $todayOrders ?? 0 }} Transaksi Berhasil</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-gray-500 font-bold text-xs uppercase">Penerimaan Barang</h3>
                <p class="text-sm text-gray-400 mt-1">Cek apakah ada kiriman dari gudang yang belum diterima.</p>
            </div>
            <a href="{{ route('store.receiving.index') }}" class="mt-4 flex items-center justify-between text-indigo-600 font-bold hover:translate-x-2 transition-transform">
                Cek Pengiriman Barang →
            </a>
        </div>
    </div>
    
    <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl flex items-center gap-4 text-amber-800">
        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="text-sm">
            Pastikan kasir selalu melakukan <strong>Close Session</strong> di akhir shift untuk menjaga akurasi laporan keuangan.
        </div>
    </div>
</div>
<div class="mt-8 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <div>
                <h3 class="font-bold text-gray-800">Produk Tersedia di Toko Anda</h3>
                <p class="text-xs text-gray-500 mt-0.5">Menampilkan produk dengan stok aktif di lokasi Anda.</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Buka Katalog Lengkap &rarr;</a>
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