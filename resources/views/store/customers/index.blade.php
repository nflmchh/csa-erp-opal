@extends('layouts.app')
@section('title', 'Data Pelanggan')
@section('page-title', 'Data Pelanggan')
@section('breadcrumb', 'Toko / Pelanggan')

@section('content')
<div class="space-y-4">

    <!-- Search and Filter Form -->
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        @if($stores->count() > 1)
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Toko</label>
            <select name="store_id" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Toko</option>
                @foreach($stores as $s)
                <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Cari Pelanggan</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / No Telepon…"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg self-end hover:bg-gray-700 transition">Filter</button>
        <a href="{{ route('store.customers.index') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end hover:bg-gray-200 transition">Reset</a>
    </form>

    <!-- Info/Summary -->
    <div class="text-sm text-gray-500">
        Menampilkan <span class="font-semibold text-gray-700">{{ $customers->total() }}</span> Pelanggan unik.
    </div>

    <!-- Customer Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Pelanggan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nomor Telepon</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Transaksi</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Belanja (Gross)</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Hutang</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider text-indigo-600 font-bold">Total Belanja (Net)</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $index => $customer)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($customer->customer_name, 0, 2)) }}
                                </div>
                                <div class="font-medium text-gray-900 truncate max-w-[200px]" title="{{ $customer->customer_name }}">
                                    {{ $customer->customer_name }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                            {{ $customer->customer_phone ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $customer->total_transactions }}x
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-900 font-semibold">
                            Rp {{ number_format($customer->total_spent, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-red-600 font-semibold">
                            Rp {{ number_format($customer->total_debt, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-indigo-700 font-black">
                            Rp {{ number_format($customer->total_spent - $customer->total_debt, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <a href="{{ route('store.customers.show', ['name' => $customer->customer_name, 'phone' => $customer->customer_phone]) }}" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                <span>Detail</span>
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            Tidak ada data pelanggan ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
        <div class="border-t border-gray-200 px-6 py-4">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
