<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\Warehouse;
use App\Models\Stock;
use App\Services\StockService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockEditController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('edit product stock');
        $user = Auth::user();

        $locations = collect();
        $isSuperAdmin = $user->hasAnyRole(['superadmin', 'owner']);

        if ($isSuperAdmin) {
            $stores = Store::orderBy('name')->get()->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'type' => 'store']);
            $warehouses = Warehouse::orderBy('name')->get()->map(fn($w) => ['id' => $w->id, 'name' => $w->name, 'type' => 'warehouse']);
            $locations = $stores->concat($warehouses);
        } else {
            if ($user->hasRole('kepala toko')) {
                $stores = $user->stores()->orderBy('name')->get()->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'type' => 'store']);
                $locations = $stores;
            } elseif ($user->hasRole('admin gudang')) {
                $warehouses = $user->warehouses()->orderBy('name')->get()->map(fn($w) => ['id' => $w->id, 'name' => $w->name, 'type' => 'warehouse']);
                $locations = $warehouses;
            }
        }

        $selectedLocationType = $request->input('location_type');
        $selectedLocationId = $request->input('location_id');

        // Jika hanya ada 1 lokasi atau belum dipilih, pilih yang pertama
        if (!$selectedLocationType || !$selectedLocationId) {
            $first = $locations->first();
            if ($first) {
                $selectedLocationType = $first['type'];
                $selectedLocationId = $first['id'];
            }
        }

        $query = Product::with(['variants.color', 'variants.size', 'variants.stocks' => function($q) use ($selectedLocationType, $selectedLocationId) {
            $q->where('location_type', $selectedLocationType)
              ->where('location_id', $selectedLocationId);
        }])
        ->when($request->search, function ($q) use ($request) {
            $q->where(function ($subQuery) use ($request) {
                $subQuery->where('name', 'like', '%' . $request->search . '%')
                         ->orWhere('model_code', 'like', '%' . $request->search . '%')
                         ->orWhereHas('variants', function ($vq) use ($request) {
                             $vq->where('sku', 'like', '%' . $request->search . '%');
                         });
            });
        });

        $products = $query->paginate(20)->withQueryString();

        return view('products.stock_edit', compact('products', 'locations', 'selectedLocationType', 'selectedLocationId', 'isSuperAdmin'));
    }

    public function update(Request $request)
    {
        $this->authorize('edit product stock');
        $user = Auth::user();

        $request->validate([
            'location_type' => 'required|in:store,warehouse',
            'location_id' => 'required|integer',
            'stocks' => 'required|array',
            'stocks.*' => 'integer|min:0'
        ]);

        $locationType = $request->location_type;
        $locationId = $request->location_id;

        // Security check
        if (!$user->hasAnyRole(['superadmin', 'owner'])) {
            if ($user->hasRole('kepala toko') && !$user->stores()->where('stores.id', $locationId)->exists()) {
                abort(403, 'Akses ditolak.');
            } elseif ($user->hasRole('admin gudang') && !$user->warehouses()->where('warehouses.id', $locationId)->exists()) {
                abort(403, 'Akses ditolak.');
            }
        }

        $note = "Penyesuaian Manual (" . $user->name . ")";
        $locationName = $locationType === 'store' ? Store::find($locationId)?->name : Warehouse::find($locationId)?->name;
        $changesCount = 0;

        DB::transaction(function () use ($request, $locationType, $locationId, $note, &$changesCount) {
            foreach ($request->stocks as $variantId => $newQty) {
                $variant = ProductVariant::find($variantId);
                if (!$variant) continue;

                $stock = Stock::firstOrCreate(
                    [
                        'product_variant_id' => $variant->id,
                        'location_type'      => $locationType,
                        'location_id'        => $locationId,
                    ],
                    ['qty' => 0]
                );

                $currentQty = $stock->qty;
                $diff = $newQty - $currentQty;

                if ($diff != 0) {
                    $type = $diff > 0 ? 'in' : 'out';
                    StockService::mutate(
                        $variant,
                        $locationType,
                        $locationId,
                        $diff, // ini quantity, bisa positif atau negatif, StockService akan menangani after-before
                        $type,
                        $note
                    );
                    $changesCount++;
                }
            }
        });

        if ($changesCount > 0) {
            AuditLogService::log('update', 'stocks', "Penyesuaian manual {$changesCount} stok varian di {$locationName} oleh {$user->name}", null, $request->all());
            return back()->with('success', "Berhasil memperbarui stok untuk {$changesCount} varian di {$locationName}.");
        }

        return back()->with('info', "Tidak ada perubahan stok.");
    }
}
