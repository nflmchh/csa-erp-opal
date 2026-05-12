@extends('layouts.app')

@section('title', 'Edit Pengeluaran')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow mt-6">
    <div class="mb-6 border-b pb-4">
        <h2 class="text-xl font-bold text-gray-800">Edit Pengeluaran</h2>
        <p class="text-sm text-gray-500">Perbarui data pengeluaran yang sudah tercatat.</p>
    </div>

    <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Pengeluaran *</label>
                <input type="text" name="title" value="{{ old('title', $expense->title) }}" class="w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500" required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Pengeluaran *</label>
                <select name="expense_type" class="w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500" required>
                    @foreach(['Operasional', 'Sewa & Maintenance', 'Packaging', 'Logistik', 'Fee & Transaksi', 'Lainnya'] as $type)
                        <option value="{{ $type }}" {{ old('expense_type', $expense->expense_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp) *</label>
                <input type="number" name="amount" min="0" value="{{ old('amount', (int)$expense->amount) }}" class="w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500" required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date) }}" class="w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Bukti Struk (Ganti Jika Perlu)</label>
            <input type="file" name="receipt" accept="image/png, image/jpeg, image/jpg" class="w-full border border-gray-300 rounded-md p-2 bg-gray-50">
            @if($expense->receipt_path)
                <p class="text-xs text-indigo-600 mt-2">
                    <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank" class="underline">Lihat struk saat ini</a>
                </p>
            @endif
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Tambahan</label>
            <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500">{{ old('description', $expense->description) }}</textarea>
        </div>

        <div class="mb-6 p-4 border border-indigo-100 rounded-md bg-indigo-50">
            <p class="mb-3 font-semibold text-indigo-800 text-sm">Alokasi Pengeluaran</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Sumber Pengeluaran</label>
                    <select name="source_type" class="w-full border border-gray-300 rounded p-2 text-sm" id="sourceType">
                        <option value="store" {{ $expense->store_id ? 'selected' : '' }}>Dari Toko</option>
                        <option value="warehouse" {{ $expense->warehouse_id ? 'selected' : '' }}>Dari Gudang</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Pilih Cabang</label>
                    <select name="source_id" class="w-full border border-gray-300 rounded p-2 text-sm" id="sourceId">
                        <optgroup label="Daftar Toko" id="optStore" style="{{ $expense->warehouse_id ? 'display:none;' : '' }}">
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ $expense->store_id == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Daftar Gudang" id="optWarehouse" style="{{ $expense->store_id ? 'display:none;' : '' }}">
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ $expense->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t pt-4">
            <a href="{{ route('expenses.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Batal</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Simpan Perubahan</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const sourceType = document.getElementById('sourceType');
    const optStore = document.getElementById('optStore');
    const optWarehouse = document.getElementById('optWarehouse');

    sourceType?.addEventListener('change', function() {
        if(this.value === 'store') {
            optStore.style.display = 'block';
            optWarehouse.style.display = 'none';
            // Auto select first visible option
            document.getElementById('sourceId').value = optStore.querySelector('option').value;
        } else {
            optStore.style.display = 'none';
            optWarehouse.style.display = 'block';
            document.getElementById('sourceId').value = optWarehouse.querySelector('option').value;
        }
    });
</script>
@endpush
@endsection