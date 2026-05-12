<?php

namespace App\Http\Controllers\Returns;

use App\Http\Controllers\Controller;
use App\Models\CustomerReturn;
use App\Models\CustomerReturnItem;
use App\Models\ProductVariant;
use App\Models\ReturnReason;
use App\Models\Sale;
use App\Models\Store;
use App\Services\AuditLogService;
use App\Services\ReferenceNumberService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerReturnController extends Controller
{
    public function index(Request $r)
    {
        $this->authorize('view customer return');
        $user   = Auth::user();
        $stores = $user->hasAnyRole(['superadmin', 'owner', 'finance']) ? Store::orderBy('name')->get() : collect();

        $q = CustomerReturn::with(['store', 'reason', 'creator'])
            ->when($r->store_id, fn($q) => $q->where('store_id', $r->store_id))
            ->when($r->status,   fn($q) => $q->where('status', $r->status))
            ->orderBy('created_at', 'desc');

        if (!$user->hasAnyRole(['superadmin', 'owner', 'finance', 'admin gudang'])) {
            $storeIds = $user->stores->pluck('id');
            $q->whereIn('store_id', $storeIds);
        }

        $returns = $q->paginate(20)->withQueryString();
        return view('returns.customer.index', compact('returns', 'stores'));
    }

    public function create()
    {
        $this->authorize('process customer return');
        $user    = Auth::user();
        $store   = $user->primaryStore();
        $reasons = ReturnReason::where('is_active', true)
            ->whereIn('type', ['customer', 'both'])->get();
        $recentSales = Sale::where('store_id', $store?->id)
            ->with('items.variant')
            ->orderBy('created_at', 'desc')
            ->limit(20)->get();
        $variants = ProductVariant::with(['product', 'color', 'size'])
            ->where('is_active', true)->get()
            ->map(fn($v) => [
                'id'    => $v->id,
                'sku'   => $v->sku,
                'label' => $v->product->name . ' · ' . $v->color->name . ' / ' . $v->size->name,
                'price' => $v->sellPrice(),
            ]);

        return view('returns.customer.create', compact('store', 'reasons', 'recentSales', 'variants'));
    }

    public function store(Request $r)
    {
        $this->authorize('process customer return');
        $user  = Auth::user();
        $store = $user->primaryStore();

        $r->validate([
            'return_reason_id'      => 'required|exists:return_reasons,id',
            'sale_id'               => 'nullable|exists:sales,id',
            'notes'                 => 'nullable|string|max:500',
            'items'                 => 'required|array|min:1',
            'items.*.variant_id'    => 'required|exists:product_variants,id',
            'items.*.qty'           => 'required|integer|min:1',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'items.*.condition'     => 'required|in:good,damaged',
        ]);

        $return = DB::transaction(function () use ($r, $store) {
            $return = CustomerReturn::create([
                'return_no'        => ReferenceNumberService::customerReturn(),
                'sale_id'          => $r->sale_id,
                'store_id'         => $store->id,
                'return_reason_id' => $r->return_reason_id,
                'status'           => 'processed',
                'notes'            => $r->notes,
                'processed_at'     => now(),
                'processed_by'     => Auth::id(),
                'created_by'       => Auth::id(),
            ]);

            foreach ($r->items as $row) {
                $variant  = ProductVariant::findOrFail($row['variant_id']);
                $qty      = (int) $row['qty'];
                $price    = (float) $row['unit_price'];

                CustomerReturnItem::create([
                    'customer_return_id' => $return->id,
                    'product_variant_id' => $variant->id,
                    'qty'                => $qty,
                    'unit_price'         => $price,
                    'subtotal'           => $price * $qty,
                    'condition'          => $row['condition'],
                ]);

                if ($row['condition'] === 'good') {
                    StockService::mutate(
                        $variant, 'store', $store->id, $qty, 'return',
                        "Retur konsumen {$return->return_no}",
                        CustomerReturn::class, $return->id
                    );
                }
            }

            AuditLogService::log('create', 'CustomerReturn', "Buat retur {$return->return_no}", null, null, CustomerReturn::class, $return->id);
            return $return;
        });

        return redirect()->route('returns.customer.show', $return)->with('success', "Retur {$return->return_no} berhasil diproses.");
    }

    public function show(CustomerReturn $return)
    {
        $this->authorize('view customer return');
        $return->load(['store', 'reason', 'creator', 'processor', 'sale', 'items.variant.product', 'items.variant.color', 'items.variant.size']);
        return view('returns.customer.show', compact('return'));
    }
}
