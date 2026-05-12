<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Product::with(['brand', 'category', 'images', 
            // Filter stok berdasarkan Role User
            'variants.stocks' => function ($q) use ($user) {
                if ($user->hasRole('kepala toko')) {
                    $storeIds = $user->stores()->pluck('stores.id');
                    $q->where('location_type', 'store')->whereIn('location_id', $storeIds);
                } elseif ($user->hasRole('admin gudang')) {
                    $warehouseIds = $user->warehouses()->pluck('warehouses.id');
                    $q->where('location_type', 'warehouse')->whereIn('location_id', $warehouseIds);
                }
                // Superadmin/Owner otomatis melihat semua stok karena tidak difilter
            }
        ])
            ->where('is_active', true)
            ->when($request->brand_id,    fn($q) => $q->where('brand_id', $request->brand_id))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->search, function ($q) use ($request) {
                // Modifikasi: Cari Nama, Kode Model, ATAU intip ke SKU Varian
                $q->where(function ($subQuery) use ($request) {
                    $subQuery->where('name', 'like', '%' . $request->search . '%')
                             ->orWhere('model_code', 'like', '%' . $request->search . '%')
                             ->orWhereHas('variants', function ($vq) use ($request) {
                                 $vq->where('sku', 'like', '%' . $request->search . '%');
                             });
                });
            })
            ->latest();

        $products   = $query->paginate(24)->withQueryString();
        $brands     = Brand::active()->orderBy('name')->get();
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('catalog.index', compact('products', 'brands', 'categories'));
    }

    public function show(ProductVariant $productVariant)
    {
        $user = Auth::user();

        $productVariant->load([
            'product.brand', 'product.category', 'product.images',
            'product.variants.color', 'product.variants.size', 'color', 'size',
            // Filter stok varian di halaman detail
            'product.variants.stocks' => function ($q) use ($user) {
                if ($user->hasRole('kepala toko')) {
                    $storeIds = $user->stores()->pluck('stores.id');
                    $q->where('location_type', 'store')->whereIn('location_id', $storeIds);
                } elseif ($user->hasRole('admin gudang')) {
                    $warehouseIds = $user->warehouses()->pluck('warehouses.id');
                    $q->where('location_type', 'warehouse')->whereIn('location_id', $warehouseIds);
                }
            }
        ]);
        
        return view('catalog.show', compact('productVariant'));
    }
}