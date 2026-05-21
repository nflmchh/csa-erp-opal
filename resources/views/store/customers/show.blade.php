@extends('layouts.app')
@section('title', 'Detail Pelanggan')
@section('page-title', 'Detail Pelanggan')
@section('breadcrumb', 'Toko / Pelanggan / Detail')

@section('content')
<div class="space-y-6" x-data="posHistoryApp()">

    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <a href="{{ route('store.customers.index') }}" 
            class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <!-- Customer Overview and Statistics -->
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Profile Card -->
        <div class="w-full lg:w-1/4 bg-white rounded-xl border border-gray-200 p-6 flex flex-col items-center text-center shadow-sm shrink-0">
            <div class="flex items-center justify-center w-20 h-20 rounded-full bg-indigo-50 text-indigo-700 font-black text-2xl mb-4 border border-indigo-100">
                {{ strtoupper(substr($customerName, 0, 2)) }}
            </div>
            <h3 class="text-lg font-bold text-gray-900 leading-tight mb-1" title="{{ $customerName }}">{{ $customerName }}</h3>
            <p class="text-sm text-gray-500 font-medium flex items-center gap-1.5 justify-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                {{ $customerPhone ?: '-' }}
            </p>
        </div>

        <!-- Metrics Cards -->
        <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Total Belanja Gross -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-12 h-12 bg-gray-50 rounded-bl-full -mr-3 -mt-3"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Total Belanja (Gross)</p>
                    <h4 class="text-lg font-black text-gray-900 mt-1">Rp {{ number_format($totalSpent, 0, ',', '.') }}</h4>
                </div>
                <div class="p-2 bg-gray-100 text-gray-600 rounded-lg relative z-10 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Total Belanja Net -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-12 h-12 bg-emerald-50 rounded-bl-full -mr-3 -mt-3"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-1">Total Belanja (Net)</p>
                    <h4 class="text-lg font-black text-emerald-600 mt-1">Rp {{ number_format($netSpent, 0, ',', '.') }}</h4>
                </div>
                <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg relative z-10 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Total Hutang -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-12 h-12 bg-rose-50 rounded-bl-full -mr-3 -mt-3"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider mb-1">Total Hutang</p>
                    <h4 class="text-lg font-black text-rose-600 mt-1">Rp {{ number_format($totalDebt, 0, ',', '.') }}</h4>
                </div>
                <div class="p-2 bg-rose-100 text-rose-600 rounded-lg relative z-10 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>

            <!-- Total Transaksi -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-12 h-12 bg-blue-50 rounded-bl-full -mr-3 -mt-3"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-1">Total Transaksi</p>
                    <h4 class="text-lg font-black text-gray-900 mt-1">{{ $totalTransactions }}x Belanja</h4>
                </div>
                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg relative z-10 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Filter Form -->
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end shadow-sm">
        <input type="hidden" name="name" value="{{ $customerName }}">
        <input type="hidden" name="phone" value="{{ $customerPhone }}">
        @if($stores->count() > 1)
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Filter Toko</label>
            <select name="store_id" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Toko</option>
                @foreach($stores as $s)
                <option value="{{ $s->id }}" {{ $storeId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg self-end hover:bg-gray-700 transition">Saring</button>
        <a href="{{ route('store.customers.show', ['name' => $customerName, 'phone' => $customerPhone]) }}" 
            class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end hover:bg-gray-200 transition">Reset Filter</a>
    </form>

    @if($sales->where('payment_status', '!=', 'lunas')->count() > 0)
    <!-- Unpaid Transactions Section -->
    <div class="bg-white rounded-xl border border-red-200 shadow-sm overflow-hidden">
        <div class="bg-red-50/50 border-b border-red-100 px-6 py-4">
            <h4 class="text-sm font-bold text-red-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-red-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Daftar Transaksi Belum Lunas
            </h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-red-50/30 text-red-700 font-semibold border-b border-red-100">
                    <tr>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider">No. Transaksi</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Tanggal Transaksi</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Jatuh Tempo</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider text-right">Total Belanja</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider text-right">Telah Dibayar</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider text-right text-red-600">Sisa Hutang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-red-100 bg-red-50/10">
                    @foreach($sales->where('payment_status', '!=', 'lunas') as $sale)
                    <tr class="hover:bg-red-50/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-mono font-medium text-indigo-600">
                            <button @click="openReceipt({{ $sale->id }})" class="hover:underline flex items-center gap-1.5 text-left font-semibold">
                                <span>{{ $sale->sale_no }}</span>
                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                {{ strtoupper($sale->payment_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                            {{ $sale->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium {{ \Carbon\Carbon::parse($sale->due_date)->isPast() ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                            {{ $sale->due_date ? \Carbon\Carbon::parse($sale->due_date)->format('d/m/Y') : '-' }}
                            @if($sale->due_date && \Carbon\Carbon::parse($sale->due_date)->isPast())
                                <span class="text-[9px] bg-red-600 text-white px-1.5 py-0.5 rounded ml-1 uppercase">Overdue</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-gray-900">
                            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-600">
                            Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-red-600">
                            Rp {{ number_format(max(0, $sale->total_amount - $sale->amount_paid), 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Detailed Transaction Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4">
            <h4 class="text-sm font-bold text-gray-800">Riwayat Riil Transaksi Pelanggan</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider">No. Transaksi</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Tanggal & Waktu</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Toko</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Jatuh Tempo</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Metode Pembayaran</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider">Kasir/Pembuat</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider text-right">Sisa Hutang</th>
                        <th class="px-6 py-3 text-xs uppercase tracking-wider text-right">Total Belanja</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50 transition-colors {{ $sale->payment_status !== 'lunas' ? 'bg-rose-50/20' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap font-mono font-medium text-indigo-600">
                            <button @click="openReceipt({{ $sale->id }})" class="hover:underline flex items-center gap-1.5 text-left font-semibold">
                                <span>{{ $sale->sale_no }}</span>
                                <svg class="w-3.5 h-3.5 text-indigo-500 hover:text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                            {{ $sale->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                            {{ $sale->store ? $sale->store->name : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($sale->payment_status === 'lunas')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                Lunas
                            </span>
                            @elseif($sale->payment_status === 'tempo')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 animate-pulse">
                                Tempo
                            </span>
                            @elseif($sale->payment_status === 'dp')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                DP (Uang Muka)
                            </span>
                            @elseif($sale->payment_status === 'po')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                PO
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                {{ strtoupper($sale->payment_status) }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                            {{ $sale->due_date ? \Carbon\Carbon::parse($sale->due_date)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">
                            {{ $sale->paymentMethod ? $sale->paymentMethod->name : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                            {{ $sale->creator ? $sale->creator->name : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-rose-600">
                            Rp {{ number_format(max(0, $sale->total_amount - $sale->amount_paid), 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-gray-900">
                            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                            Belum ada riwayat transaksi pada filter toko terpilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Print Modal Teleport -->
    <template x-teleport="body">
        <div x-show="showReceiptModal" style="display: none; z-index: 999999;" class="fixed inset-0 flex items-center justify-center bg-gray-900/70 backdrop-blur-md p-4 transition-opacity">
            <div @click.outside="showReceiptModal = false" x-show="showReceiptModal" x-transition.scale.origin.bottom class="bg-white w-full max-w-md rounded-[2rem] overflow-hidden shadow-2xl flex flex-col max-h-[90vh] border border-white/20">
                <!-- Header Modal -->
                <div class="bg-indigo-600 px-6 py-4 flex flex-col gap-3 shrink-0 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                    <div class="absolute -left-4 -bottom-4 w-24 h-24 bg-black/10 rounded-full blur-xl"></div>
                    
                    <div class="flex justify-between items-center relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h3 class="text-white font-black text-xl tracking-wide">Cetak Struk</h3>
                                <p class="text-indigo-100 text-xs font-medium">Preview transaksi terpilih</p>
                            </div>
                        </div>
                        <button @click="showReceiptModal = false" class="text-white/70 hover:text-white bg-black/10 hover:bg-black/20 p-2 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    @if(auth()->user()->hasRole('superadmin'))
                    <!-- Superadmin Printer Selector -->
                    <div class="relative z-10 bg-black/10 border border-white/10 rounded-xl p-2 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-white/80 uppercase tracking-widest ml-1">Setting Printer:</span>
                        <select x-model="printMethod" @change="localStorage.setItem('pos_print_method', $event.target.value)" 
                                class="bg-white border-none rounded-lg py-1 px-3 text-[10px] font-black text-indigo-600 focus:ring-0 cursor-pointer">
                            <option value="pc_usb">USB (BROWSER)</option>
                            <option value="pc_bluetooth">BLUETOOTH (PC)</option>
                            <option value="ios_bluefy">BLUETOOTH (IOS)</option>
                            <option value="android_bluetooth">BLUETOOTH (ANDROID)</option>
                            <option value="android_flutter">ANDROID APP</option>
                        </select>
                    </div>
                    @endif
                </div>
                
                <!-- Area Preview Struk -->
                <div class="flex-1 bg-gray-50 overflow-y-auto p-6 flex justify-center custom-scrollbar relative shadow-inner">
                    <!-- Decorative background elements -->
                    <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px] opacity-50"></div>
                    
                    <div id="print-area" x-html="receiptHtmlHtml" class="bg-white shadow-xl p-0 relative z-10 transition-transform duration-300 min-w-[72mm] flex-shrink-0" style="zoom: 0.85; min-height: 100px;"></div>
                </div>

                <!-- Area Aksi / Tombol -->
                <div class="p-5 bg-white border-t border-gray-100 flex flex-col gap-3 shrink-0 shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.05)]">
                    <button @click="executePrint()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 rounded-2xl text-lg shadow-[0_8px_20px_-6px_rgba(79,70,229,0.5)] hover:shadow-[0_12px_25px_-8px_rgba(79,70,229,0.7)] transition-all flex items-center justify-center gap-3 group">
                        <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        CETAK STRUK SEKARANG
                    </button>
                    <button @click="showReceiptModal = false" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3.5 rounded-2xl transition-colors flex items-center justify-center gap-2">
                        Tutup Preview
                    </button>
                </div>
            </div>
        </div>
    </template>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }

        @media print {
            header, nav, aside { display: none !important; }
            .space-y-6 { display: none !important; }
            .fixed.inset-0 { position: static !important; background: transparent !important; padding: 0 !important; }
            .bg-white.max-w-md { max-width: 100% !important; box-shadow: none !important; height: auto !important; }
            .bg-indigo-600, .p-5.bg-white.border-t { display: none !important; }
            @page { margin: 0; }
        }
    </style>

</div>
@endsection

@push('scripts')
<script>
function posHistoryApp() {
    return {
        showReceiptModal: false,
        receiptHtmlHtml: '',
        currentSaleData: null,
        printMethod: localStorage.getItem('pos_print_method') || 'pc_usb',
        cachedBluetoothDevice: null,
        cachedCharacteristic: null,

        async openReceipt(saleId) {
            try {
                let res = await fetch(`/reports/sales/${saleId}/detail`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!res.ok) {
                    alert('Gagal mengambil data struk (status: ' + res.status + ').');
                    return;
                }
                let data = await res.json();
                if (data.success) {
                    this.currentSaleData = data.sale;
                    if (data.html) {
                        this.receiptHtmlHtml = data.html;
                    } else {
                        this.receiptHtmlHtml = this.buildSimpleReceiptHtml(data.sale);
                    }
                    this.showReceiptModal = true;
                } else {
                    alert('Gagal mengambil data struk.');
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan: ' + err.message);
            }
        },

        buildSimpleReceiptHtml(sale) {
            const fmt = (n) => parseInt(n || 0).toLocaleString('id-ID');
            let rows = sale.items.map(item => {
                const name = item.variant?.product?.name || '-';
                const sku  = item.variant?.sku || '';
                const color = item.variant?.color?.name || '';
                const size = item.variant?.size?.name || '';
                return `<div style="margin-bottom:8px">
                    <div style="font-weight:bold;font-size:13px">${name}</div>
                    <div style="font-size:11px;color:#444;margin-bottom:2px;">${sku} · ${color} / ${size}</div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="font-size:12px">@ Rp ${fmt(item.unit_price)}</span>
                        <span style="width:40px;text-align:center;">x${item.qty}</span>
                        <span style="font-weight:bold;width:85px;text-align:right;">Rp ${fmt(item.subtotal)}</span>
                    </div>
                </div>`;
            }).join('');
            
            let pMethod = sale.payment_method || sale.paymentMethod;
            let pMethodName = pMethod ? pMethod.name.toUpperCase() : '-';
            let priceLabel = sale.price_method === 'custom' ? 'Ecer (Custom)' : (sale.price_method === 'grosir' ? 'Grosir' : 'Ecer');
            let statusLabel = sale.payment_status ? sale.payment_status.toUpperCase() : 'LUNAS';
            let statusColor = (sale.payment_status === 'lunas') ? '#16a34a' : '#dc2626';

            let dueDateHtml = '';
            if (sale.due_date) {
                let dueD = new Date(sale.due_date);
                let dueFmt = String(dueD.getDate()).padStart(2, '0') + "/" + String(dueD.getMonth() + 1).padStart(2, '0') + "/" + dueD.getFullYear();
                dueDateHtml = `<div style="display:flex;justify-content:space-between;margin-bottom:4px"><span>Jatuh Tempo</span><span>${dueFmt}</span></div>`;
            }

            let customerHtml = '';
            if (sale.customer_name) {
                customerHtml += `<div style="border-top:1px dashed #000;margin:6px 0"></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px"><span>Nama Pelanggan:</span><span style="font-weight:bold">${sale.customer_name}</span></div>`;
                if (sale.customer_phone) {
                    customerHtml += `<div style="display:flex;justify-content:space-between;margin-bottom:4px"><span>No telp Pelanggan:</span><span>${sale.customer_phone}</span></div>`;
                }
            }

            let d = new Date(sale.created_at);
            let formattedDate = d.getFullYear() + "-" + 
                String(d.getMonth() + 1).padStart(2, '0') + "-" + 
                String(d.getDate()).padStart(2, '0') + " " + 
                String(d.getHours()).padStart(2, '0') + ":" + 
                String(d.getMinutes()).padStart(2, '0');

            let bankHtml = '';
            if (sale.store?.bank_name || sale.store?.bank_account) {
                bankHtml += `<div style="text-align:center;margin-bottom:4px">PEMBAYARAN TRANSFER:</div>`;
                if (sale.store?.bank_name) bankHtml += `<div style="text-align:center;margin-bottom:2px">Bank: ${sale.store.bank_name}</div>`;
                if (sale.store?.bank_account) bankHtml += `<div style="text-align:center;font-weight:bold;margin-bottom:2px">${sale.store.bank_account}</div>`;
                if (sale.store?.bank_account_name) bankHtml += `<div style="text-align:center;margin-bottom:2px">A.N. ${sale.store.bank_account_name}</div>`;
                bankHtml += `<div style="border-top:1px dashed #000;margin:10px 0"></div>`;
            }

            let phoneHtml = '';
            if (sale.store?.phone) {
                phoneHtml += `<div style="text-align:center;margin-top:10px">No Telp</div>`;
                if (Array.isArray(sale.store.phone)) {
                    sale.store.phone.forEach(p => { if (p) phoneHtml += `<div style="text-align:center">${p}</div>`; });
                } else if (typeof sale.store.phone === 'string') {
                    try {
                        let phones = JSON.parse(sale.store.phone);
                        if (Array.isArray(phones)) {
                            phones.forEach(p => { if (p) phoneHtml += `<div style="text-align:center">${p}</div>`; });
                        } else {
                            phoneHtml += `<div style="text-align:center">${sale.store.phone}</div>`;
                        }
                    } catch(e) {
                        phoneHtml += `<div style="text-align:center">${sale.store.phone}</div>`;
                    }
                }
                phoneHtml += `<div style="margin-bottom:10px"></div>`;
            }

            let qrBarcodeHtml = `
                <div style="text-align:center;margin:10px 0;padding:10px;border:1px dashed #ccc;color:#666;font-size:10px;">
                    [ QR CODE ]<br><br>
                    [ BARCODE: ${sale.sale_no} ]<br>
                    ${sale.sale_no}
                </div>
            `;

            return `<div style="font-family:'Courier New', monospace;font-size:13px;width:72mm;margin:0 auto;padding:12px;color:#000;background:#fff;">
                <div style="text-align:center;font-weight:bold;font-size:16px;margin-bottom:2px;text-transform:uppercase;">${sale.store?.name || ''}</div>
                <div style="text-align:center;font-size:9px;color:#666;margin-bottom:4px">SevenKey erp</div>
                ${sale.store?.address ? `<div style="text-align:center;font-size:11px;color:#444">${sale.store.address}</div>` : ''}
                <div style="border-top:1px dashed #000;margin:10px 0"></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px"><span>No.</span><span style="font-weight:bold">${sale.sale_no}</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px"><span>Tgl</span><span>${formattedDate}</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px"><span>Kasir</span><span>${sale.creator?.name||'-'}</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px"><span>Metode</span><span>${pMethodName}</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px"><span>Harga</span><span>${priceLabel}</span></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;color:${statusColor};font-weight:bold"><span>Status</span><span>${statusLabel}</span></div>
                ${dueDateHtml}
                ${customerHtml}
                <div style="border-top:1px dashed #000;margin:10px 0"></div>
                <div style="margin-bottom:6px">
                    <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:12px;text-transform:uppercase;">
                        <span style="flex:1;padding-right:8px;">ITEM</span>
                        <span style="width:85px;text-align:right;">TOTAL</span>
                    </div>
                </div>
                <div style="border-top:1px solid #000;margin:10px 0"></div>
                ${rows}
                <div style="border-top:1px solid #000;margin:10px 0"></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px"><span>Subtotal</span><span>Rp ${fmt(sale.subtotal)}</span></div>
                ${sale.discount_amount > 0 ? `<div style="display:flex;justify-content:space-between;margin-bottom:4px"><span>Diskon</span><span>-Rp ${fmt(sale.discount_amount)}</span></div>` : ''}
                <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:15px;margin-top:8px"><span>TOTAL</span><span>Rp ${fmt(sale.total_amount)}</span></div>
                <div style="display:flex;justify-content:space-between;margin-top:8px"><span>Bayar (${sale.payment_status === 'lunas' ? 'Tunai' : 'Uang Muka/DP'})</span><span>Rp ${fmt(sale.amount_paid)}</span></div>
                ${sale.payment_status !== 'lunas' ? `<div style="display:flex;justify-content:space-between;font-weight:bold;color:#dc2626;margin-bottom:4px"><span>Sisa Hutang</span><span>Rp ${fmt(Math.max(0, sale.total_amount - sale.amount_paid))}</span></div>` : ''}
                ${sale.change_amount > 0 ? `<div style="display:flex;justify-content:space-between;font-weight:bold;margin-bottom:4px"><span>Kembalian</span><span>Rp ${fmt(sale.change_amount)}</span></div>` : ''}
                <div style="border-top:1px dashed #000;margin:10px 0"></div>
                ${bankHtml}
                ${qrBarcodeHtml}
                ${phoneHtml}
                <div style="text-align:center;font-size:12px;font-weight:bold;margin-top:16px">TERIMA KASIH TELAH BERBELANJA</div>
                <div style="text-align:center;font-size:10px;margin-top:4px">Silahkan bawa struk ini untuk retur barang</div>
                <div style="height:2.5cm"></div>
            </div>`;
        },

        async executePrint() {
            if (this.printMethod === 'pc_usb') {
                let printFrame = document.createElement('iframe');
                printFrame.style.display = 'none';
                document.body.appendChild(printFrame);
                printFrame.contentDocument.write('<html><head><style>@page { margin: 0; } body { margin: 0; font-family: monospace; }</style></head><body>' + this.receiptHtmlHtml + '</body></html>');
                printFrame.contentDocument.close();
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
                setTimeout(() => document.body.removeChild(printFrame), 2000);
            } 
            else if (this.printMethod === 'android_flutter') {
                let sale = this.currentSaleData;
                
                let d = new Date(sale.created_at);
                let formattedDate = d.getFullYear() + "-" + 
                    String(d.getMonth() + 1).padStart(2, '0') + "-" + 
                    String(d.getDate()).padStart(2, '0') + " " + 
                    String(d.getHours()).padStart(2, '0') + ":" + 
                    String(d.getMinutes()).padStart(2, '0');

                let dataStruk = {
                    store_name: sale.store.name,
                    store_address: sale.store.address || '',
                    receipt_no: sale.sale_no,
                    date: formattedDate,
                    cashier: sale.creator ? sale.creator.name.substring(0, 15) : '-',
                    items: sale.items.map(item => ({
                        name: String(item.variant.product.name).substring(0, 30),
                        qty: item.qty,
                        price: parseInt(item.unit_price).toLocaleString('id-ID'),
                        total: parseInt(item.subtotal).toLocaleString('id-ID')
                    })),
                    subtotal: parseInt(sale.subtotal).toLocaleString('id-ID'),
                    grand_total: parseInt(sale.total_amount).toLocaleString('id-ID'),
                    paid: parseInt(sale.amount_paid).toLocaleString('id-ID'),
                    change: parseInt(sale.change_amount).toLocaleString('id-ID'),
                    bank_name: sale.store.bank_name || '',
                    bank_account: sale.store.bank_account || '',
                    bank_account_name: sale.store.bank_account_name || '',
                    qr_code: sale.sale_no,
                    barcode: sale.sale_no
                };

                if (window.PrintChannel) {
                    window.PrintChannel.postMessage(JSON.stringify(dataStruk));
                } else {
                    alert("Aplikasi Native Flutter tidak terdeteksi!");
                }
            } 
            else {
                try {
                    if (!this.cachedCharacteristic) {
                        this.cachedBluetoothDevice = await navigator.bluetooth.requestDevice({
                            acceptAllDevices: true,
                            optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                        });
                        
                        this.cachedBluetoothDevice.addEventListener('gattserverdisconnected', () => {
                            this.cachedCharacteristic = null;
                        });

                        const server = await this.cachedBluetoothDevice.gatt.connect();
                        const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                        this.cachedCharacteristic = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                    }

                    const alignLR = (left, right, max = 48) => {
                        let spaces = max - left.toString().length - right.toString().length;
                        return left + " ".repeat(spaces > 0 ? spaces : 1) + right + "\n";
                    };
                    const alignC = (text, max = 48) => {
                        let spaces = Math.floor((max - text.length) / 2);
                        return " ".repeat(spaces > 0 ? spaces : 0) + text + "\n";
                    };

                    let sale = this.currentSaleData;
                    
                    let d = new Date(sale.created_at);
                    let formattedDate = d.getFullYear() + "-" + 
                        String(d.getMonth() + 1).padStart(2, '0') + "-" + 
                        String(d.getDate()).padStart(2, '0') + " " + 
                        String(d.getHours()).padStart(2, '0') + ":" + 
                        String(d.getMinutes()).padStart(2, '0');

                    let text = "\n"; 
                    
                    text += "\x1B\x61\x01"; // Align Center
                    text += "\x1D\x21\x11\x1B\x45\x01"; // Double Height/Width + Bold
                    text += sale.store.name.toUpperCase() + "\n";
                    text += "\x1D\x21\x00\x1B\x45\x00"; // Reset Size + Normal
                    text += "SevenKey erp\n";
                    if (sale.store.address) {
                        text += sale.store.address + "\n";
                    }
                    text += "\x1B\x61\x00"; // Align Left
                    
                    text += "------------------------------------------------\n";
                    text += alignLR("No:", sale.sale_no);
                    text += alignLR("Tgl:", formattedDate);
                    text += alignLR("Kasir:", sale.creator ? sale.creator.name.substring(0, 15) : '-');

                    let pMethod = sale.payment_method || sale.paymentMethod;
                    text += alignLR("Metode:", pMethod ? pMethod.name.toUpperCase() : '-');
                    
                    let priceLabel = sale.price_method === 'custom' ? 'Ecer (Custom)' : (sale.price_method === 'grosir' ? 'Grosir' : 'Ecer');
                    text += alignLR("Harga:", priceLabel);
                    
                    let statusLabel = sale.payment_status ? sale.payment_status.toUpperCase() : 'LUNAS';
                    text += alignLR("Status:", statusLabel);
                    if (sale.due_date) {
                        let dueD = new Date(sale.due_date);
                        let dueFmt = String(dueD.getDate()).padStart(2, '0') + "/" + String(dueD.getMonth() + 1).padStart(2, '0') + "/" + dueD.getFullYear();
                        text += alignLR("Jatuh Tempo:", dueFmt);
                    }

                    if (sale.customer_name) {
                        text += "------------------------------------------------\n";
                        text += alignLR("Nama Pelanggan:", sale.customer_name);
                        if (sale.customer_phone) {
                            text += alignLR("No telp Pelanggan:", sale.customer_phone);
                        }
                    }

                    text += "------------------------------------------------\n";
                    text += alignLR("ITEM", "TOTAL");
                    text += "------------------------------------------------\n";
                    
                    sale.items.forEach(item => {
                        text += String(item.variant.product.name).substring(0, 48) + "\n";
                        let skuText = item.variant.sku + " · " + (item.variant.color ? item.variant.color.name : '') + " / " + (item.variant.size ? item.variant.size.name : '');
                        text += String(skuText).substring(0, 48) + "\n";
                        
                        let c1 = "@ Rp " + parseInt(item.unit_price).toLocaleString('id-ID');
                        let c2 = "x" + item.qty;
                        let leftSide = c1.padEnd(20, ' ') + c2;
                        let rightSide = "Rp " + parseInt(item.subtotal).toLocaleString('id-ID');
                        text += alignLR(leftSide, rightSide);
                    });
                    
                    text += "------------------------------------------------\n";
                    text += alignLR("Subtotal", "Rp " + parseInt(sale.subtotal).toLocaleString('id-ID'));
                    if (sale.discount_amount > 0) {
                        text += alignLR("Diskon", "-Rp " + parseInt(sale.discount_amount).toLocaleString('id-ID'));
                    }
                    text += alignLR("TOTAL", "Rp " + parseInt(sale.total_amount).toLocaleString('id-ID'));
                    
                    let bayarLabel = (sale.payment_status === 'lunas') ? 'Bayar (Tunai)' : 'Bayar (Uang Muka/DP)';
                    text += alignLR(bayarLabel, "Rp " + parseInt(sale.amount_paid).toLocaleString('id-ID'));
                    
                    if (sale.payment_status !== 'lunas') {
                        let sisa = Math.max(0, sale.total_amount - sale.amount_paid);
                        text += alignLR("Sisa Hutang", "Rp " + parseInt(sisa).toLocaleString('id-ID'));
                    }

                    if (sale.change_amount > 0) {
                        text += alignLR("Kembalian", "Rp " + parseInt(sale.change_amount).toLocaleString('id-ID'));
                    }
                    text += "------------------------------------------------\n";
                    
                    if (sale.store.bank_name || sale.store.bank_account) {
                        text += alignC("PEMBAYARAN TRANSFER:");
                        if (sale.store.bank_name) text += alignC("Bank: " + sale.store.bank_name);
                        if (sale.store.bank_account) text += alignC(sale.store.bank_account);
                        if (sale.store.bank_account_name) text += alignC("A.N. " + sale.store.bank_account_name);
                        text += "------------------------------------------------\n";
                    }

                    if (sale.store.phone) {
                        text += alignC("No Telp");
                        if (Array.isArray(sale.store.phone)) {
                            sale.store.phone.forEach(p => { if (p) text += alignC(p); });
                        } else if (typeof sale.store.phone === 'string') {
                            try {
                                let phones = JSON.parse(sale.store.phone);
                                if (Array.isArray(phones)) {
                                    phones.forEach(p => { if (p) text += alignC(p); });
                                } else {
                                    text += alignC(sale.store.phone);
                                }
                            } catch(e) {
                                text += alignC(sale.store.phone);
                            }
                        }
                        text += "\n";
                    }

                    const encoder = new TextEncoder();
                    const init = new Uint8Array([0x1B, 0x40]);
                    const contentBytes = encoder.encode(text);
                    
                    let qrData = sale.sale_no;
                    let qrBytes = encoder.encode(qrData);
                    let pL = (qrBytes.length + 3) % 256;
                    let pH = Math.floor((qrBytes.length + 3) / 256);
                    let qrCmds = new Uint8Array([
                        0x1B, 0x61, 0x01,
                        0x1D, 0x28, 0x6B, 0x04, 0x00, 0x31, 0x41, 0x32, 0x00,
                        0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x43, 0x06,
                        0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x45, 0x31,
                        0x1D, 0x28, 0x6B, pL, pH, 0x31, 0x50, 0x30, ...qrBytes,
                        0x1D, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x51, 0x30,
                        0x0A, 0x0A
                    ]);

                    let barcodeBytes = encoder.encode("{B" + sale.sale_no);
                    let textBelowBarcodeBytes = encoder.encode(sale.sale_no + "\n");
                    let barcodeCmds = new Uint8Array([
                        0x1B, 0x61, 0x01,
                        0x1D, 0x68, 60,
                        0x1D, 0x77, 2,
                        0x1D, 0x48, 0,
                        0x1D, 0x6B, 73, barcodeBytes.length, ...barcodeBytes,
                        0x0A,
                        ...textBelowBarcodeBytes,
                        0x0A, 0x0A
                    ]);

                    const thanksBytes = encoder.encode(alignC("TERIMA KASIH TELAH BERBELANJA") + alignC("Silahkan bawa struk ini untuk retur barang", 48));
                    const feed = new Uint8Array([0x1B, 0x64, 0x05]); 

                    const payload = new Uint8Array(init.length + contentBytes.length + qrCmds.length + barcodeCmds.length + thanksBytes.length + feed.length);
                    let offset = 0;
                    payload.set(init, offset); offset += init.length;
                    payload.set(contentBytes, offset); offset += contentBytes.length;
                    payload.set(qrCmds, offset); offset += qrCmds.length;
                    payload.set(barcodeCmds, offset); offset += barcodeCmds.length;
                    payload.set(thanksBytes, offset); offset += thanksBytes.length;
                    payload.set(feed, offset);

                    for (let i = 0; i < payload.length; i += 40) {
                        await this.cachedCharacteristic.writeValue(payload.slice(i, i + 40));
                    }
                } catch (e) { 
                    alert("Koneksi Bluetooth Terputus. Mohon pilih perangkat kembali. " + e.message); 
                    this.cachedCharacteristic = null; 
                }
            }
        },
    };
}
</script>
@endpush
