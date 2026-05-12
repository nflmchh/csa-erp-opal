@extends('layouts.app') 

@section('title', 'Laporan Pengeluaran')

@section('content')
<div class="p-6 bg-white rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Laporan Pengeluaran</h2>
            <p class="text-sm text-gray-500 mt-1">Daftar riwayat pengeluaran yang telah dicatat.</p>
        </div>
        <a href="{{ route('expenses.create') }}" class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengeluaran
        </a>
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="w-full text-sm text-left text-gray-600 border-collapse">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-4 py-3">Tanggal</th>
                    <th scope="col" class="px-4 py-3">Jenis</th>
                    <th scope="col" class="px-4 py-3">Judul Pengeluaran</th>
                    <th scope="col" class="px-4 py-3">Sumber Asal</th>
                    <th scope="col" class="px-4 py-3 text-center">Bukti Struk</th>
                    <th scope="col" class="px-4 py-3">Dicatat Oleh</th>
                    <th scope="col" class="px-4 py-3 text-right">Nominal (Rp)</th>
                    <!-- TAMBAHKAN INI DI BAWAH KOLOM NOMINAL -->
                    @hasanyrole('superadmin|owner')
                    <th scope="col" class="px-4 py-3 text-center">Aksi</th>
                    @endhasanyrole
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}
                    </td>
                    
                    <!-- Kolom Jenis Pengeluaran -->
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">
                            {{ $expense->expense_type }}
                        </span>
                    </td>
                    
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $expense->title }}</p>
                        @if($expense->description)
                            <p class="text-xs text-gray-500 mt-1 line-clamp-2" title="{{ $expense->description }}">{{ $expense->description }}</p>
                        @endif
                    </td>
                    
                    <td class="px-4 py-3">
                        @if($expense->store_id)
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                Toko: {{ $expense->store->name ?? 'Toko Dihapus' }}
                            </span>
                        @elseif($expense->warehouse_id)
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                Gudang: {{ $expense->warehouse->name ?? 'Gudang Dihapus' }}
                            </span>
                        @else
                            <span class="text-gray-400 italic text-xs">-</span>
                        @endif
                    </td>
                    
                    <!-- Kolom Tombol Lihat Struk -->
                    <td class="px-4 py-3 text-center align-middle">
                        @if($expense->receipt_path)
                            <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" title="Lihat Struk" class="inline-flex items-center justify-center text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-1.5 rounded-md hover:bg-indigo-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        @else
                            <span class="text-gray-400 text-sm" title="Tidak ada struk yang diunggah">-</span>
                        @endif
                    </td>
                    
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $expense->creator->name ?? 'Sistem' }}
                    </td>
                    
                    <td class="px-4 py-3 text-right font-semibold text-gray-800 whitespace-nowrap">
                        Rp {{ number_format($expense->amount, 0, ',', '.') }}
                    </td>

                    @hasanyrole('superadmin|owner')
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('expenses.edit', $expense) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium px-2 py-1 rounded hover:bg-indigo-50 transition">Edit</a>
                            
                            <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengeluaran ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium px-2 py-1 rounded hover:bg-red-50 transition">Hapus</button>
                            </form>
                        </div>
                    </td>
                    @endhasanyrole
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p>Belum ada data pengeluaran yang dicatat.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Bawaan Laravel -->
    <div class="mt-4">
        {{ $expenses->links() }}
    </div>
</div>
@endsection