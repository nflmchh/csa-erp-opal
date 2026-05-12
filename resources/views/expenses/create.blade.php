@extends('layouts.app') <!-- Pastikan ini sesuai dengan nama file layout utama Anda (app.blade.php) -->

@section('title', 'Input Pengeluaran Baru')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow mt-6">
    <div class="mb-6 border-b pb-4">
        <h2 class="text-xl font-bold text-gray-800">Catat Pengeluaran Baru</h2>
        <p class="text-sm text-gray-500">Silakan isi detail pengeluaran beserta foto struk (jika ada).</p>
    </div>

    <!-- PENTING: Tambahkan enctype="multipart/form-data" untuk upload gambar -->
    <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Input Judul -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Pengeluaran *</label>
                <input type="text" name="title" class="w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: Beli Lakban & Kardus" required>
            </div>
            
            <!-- Input Dropdown Jenis Pengeluaran -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Pengeluaran *</label>
                <select name="expense_type" class="w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                    <option value="">-- Pilih Jenis Pengeluaran --</option>
                    <option value="Operasional">Operasional (Listrik, Air, Internet)</option>
                    <option value="Sewa & Maintenance">Sewa & Maintenance</option>
                    <option value="Packaging">Packaging (Kardus, Lakban, Bubble Wrap)</option>
                    <option value="Logistik">Logistik & Ongkir</option>
                    <option value="Fee & Transaksi">Fee & Transaksi</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Input Nominal -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp) *</label>
                <input type="number" name="amount" min="0" class="w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: 150000" required>
            </div>
            
            <!-- Input Tanggal -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
        </div>

        <!-- Input File Upload Struk -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Bukti Struk / Nota (Opsional)</label>
            <input type="file" name="receipt" accept="image/png, image/jpeg, image/jpg" class="w-full border border-gray-300 rounded-md p-2 bg-gray-50 cursor-pointer">
            <p class="text-xs text-gray-500 mt-1">Format yang diizinkan: JPG, JPEG, PNG. Maksimal ukuran file: 2MB.</p>
        </div>

        <!-- Input Deskripsi -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Tambahan (Opsional)</label>
            <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Catatan tambahan mengenai pengeluaran ini..."></textarea>
        </div>

        <!-- Khusus untuk Super Admin atau Owner agar bisa memilih pengeluaran ini milik siapa -->
        @hasanyrole('superadmin|owner')
        <div class="mb-6 p-4 border border-indigo-100 rounded-md bg-indigo-50">
            <p class="mb-3 font-semibold text-indigo-800 text-sm">Alokasi Pengeluaran (Fitur Super Admin / Owner)</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Sumber Pengeluaran</label>
                    <select name="source_type" class="w-full border border-gray-300 rounded p-2 text-sm" id="sourceType">
                        <option value="store">Dari Toko</option>
                        <option value="warehouse">Dari Gudang</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Pilih Cabang</label>
                    <select name="source_id" class="w-full border border-gray-300 rounded p-2 text-sm">
                        <!-- Grup Toko -->
                        <optgroup label="Daftar Toko" id="optStore">
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </optgroup>
                        
                        <!-- Grup Gudang (Disembunyikan secara default oleh JS di bawah) -->
                        <optgroup label="Daftar Gudang" id="optWarehouse" style="display:none;">
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>
        @endhasanyrole

        <!-- Tombol Aksi -->
        <div class="flex justify-end gap-3 border-t pt-4">
            <a href="{{ route('expenses.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors">Batal</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">Simpan Pengeluaran</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Script sederhana untuk mengganti pilihan cabang saat Tipe Sumber diubah (Toko / Gudang)
    document.getElementById('sourceType')?.addEventListener('change', function() {
        if(this.value === 'store') {
            document.getElementById('optStore').style.display = 'block';
            document.getElementById('optWarehouse').style.display = 'none';
        } else {
            document.getElementById('optStore').style.display = 'none';
            document.getElementById('optWarehouse').style.display = 'block';
        }
    });
</script>
@endpush
@endsection