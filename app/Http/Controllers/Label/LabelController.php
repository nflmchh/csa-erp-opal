<?php

namespace App\Http\Controllers\Label;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function single(\App\Models\ProductVariant $variant)
    {
        $variant->load(['product.brand', 'color', 'size']);
        $copies = request()->integer('copies', 1);
        
        // KUNCI PERBAIKAN:
        // Kita bungkus 1 barang ini ke dalam format array (seolah-olah cetak massal)
        // agar bisa dibaca oleh sistem Label Studio yang baru.
        $items = collect([
            ['variant' => $variant, 'copies' => $copies]
        ]);

        // Arahkan ke file 'labels.bulk' (Label Studio), BUKAN 'labels.product' lagi
        return view('labels.bulk', compact('items'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'variants'        => 'required|array|min:1',
            'variants.*.id'   => 'required|exists:product_variants,id',
            'variants.*.copies' => 'required|integer|min:1|max:100',
        ]);

        $items = collect($request->variants)->map(function ($item) {
            $variant = ProductVariant::with(['product.brand', 'color', 'size'])->find($item['id']);
            return ['variant' => $variant, 'copies' => (int) $item['copies']];
        })->filter(fn($i) => $i['variant'] !== null);

        return view('labels.bulk', compact('items'));
    }

    public function picker(Request $request)
    {
        $variants = ProductVariant::with(['product.brand', 'color', 'size'])
            ->where('is_active', true)
            ->whereHas('product')
            ->when($request->search, fn($q) => $q->where('sku', 'like', "%{$request->search}%")
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$request->search}%")))
            ->orderBy('sku')
            ->paginate(30)
            ->withQueryString();

        return view('labels.picker', compact('variants'));
    }
}
