@extends('layouts.app')
@section('title', 'Dashboard Keuangan')
@section('page-title', 'Dashboard Keuangan')
@section('breadcrumb', 'Keuangan')

@section('content')
<div class="space-y-6">

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @if($isGlobal || !empty($storeIds))
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Penjualan Hari Ini</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $totalOrders }} transaksi</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Penjualan Bulan Ini</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">Rp {{ number_format($monthSales, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ now()->format('F Y') }}</p>
        </div>
        @endif
        
        <div class="bg-white rounded-xl border border-gray-200 p-5 @if($isGlobal || (!empty($storeIds) && !empty($warehouseIds))) sm:col-span-2 lg:col-span-1 @endif">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Nilai Stok Total</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">Rp {{ number_format($storeStockValue + $warehouseStockValue, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">
                @if($isGlobal) Toko + Gudang
                @elseif(!empty($storeIds) && empty($warehouseIds)) Toko
                @elseif(empty($storeIds) && !empty($warehouseIds)) Gudang
                @else Toko + Gudang @endif
            </p>
        </div>
    </div>

    {{-- Stock value breakdown --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if($isGlobal || !empty($storeIds))
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-gray-700">Nilai Stok Toko</p>
                <a href="{{ route('finance.stock-value', ['location_type' => 'store']) }}"
                    class="text-xs text-indigo-600 hover:underline">Lihat Detail →</a>
            </div>
            <p class="text-3xl font-bold text-indigo-600">Rp {{ number_format($storeStockValue, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Berdasarkan harga jual</p>
        </div>
        @endif
        
        @if($isGlobal || !empty($warehouseIds))
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-gray-700">Nilai Stok Gudang</p>
                <a href="{{ route('finance.stock-value', ['location_type' => 'warehouse']) }}"
                    class="text-xs text-indigo-600 hover:underline">Lihat Detail →</a>
            </div>
            <p class="text-3xl font-bold text-green-600">Rp {{ number_format($warehouseStockValue, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Berdasarkan harga jual</p>
        </div>
        @endif
    </div>

    {{-- Quick links --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Laporan Cepat</h2>
        <div class="flex flex-wrap gap-3">
            @if($isGlobal || !empty($storeIds))
            <a href="{{ route('finance.rewards') }}"
                class="bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm px-4 py-2 rounded-lg font-medium border border-blue-200 shadow-sm">
                Laporan Reward Toko
            </a>
            <a href="{{ route('reports.sales') }}"
                class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm px-4 py-2 rounded-lg font-medium">
                Laporan Penjualan
            </a>
            @endif
            <a href="{{ route('finance.stock-value') }}"
                class="bg-green-50 hover:bg-green-100 text-green-700 text-sm px-4 py-2 rounded-lg font-medium">
                Nilai Stok Detail
            </a>
            <a href="{{ route('reports.shipment') }}"
                class="bg-orange-50 hover:bg-orange-100 text-orange-700 text-sm px-4 py-2 rounded-lg font-medium">
                Laporan Pengiriman
            </a>
            <a href="{{ route('reports.transfer') }}"
                class="bg-purple-50 hover:bg-purple-100 text-purple-700 text-sm px-4 py-2 rounded-lg font-medium">
                Laporan Transfer
            </a>
        </div>
    </div>

</div>
@endsection
