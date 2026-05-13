<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockEntryController extends Controller
{
    public function create()
    {
        $this->authorize('create local stock entry');
        $user = Auth::user();
        
        $location = null;
        $locationType = null;

        if ($user->hasRole('kepala toko')) {
            $location = $user->primaryStore();
            $locationType = 'store';
        } elseif ($user->hasRole('admin gudang')) {
            $location = $user->primaryWarehouse();
            $locationType = 'warehouse';
        } elseif ($user->hasAnyRole(['superadmin', 'owner', 'finance'])) {
            // Admin can choose or defaults to first store
            $location = Store::first();
            $locationType = 'store';
        }

        if (!$location) {
            return redirect()->route('products.index')->with('error', 'Akun Anda belum ditugaskan ke lokasi manapun (Toko/Gudang).');
        }

        return view('products.stock_entry', compact('location', 'locationType'));
    }

    public function store(Request $request)
    {
        $this->authorize('create local stock entry');
        $user = Auth::user();

        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'qty'                => 'required|integer|min:1',
            'note'               => 'required|string|max:255',
            'location_id'        => 'required|integer',
            'location_type'      => 'required|in:store,warehouse',
        ]);

        $variant = ProductVariant::findOrFail($request->product_variant_id);
        $locationId = $request->location_id;
        $locationType = $request->location_type;

        // Security check: ensure user has access to this location
        if ($user->hasRole('kepala toko')) {
            if (!$user->stores()->where('stores.id', $locationId)->exists()) {
                abort(403, 'Anda tidak ditugaskan di toko ini.');
            }
        } elseif ($user->hasRole('admin gudang')) {
            if (!$user->warehouses()->where('warehouses.id', $locationId)->exists()) {
                abort(403, 'Anda tidak ditugaskan di gudang ini.');
            }
        }

        $stock = StockService::mutate(
            $variant,
            $locationType,
            $locationId,
            $request->qty,
            'local_entry',
            $request->note
        );

        $locationName = $locationType === 'store' ? Store::find($locationId)?->name : Warehouse::find($locationId)?->name;

        AuditLogService::log('create', 'stocks', "Input barang lokal: {$variant->sku} sebanyak {$request->qty} di {$locationName}",
            null, $request->all());

        return redirect()->route('products.index')->with('success', "Stok berhasil ditambahkan ke {$locationName}.");
    }

    public function searchVariants(Request $request)
    {
        $q = $request->input('q');
        if (!$q) return response()->json([]);

        $variants = ProductVariant::with(['product', 'color', 'size'])
            ->where('sku', 'like', "%{$q}%")
            ->orWhereHas('product', fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->limit(10)
            ->get();

        return response()->json($variants->map(fn($v) => [
            'id' => $v->id,
            'sku' => $v->sku,
            'text' => $v->product->name . ' - ' . $v->color->name . ' / ' . $v->size->name,
            'price' => $v->product->sell_price,
        ]));
    }
}
