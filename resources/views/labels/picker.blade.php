@extends('layouts.app')
@section('title', 'Cetak Label Produk')
@section('page-title', 'Cetak Label Produk')
@section('breadcrumb', 'Label')

@section('content')
<div class="space-y-4" x-data="labelPicker()">

    {{-- Search --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex gap-3 items-end">
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-500 mb-1">Cari SKU atau Nama Produk</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Ketik SKU atau nama…"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg self-end">Cari</button>
        <a href="{{ route('labels.picker') }}" class="bg-gray-100 text-gray-600 text-sm px-4 py-2 rounded-lg self-end">Reset</a>
    </form>

    {{-- Selected items summary --}}
    <div x-show="selected.length > 0" x-transition
        class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-center justify-between" style="display:none">
        <p class="text-sm text-indigo-700">
            <strong x-text="selected.length"></strong> SKU dipilih,
            <strong x-text="totalCopies()"></strong> total lembar
        </p>
        <form method="POST" action="{{ route('labels.bulk') }}" id="bulkForm">
            @csrf
            <template x-for="(item, idx) in selected" :key="item.id">
                <span>
                    <input type="hidden" :name="'variants[' + idx + '][id]'" :value="item.id">
                    <input type="hidden" :name="'variants[' + idx + '][copies]'" :value="item.copies">
                </span>
            </template>
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-lg text-sm">
                Cetak Semua Label
            </button>
        </form>
    </div>

    {{-- Product list --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-600 uppercase w-10">Pilih</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">SKU</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Produk</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Warna / Ukuran</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Harga</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase">Cetak 1</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($variants as $v)
                    <tr class="hover:bg-gray-50" :class="isSelected({{ $v->id }}) ? 'bg-indigo-50' : ''">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox"
                                :checked="isSelected({{ $v->id }})"
                                @change="toggleSelect({{ $v->id }}, '{{ addslashes($v?->sku) }}', $event.target.checked)"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">{{ $v?->sku }}</td>
                        <td class="px-4 py-3">
                            <p class="text-xs font-medium text-gray-800">{{ optional($v->product)->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ optional($v->product->brand)->name ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $v?->color?->name }} / {{ $v?->size?->name }}</td>
                        <td class="px-4 py-3 text-right text-xs font-semibold text-gray-800">
                            Rp {{ number_format($v->sellPrice(), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <!-- Perhatikan tambahan titik dua (:) pada href dan penggunaan fungsi getCopies() -->
                            <a :href="`{{ route('labels.single', $v) }}?copies=1`"
                                class="text-xs text-indigo-600 border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-lg transition-colors">
                                Cetak
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Tidak ada produk ditemukan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($variants->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $variants->links() }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function labelPicker() {
    return {
        selected: [],

        isSelected(id) {
            return this.selected.some(s => s.id === id);
        },

        getCopies(id) {
            const item = this.selected.find(s => s.id === id);
            return item ? item.copies : 1;
        },

        toggleSelect(id, sku, checked) {
            if (checked) {
                if (!this.isSelected(id)) this.selected.push({ id, sku, copies: 1 });
            } else {
                this.selected = this.selected.filter(s => s.id !== id);
            }
        },

        setCopies(id, val) {
            const item = this.selected.find(s => s.id === id);
            if (item) item.copies = Math.max(1, parseInt(val) || 1);
        },

        totalCopies() {
            return this.selected.reduce((sum, s) => sum + s.copies, 0);
        },
    };
}
</script>
@endpush
@endsection
