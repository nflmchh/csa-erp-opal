<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\StockLedger;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutboundController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view warehouse');

        $ledgers = StockLedger::with(['variant.product.brand', 'variant.color', 'variant.size', 'creator'])
            ->where('location_type', 'warehouse')
            ->whereIn('type', ['out', 'transfer_out', 'adjust'])
            ->whereHas('variant.product')
            ->when($request->warehouse_id, fn($q) => $q->where('location_id', $request->warehouse_id))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('warehouse.outbound.index', compact('ledgers', 'warehouses'));
    }
}
