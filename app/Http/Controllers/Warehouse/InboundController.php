<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Inbound;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use App\Services\ReferenceNumberService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InboundController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view warehouse');
        $user = auth()->user();

        // Ambil list gudang sesuai role
        if ($user->hasRole(['superadmin', 'owner', 'finance'])) {
            $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        } elseif ($user->hasRole('admin gudang')) {
            $warehouses = $user->warehouses()->where('is_active', true)->orderBy('name')->get();
        } else {
            $warehouses = collect();
        }

        // Tentukan warehouseId aktif untuk filter
        $warehouseId = $request->warehouse_id;
        if ($user->hasRole('admin gudang')) {
            $warehouseId = $request->warehouse_id ?? $warehouses->first()?->id;
            if ($request->warehouse_id && !$warehouses->contains('id', $request->warehouse_id)) {
                $warehouseId = $warehouses->first()?->id;
            }
        }

        $currentWarehouse = $warehouseId ? $warehouses->firstWhere('id', $warehouseId) : null;

        $inbounds = Inbound::with(['warehouse', 'creator'])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('warehouse.inbound.index', compact('inbounds', 'warehouses', 'warehouseId', 'currentWarehouse'));
    }

    public function create(): View
    {
        $this->authorize('create warehouse stock');
        $user = auth()->user();

        if ($user->hasRole(['superadmin', 'owner', 'finance'])) {
            $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        } elseif ($user->hasRole('admin gudang')) {
            $warehouses = $user->warehouses()->where('is_active', true)->orderBy('name')->get();
        } else {
            $warehouses = collect();
        }

        $refNo      = ReferenceNumberService::inbound();

        return view('warehouse.inbound.create', compact('warehouses', 'refNo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create warehouse stock');
        $user = auth()->user();

        $data = $request->validate([
            'warehouse_id'          => 'required|exists:warehouses,id',
            'supplier_name'         => 'nullable|string|max:200',
            'notes'                 => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.sku'           => 'required|string',
            'items.*.qty'           => 'required|integer|min:1',
            'items.*.unit_cost'     => 'nullable|numeric|min:0',
        ]);

        // Security Check: Pastikan Admin Gudang tidak input gudang orang lain
        if ($user->hasRole('admin gudang')) {
            $allowedIds = $user->warehouses()->pluck('warehouses.id');
            if (!$allowedIds->contains($data['warehouse_id'])) {
                return back()->with('error', 'Anda tidak memiliki akses ke gudang yang dipilih.')->withInput();
            }
        }

        DB::transaction(function () use ($data, $request) {
            $refNo = ReferenceNumberService::inbound();

            $inbound = Inbound::create([
                'warehouse_id'  => $data['warehouse_id'],
                'reference_no'  => $refNo,
                'supplier_name' => $data['supplier_name'] ?? null,
                'notes'         => $data['notes'] ?? null,
                'status'        => 'draft',
                'created_by'    => Auth::id(),
            ]);

            foreach ($data['items'] as $row) {
                $variant = ProductVariant::where('sku', $row['sku'])->firstOrFail();
                $inbound->items()->create([
                    'product_variant_id' => $variant->id,
                    'qty'               => $row['qty'],
                    'unit_cost'         => $row['unit_cost'] ?? 0,
                ]);
            }

            // Immediately receive (stock goes in right away on create)
            $this->receive($inbound);

            AuditLogService::log('create', 'warehouse', "Penerimaan barang {$refNo} dibuat dan diterima",
                null, $inbound->toArray(), Inbound::class, $inbound->id);
        });

        return redirect()->route('warehouse.inbound.index')->with('success', 'Penerimaan barang berhasil disimpan dan stok telah ditambahkan.');
    }

    public function show(Inbound $inbound): View
    {
        $this->authorize('view warehouse');
        $inbound->load(['warehouse', 'creator', 'receiver', 'items.variant.product.brand', 'items.variant.color', 'items.variant.size']);

        return view('warehouse.inbound.show', compact('inbound'));
    }

    public function searchVariants(Request $request)
    {
        $term = trim($request->q ?? '');
        $warehouseId = (int) $request->warehouse_id;
        $storeId = (int) $request->store_id;
        $exact = $request->exact == '1';

        $query = \App\Models\ProductVariant::with(['product.brand', 'color', 'size'])
            ->where('is_active', true)
            ->whereHas('product', fn($q) => $q->where('is_active', true));

        if ($term !== '') {
            if ($exact) {
                $query->where('sku', $term);
            } else {
                $query->where(function($q) use ($term) {
                    $q->where('sku', 'like', "%{$term}%")
                      ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$term}%"));
                });
            }
        }

        $variants = $query->limit(50)
            ->get()
            ->map(function ($v) use ($warehouseId, $storeId) {
                $stock = 0;
                if ($warehouseId) {
                    $stock = \App\Models\Stock::where('product_variant_id', $v->id)
                        ->where('location_type', 'warehouse')
                        ->where('location_id', $warehouseId)
                        ->value('qty') ?? 0;
                } elseif ($storeId) {
                    $stock = \App\Models\Stock::where('product_variant_id', $v->id)
                        ->where('location_type', 'store')
                        ->where('location_id', $storeId)
                        ->value('qty') ?? 0;
                }

                return [
                    'id'    => $v->id,
                    'sku'   => $v->sku,
                    'label' => $v->product->name . ' · ' . $v->color->name . ' / ' . $v->size->name,
                    'price' => $v->sellPrice(),
                    'stock' => $stock,
                ];
            });

        return response()->json($variants);
    }

    private function receive(Inbound $inbound): void
    {
        foreach ($inbound->items as $item) {
            StockService::mutate(
                $item->variant,
                'warehouse',
                $inbound->warehouse_id,
                $item->qty,
                'in',
                "Penerimaan barang {$inbound->reference_no}",
                Inbound::class,
                $inbound->id
            );
        }

        $inbound->update([
            'status'      => 'received',
            'received_at' => now(),
            'received_by' => Auth::id(),
        ]);
    }
}
