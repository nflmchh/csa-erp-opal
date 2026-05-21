@extends('layouts.app')
@section('title', 'Dashboard Toko')
@section('page-title', 'Performa Toko Hari Ini')

@section('content')
<div class="space-y-6">
    <div class="mb-4">
        <h3 class="text-lg font-bold text-gray-800 border-l-4 border-indigo-500 pl-3 uppercase tracking-tight">Ringkasan Finansial Toko Hari Ini</h3>
    </div>

    {{-- Pelanggan Jatuh Tempo Card --}}
    @if(isset($approachingDueSales) && $approachingDueSales->count() > 0)
    <div class="bg-white rounded-xl border border-red-200 shadow-sm overflow-hidden mb-6">
        <div class="bg-red-50/50 border-b border-red-100 px-6 py-4 flex items-center justify-between">
            <h4 class="text-sm font-bold text-red-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Peringatan Pembayaran Jatuh Tempo Pelanggan
            </h4>
            <span class="text-xs bg-red-100 text-red-800 px-2.5 py-0.5 rounded-full font-bold">Mendekati / Lewat Tempo</span>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($approachingDueSales as $sale)
            @php
                $due = \Carbon\Carbon::parse($sale->due_date);
                $isOverdue = $due->isPast();
                $daysDiff = now()->startOfDay()->diffInDays($due, false);
            @endphp
            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-red-50/10 transition-colors">
                <div class="flex items-start gap-3">
                    <div class="p-2 {{ $isOverdue ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600' }} rounded-lg shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h5 class="text-sm font-bold text-gray-900">{{ $sale->customer_name }}</h5>
                            <span class="text-xs text-gray-500 font-mono">({{ $sale->customer_phone ?: '-' }})</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            No. Transaksi: <span class="font-mono text-indigo-600 font-semibold">{{ $sale->sale_no }}</span> 
                            @if($sale->store)
                                · Toko: <span class="font-medium text-gray-700">{{ $sale->store->name }}</span>
                            @endif
                        </p>
                        <p class="text-xs font-semibold mt-1">
                            Status: 
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] uppercase font-bold {{ $sale->payment_status === 'tempo' ? 'bg-red-100 text-red-800' : ($sale->payment_status === 'dp' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800') }}">
                                {{ strtoupper($sale->payment_status) }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-6 justify-between sm:justify-end">
                    <div class="text-left sm:text-right">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Sisa Hutang</p>
                        <p class="text-sm font-extrabold text-red-600">Rp {{ number_format(max(0, $sale->total_amount - $sale->amount_paid), 0, ',', '.') }}</p>
                    </div>
                    <div class="text-left sm:text-right min-w-[120px]">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Jatuh Tempo</p>
                        <p class="text-xs font-bold {{ $isOverdue ? 'text-red-600' : 'text-amber-600' }}">
                            {{ $due->format('d/m/Y') }}
                        </p>
                        <p class="text-[10px] mt-0.5 {{ $isOverdue ? 'text-red-700 font-extrabold' : 'text-gray-500 font-semibold' }}">
                            @if($daysDiff < 0)
                                Lewat {{ abs($daysDiff) }} hari!
                            @elseif($daysDiff == 0)
                                Hari ini!
                            @else
                                {{ $daysDiff }} hari lagi
                            @endif
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        <a href="{{ route('store.customers.show', ['name' => $sale->customer_name, 'phone' => $sale->customer_phone]) }}" class="inline-flex items-center gap-1 bg-gray-900 text-white hover:bg-gray-800 text-xs px-3 py-1.5 rounded-lg font-medium shadow-sm transition">
                            <span>Detail</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Daily Financial Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card Pemasukan Hari Ini --}}
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm group relative overflow-hidden transition-all hover:shadow-md">
            <div class="absolute top-0 right-0 w-16 h-16 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1 relative z-10">Pemasukan Hari Ini</p>
            <div class="flex items-center justify-between relative z-10">
                <h4 class="text-xl font-black text-gray-900">Rp {{ number_format($todaySales, 0, ',', '.') }}</h4>
                <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
        </div>

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

        {{-- Card Keuntungan Hari Ini --}}
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm group relative overflow-hidden transition-all hover:shadow-md">
            <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-emerald-500 uppercase tracking-wider mb-1 relative z-10">Keuntungan Hari Ini</p>
            <div class="flex items-center justify-between relative z-10">
                <h4 class="text-xl font-black text-gray-900">Rp {{ number_format($todayProfit, 0, ',', '.') }}</h4>
                <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                </div>
            </div>
        </div>

        {{-- Card Penjualan Hari Ini (Trx) --}}
        <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm group relative overflow-hidden transition-all hover:shadow-md">
            <div class="absolute top-0 right-0 w-16 h-16 bg-amber-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-amber-500 uppercase tracking-wider mb-1 relative z-10">Penjualan Hari Ini</p>
            <div class="flex items-center justify-between relative z-10">
                <h4 class="text-xl font-black text-gray-900">{{ number_format($todayOrders) }} Trx</h4>
                <div class="p-2 bg-amber-100 rounded-lg text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between group">
            <div>
                <h3 class="text-gray-500 font-bold text-xs uppercase">Penerimaan Barang</h3>
                <p class="text-sm text-gray-400 mt-1">Cek apakah ada kiriman dari gudang yang belum diterima.</p>
            </div>
            <a href="{{ route('store.receiving.index') }}" class="p-3 bg-indigo-50 text-indigo-600 rounded-xl group-hover:bg-indigo-600 group-hover:text-white transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl flex items-center gap-4 text-amber-800">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="text-sm font-medium">
                Pastikan kasir selalu melakukan <strong>Close Session</strong> di akhir shift untuk menjaga akurasi laporan keuangan.
            </div>
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