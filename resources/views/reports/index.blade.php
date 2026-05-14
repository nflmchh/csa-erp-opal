@extends('layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan')
@section('breadcrumb', 'Laporan')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

    <a href="{{ route('reports.expenses') }}"
        class="bg-white rounded-xl border border-gray-200 p-6 hover:border-indigo-300 hover:shadow-sm transition group">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-indigo-200 transition">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800 text-sm">Laporan Pengeluaran</h3>
                <p class="text-xs text-gray-500 mt-1">Riwayat transaksi pengeluaran per toko dan periode</p>
            </div>
        </div>
    </a>

    @unless(auth()->user()->hasRole('admin gudang'))
    <a href="{{ route('reports.sales') }}"
        class="bg-white rounded-xl border border-gray-200 p-6 hover:border-indigo-300 hover:shadow-sm transition group">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-indigo-200 transition">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800 text-sm">Laporan Penjualan</h3>
                <p class="text-xs text-gray-500 mt-1">Riwayat transaksi penjualan per toko dan periode</p>
            </div>
        </div>
    </a>
    @endunless

    <a href="{{ route('reports.stock') }}"
        class="bg-white rounded-xl border border-gray-200 p-6 hover:border-indigo-300 hover:shadow-sm transition group">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-green-200 transition">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800 text-sm">Laporan Stok</h3>
                <p class="text-xs text-gray-500 mt-1">Posisi stok saat ini per gudang dan toko</p>
            </div>
        </div>
    </a>

    <a href="{{ route('reports.shipment') }}"
        class="bg-white rounded-xl border border-gray-200 p-6 hover:border-indigo-300 hover:shadow-sm transition group">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-orange-200 transition">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800 text-sm">Laporan Pengiriman</h3>
                <p class="text-xs text-gray-500 mt-1">Riwayat pengiriman dari gudang ke toko</p>
            </div>
        </div>
    </a>

    @unless(auth()->user()->hasRole('kepala toko'))
    <a href="{{ route('reports.inbound') }}"
        class="bg-white rounded-xl border border-gray-200 p-6 hover:border-indigo-300 hover:shadow-sm transition group">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-blue-200 transition">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800 text-sm">Laporan Barang Masuk</h3>
                <p class="text-xs text-gray-500 mt-1">Riwayat penerimaan barang di gudang</p>
            </div>
        </div>
    </a>
    @endunless

    @unless(auth()->user()->hasRole('admin gudang'))
    <a href="{{ route('reports.transfer') }}"
        class="bg-white rounded-xl border border-gray-200 p-6 hover:border-indigo-300 hover:shadow-sm transition group">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-purple-200 transition">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800 text-sm">Laporan Transfer Toko</h3>
                <p class="text-xs text-gray-500 mt-1">Riwayat transfer barang antar toko</p>
            </div>
        </div>
    </a>
    @endunless

    @can('view finance')
    <a href="{{ route('finance.index') }}"
        class="bg-white rounded-xl border border-gray-200 p-6 hover:border-indigo-300 hover:shadow-sm transition group">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center shrink-0 group-hover:bg-yellow-200 transition">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800 text-sm">Dashboard Keuangan</h3>
                <p class="text-xs text-gray-500 mt-1">Ringkasan penjualan dan nilai stok</p>
            </div>
        </div>
    </a>
    @endcan

</div>
@endsection
