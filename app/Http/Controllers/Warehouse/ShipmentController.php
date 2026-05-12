<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Stock;
use App\Models\Store;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use App\Services\ReferenceNumberService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view shipment');

        $shipments = Shipment::with(['warehouse', 'store', 'creator'])
            ->when($request->warehouse_id, fn($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->store_id,     fn($q) => $q->where('store_id', $request->store_id))
            ->when($request->status,       fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $stores     = Store::where('is_active', true)->orderBy('name')->get();

        return view('warehouse.shipments.index', compact('shipments', 'warehouses', 'stores'));
    }

    public function create(): View
    {
        $this->authorize('create shipment');

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $stores     = Store::where('is_active', true)->orderBy('name')->get();
        $shipNo     = ReferenceNumberService::shipment();

        return view('warehouse.shipments.create', compact('warehouses', 'stores', 'shipNo'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create shipment');

        $data = $request->validate([
            'warehouse_id'      => 'required|exists:warehouses,id',
            'store_id'          => 'required|exists:stores,id',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.sku'       => 'required|string',
            'items.*.qty_sent'  => 'required|integer|min:1',
        ]);

        $shipment = null;
        DB::transaction(function () use ($data, &$shipment) {
            $shipNo = ReferenceNumberService::shipment();

            $shipment = Shipment::create([
                'shipment_no'  => $shipNo,
                'warehouse_id' => $data['warehouse_id'],
                'store_id'     => $data['store_id'],
                'status'       => 'draft',
                'notes'        => $data['notes'] ?? null,
                'created_by'   => Auth::id(),
            ]);

            foreach ($data['items'] as $row) {
                $variant = \App\Models\ProductVariant::where('sku', $row['sku'])->firstOrFail();

                // Check warehouse stock
                $warehouseStock = Stock::where('product_variant_id', $variant->id)
                    ->where('location_type', 'warehouse')
                    ->where('location_id', $data['warehouse_id'])
                    ->value('qty') ?? 0;

                if ($warehouseStock < $row['qty_sent']) {
                    throw new \RuntimeException("Stok gudang tidak cukup untuk SKU {$row['sku']} (tersedia: {$warehouseStock}, diminta: {$row['qty_sent']}).");
                }

                $shipment->items()->create([
                    'product_variant_id' => $variant->id,
                    'qty_sent'           => $row['qty_sent'],
                    'qty_received'       => 0,
                ]);
            }

            AuditLogService::log('create', 'shipments', "Pengiriman {$shipNo} dibuat ke toko {$shipment->store->name}",
                null, $shipment->toArray(), Shipment::class, $shipment->id);
        });

        return redirect()->route('warehouse.shipments.show', $shipment)
            ->with('success', 'Pengiriman berhasil dibuat.');
    }

    public function show(Shipment $shipment): View
    {
        $this->authorize('view shipment');
        $shipment->load(['warehouse', 'store', 'creator', 'shipper', 'receiver',
            'items.variant.product.brand', 'items.variant.color', 'items.variant.size']);

        return view('warehouse.shipments.show', compact('shipment'));
    }

    public function updateStatus(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update shipment');

        $next = $request->validate(['status' => 'required|in:prepared,packed,shipped,arrived,received'])['status'];

        if (! $shipment->canTransitionTo($next)) {
            return back()->with('error', "Status tidak bisa langsung dari '{$shipment->statusLabel()}' ke '{$shipment::STATUS_LABELS[$next]}'.");
        }

        DB::transaction(function () use ($shipment, $next) {
            $updates = ['status' => $next];

            if ($next === 'shipped') {
                // Deduct stock from warehouse when shipped
                $shipment->load('items.variant');
                foreach ($shipment->items as $item) {
                    StockService::mutate(
                        $item->variant,
                        'warehouse',
                        $shipment->warehouse_id,
                        -$item->qty_sent,
                        'transfer_out',
                        "Pengiriman {$shipment->shipment_no} ke {$shipment->store->name}",
                        Shipment::class,
                        $shipment->id
                    );
                }
                $updates['shipped_at'] = now();
                $updates['shipped_by'] = Auth::id();
            }

            if ($next === 'arrived') {
                $updates['arrived_at'] = now();
            }

            $shipment->update($updates);

            AuditLogService::log('update', 'shipments', "Status pengiriman {$shipment->shipment_no} → {$shipment::STATUS_LABELS[$next]}",
                ['status' => $shipment->getOriginal('status')], ['status' => $next], Shipment::class, $shipment->id);
        });

        return back()->with('success', "Status diperbarui ke: {$shipment::STATUS_LABELS[$next]}.");
    }

    public function printDoc(Shipment $shipment): View
    {
        $this->authorize('print shipment');
        $shipment->load(['warehouse', 'store', 'creator', 'shipper',
            'items.variant.product.brand', 'items.variant.color', 'items.variant.size']);

        return view('warehouse.shipments.print', compact('shipment'));
    }
}
