<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Services\AuditLogService;
use App\Services\SkuGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    public function create(Product $product): View
    {
        $this->authorize('update product');
        $colors = Color::orderBy('name')->get();
        $sizes  = Size::orderBy('sort_order')->get();

        return view('products.variants.create', compact('product', 'colors', 'sizes'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update product');

        $request->validate([
            'color_ids' => 'required|array|min:1',
            'color_ids.*' => 'exists:colors,id',
            'size_ids' => 'required|array|min:1',
            'size_ids.*' => 'exists:sizes,id',
        ]);

        $colorIds = $request->input('color_ids');
        $sizeIds  = $request->input('size_ids');

        $created = 0;
        $skipped = 0;
        $brand   = $product->brand;

        foreach ($colorIds as $colorId) {
            $color = Color::find($colorId);
            
            foreach ($sizeIds as $sizeId) {
                $exists = ProductVariant::withTrashed()
                    ->where('product_id', $product->id)
                    ->where('color_id', $colorId)
                    ->where('size_id', $sizeId)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $size  = Size::find($sizeId);
                $sku   = SkuGeneratorService::generate($brand, $product->model_code, $color, $size);

                $variant = ProductVariant::create([
                    'product_id'       => $product->id,
                    'color_id'         => $colorId,
                    'size_id'          => $sizeId,
                    'sku'              => $sku,
                    'price_adjustment' => 0,
                    'is_active'        => true,
                ]);

                AuditLogService::log('create', 'products', "Varian {$sku} ditambahkan ke produk '{$product->name}'",
                    null, $variant->toArray(), ProductVariant::class, $variant->id);

                $created++;
            }
        }

        $msg = "{$created} varian baru berhasil ditambahkan.";
        if ($skipped > 0) {
            $msg .= " {$skipped} kombinasi sudah ada/pernah dihapus, sehingga dilewati.";
        }

        return redirect()->route('products.show', $product)->with('success', $msg);
    }

    public function edit(ProductVariant $variant): View
    {
        $this->authorize('update product');
        $variant->load('product', 'color', 'size');

        return view('products.variants.edit', compact('variant'));
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update product');

        $data = $request->validate([
            'price_adjustment' => 'nullable|numeric',
            'is_active'        => 'boolean',
        ]);

        $old = $variant->toArray();
        $variant->update([
            'price_adjustment' => $data['price_adjustment'] ?? 0,
            'is_active'        => $request->boolean('is_active'),
        ]);

        AuditLogService::log('update', 'products', "Varian {$variant->sku} diperbarui",
            $old, $variant->fresh()->toArray(), ProductVariant::class, $variant->id);

        return redirect()->route('products.show', $variant->product_id)
            ->with('success', "Varian {$variant->sku} diperbarui.");
    }

    public function destroy(ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update product');

        if ($variant->stocks()->where('qty', '>', 0)->exists()) {
            return back()->with('error', 'Varian tidak dapat dihapus karena masih ada stok.');
        }

        $productId = $variant->product_id;
        $sku       = $variant->sku;

        AuditLogService::log('delete', 'products', "Varian {$sku} dihapus");
        $variant->delete();

        return redirect()->route('products.show', $productId)
            ->with('success', "Varian {$sku} dihapus.");
    }
}
