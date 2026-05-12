@extends('layouts.app')
@section('title', 'Tambah Varian')
@section('page-title', 'Tambah Varian — ' . $product->name)
@section('breadcrumb', 'Produk / ' . $product->model_code . ' / Varian Baru')

@section('content')
<div class="max-w-3xl mx-auto" x-data="variantBuilder()">

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Tambah Varian Produk</h2>
            <span class="text-xs font-mono text-gray-400 bg-gray-100 px-2 py-1 rounded">{{ $product->name }}</span>
        </div>

        <p class="text-sm text-gray-500">Pilih kombinasi warna dan ukuran. SKU akan di-generate otomatis.</p>

        {{-- Rows --}}
        <form method="POST" action="{{ route('products.variants.store', $product) }}" id="variant-form">
            @csrf

            <div class="space-y-3 mb-4">
                <template x-for="(row, idx) in rows" :key="idx">
                    <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg bg-gray-50">
                        <span class="text-xs text-gray-400 font-mono w-6" x-text="idx + 1"></span>

                        <select :name="`variants[${idx}][color_id]`" x-model="row.color_id" required
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Warna --</option>
                            @foreach($colors as $color)
                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                            @endforeach
                        </select>

                        <select :name="`variants[${idx}][size_id]`" x-model="row.size_id" required
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Ukuran --</option>
                            @foreach($sizes as $size)
                            <option value="{{ $size->id }}">{{ $size->name }}</option>
                            @endforeach
                        </select>

                        <!-- <div class="relative w-36">
                            <span class="absolute left-3 top-2.5 text-xs text-gray-400">Rp</span>
                            <input type="number" :name="`variants[${idx}][price_adjustment]`" x-model="row.price_adj"
                                placeholder="0" step="500"
                                class="w-full border border-gray-300 rounded-lg pl-8 pr-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div> -->

                        <button type="button" @click="removeRow(idx)"
                            class="text-red-400 hover:text-red-600 text-lg font-bold leading-none"
                            :disabled="rows.length === 1">×</button>
                    </div>
                </template>
            </div>

            <button type="button" @click="addRow()"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                + Tambah baris
            </button>

            <div class="border-t border-gray-100 pt-5 mt-5 flex items-center justify-between">
                <a href="{{ route('products.show', $product) }}" class="text-sm text-gray-600 hover:underline">← Batal</a>
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">
                    Simpan Varian
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function variantBuilder() {
    return {
        rows: [{ color_id: '', size_id: '', price_adj: 0 }],
        addRow() {
            this.rows.push({ color_id: '', size_id: '', price_adj: 0 });
        },
        removeRow(idx) {
            if (this.rows.length > 1) this.rows.splice(idx, 1);
        }
    }
}
</script>
@endsection
