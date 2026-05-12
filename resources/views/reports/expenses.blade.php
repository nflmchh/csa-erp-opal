@extends('layouts.app')
@section('title', 'Laporan Pengeluaran')
@section('page-title', 'Laporan Pengeluaran')
@section('breadcrumb', 'Laporan / Pengeluaran')

@section('content')
<div class="space-y-4">

    {{-- Filter Form --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        
        {{-- Filter Sumber Pengeluaran (Hanya untuk Super Admin/Owner) --}}
        @hasanyrole('super admin|owner')
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sumber Pengeluaran</label>
            <select name="source_filter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 min-w-[200px]">
                <option value="">Semua Sumber (Toko & Gudang)</option>
                <optgroup label="Toko">
                    @foreach(\App\Models\Store::all() as $s)
                        <option value="store_{{ $s->id }}" {{ request('source_filter') == 'store_'.$s->id ? 'selected' : '' }}>
                            Toko: {{ $s->name }}
                        </option>
                    @endforeach
                </optgroup>
                <optgroup label="Gudang">
                    @foreach(\App\Models\Warehouse::all() as $w)
                        <option value="warehouse_{{ $w->id }}" {{ request('source_filter') == 'warehouse_'.$w->id ? 'selected' : '' }}>
                            Gudang: {{ $w->name }}
                        </option>
                    @endforeach
                </optgroup>
            </select>
        </div>
        @endhasanyrole

        {{-- Filter Jenis Pengeluaran --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Pengeluaran</label>
            <select name="expense_type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Jenis</option>
                <option value="Operasional" {{ request('expense_type') == 'Operasional' ? 'selected' : '' }}>Operasional</option>
                <option value="Sewa & Maintenance" {{ request('expense_type') == 'Sewa & Maintenance' ? 'selected' : '' }}>Sewa & Maintenance</option>
                <option value="Packaging" {{ request('expense_type') == 'Packaging' ? 'selected' : '' }}>Packaging</option>
                <option value="Logistik" {{ request('expense_type') == 'Logistik' ? 'selected' : '' }}>Logistik</option>
                <option value="Fee & Transaksi" {{ request('expense_type') == 'Fee & Transaksi' ? 'selected' : '' }}>Fee & Transaksi</option>
                <option value="Lainnya" {{ request('expense_type') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>
        </div>

        {{-- Filter Tanggal --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        
        <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg self-end hover:bg-indigo-700 transition">Filter</button>
        <a href="{{ route('reports.expenses') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end hover:bg-gray-200 transition">Reset</a>
    </form>

    {{-- Summary cards --}}
    @php
        // Menghitung total dari data yang di-paginate (atau query sebelum di paginate jika ingin total keseluruhan filter)
        // Disarankan menghitung ini di Controller agar lebih efisien, tapi bisa dilakukan di sini (hanya menghitung yang tampil di halaman ini)
        // Untuk menghitung total KESELURUHAN berdasarkan filter, Anda harus passing variabel $totalAmount dari Controller.
        // Di sini kita asumsikan $totalAmount dan $totalTransactions sudah di-passing dari ReportController.
        $displayTotal = isset($totalAmount) ? $totalAmount : $expenses->sum('amount');
        $displayCount = isset($totalTransactions) ? $totalTransactions : $expenses->total();
    @endphp
    
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Total Nominal Pengeluaran</p>
            <p class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format($displayTotal, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Total Item Pengeluaran</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($displayCount) }}</p>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Jenis</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Judul Pengeluaran</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Sumber Asal</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Struk</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($expenses as $expense)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-xs text-gray-600">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-medium bg-gray-100 text-gray-700">
                                {{ $expense->expense_type }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs font-semibold text-gray-800">{{ $expense->title }}</p>
                            @if($expense->description)
                                <p class="text-[10px] text-gray-500 mt-0.5 truncate max-w-xs">{{ $expense->description }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @if($expense->store_id)
                                <span class="text-blue-600 font-medium">Toko: {{ $expense->store->name ?? '-' }}</span>
                            @elseif($expense->warehouse_id)
                                <span class="text-purple-600 font-medium">Gudang: {{ $expense->warehouse->name ?? '-' }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center align-middle">
                            @if($expense->receipt_path)
                                <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" title="Lihat Struk" class="inline-flex items-center justify-center text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-1.5 rounded hover:bg-indigo-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-xs font-semibold text-gray-900">
                            {{ number_format($expense->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">
                            Tidak ada data laporan pengeluaran yang sesuai dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($expenses->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>

</div>
@endsection