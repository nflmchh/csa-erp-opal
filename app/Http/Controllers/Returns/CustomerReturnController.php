<?php

namespace App\Http\Controllers\Returns;

use App\Http\Controllers\Controller;
use App\Models\CustomerReturn;
use App\Models\CustomerReturnItem;
use App\Models\ProductVariant;
use App\Models\CashSession;
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
        $user = Auth::user();
        $stores = $user->hasAnyRole(['superadmin', 'owner', 'finance']) ? Store::orderBy('name')->get() : collect();

        $q = CustomerReturn::with(['store', 'reason', 'creator'])
            ->when($r->store_id, fn($q) => $q->where('store_id', $r->store_id))
            ->when($r->status, fn($q) => $q->where('status', $r->status))
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
        $user = Auth::user();
        $store = $user->primaryStore();
        $reasons = ReturnReason::where('is_active', true)
            ->whereIn('type', ['customer', 'both'])->get();
        $recentSales = Sale::where('store_id', $store?->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)->get();

        return view('returns.customer.create', compact('store', 'reasons', 'recentSales'));
    }

    public function store(Request $r)
    {
        $this->authorize('process customer return');
        $user = Auth::user();
        $store = $user->primaryStore();

        $r->validate([
            'return_reason_id' => 'required|exists:return_reasons,id',
            'sale_id' => 'nullable|exists:sales,id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.condition' => 'required|in:good,damaged',
        ]);

        $activeSession = CashSession::where('user_id', Auth::id())->where('status', 'open')->first();
        if (!$activeSession) {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses retur! Anda harus membuka Sesi Kasir (Cash Session) terlebih dahulu untuk memproses refund.');
        }

        $return = DB::transaction(function () use ($r, $store, $activeSession) {
            $totalRefund = 0;
            foreach ($r->items as $row) {
                $totalRefund += (float) $row['unit_price'] * (int) $row['qty'];
            }

            $return = CustomerReturn::create([
                'return_no' => ReferenceNumberService::customerReturn(),
                'sale_id' => $r->sale_id,
                'store_id' => $store->id,
                'return_reason_id' => $r->return_reason_id,
                'status' => 'processed',
                'notes' => $r->notes,
                'processed_at' => now(),
                'processed_by' => Auth::id(),
                'created_by' => Auth::id(),
                'cash_session_id' => $activeSession->id,
                'refund_amount' => $totalRefund,
            ]);

            $activeSession->increment('refund_amount', $totalRefund);

            foreach ($r->items as $row) {
                $variant = ProductVariant::findOrFail($row['variant_id']);
                $qty = (int) $row['qty'];
                $price = (float) $row['unit_price'];

                CustomerReturnItem::create([
                    'customer_return_id' => $return->id,
                    'product_variant_id' => $variant->id,
                    'qty' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $price * $qty,
                    'condition' => $row['condition'],
                ]);

                if ($row['condition'] === 'good') {
                    StockService::mutate(
                        $variant,
                        'store',
                        $store->id,
                        $qty,
                        'return',
                        "Retur konsumen {$return->return_no}",
                        CustomerReturn::class,
                        $return->id
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

    public function searchSale(Request $r)
    {
        $this->authorize('process customer return');
        $saleNo = $r->input('sale_no');

        if (!$saleNo) {
            return response()->json(['error' => 'Nomor struk tidak diberikan'], 400);
        }

        $user = Auth::user();
        $store = $user->primaryStore();

        if (!$store) {
            return response()->json(['error' => 'Akun Anda belum ditugaskan ke toko manapun.'], 403);
        }

        $sale = Sale::where('sale_no', $saleNo)
            ->where('store_id', $store->id)
            ->with(['items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->first();

        if (!$sale) {
            return response()->json(['error' => 'Transaksi tidak ditemukan atau tidak berasal dari toko ini'], 404);
        }

        $items = $sale->items->map(function ($item) {
            $v = $item->variant;
            return [
                'id'    => $v->id,
                'sku'   => $v->sku,
                'label' => $v->product->name . ' · ' . $v->color->name . ' / ' . $v->size->name,
                'price' => (float) $item->unit_price,
                'qty'   => $item->qty,
            ];
        });

        return response()->json([
            'sale_id' => $sale->id,
            'sale_no' => $sale->sale_no,
            'date'    => $sale->created_at->format('d/m/Y H:i'),
            'items'   => $items
        ]);
    }

    public function searchSales(Request $r)
    {
        $this->authorize('process customer return');
        $user  = Auth::user();
        $store = $user->primaryStore();

        if (!$store) {
            return response()->json([]);
        }

        $q = $r->input('q', '');

        $sales = Sale::where('store_id', $store->id)
            ->when($q, fn($query) => $query->where('sale_no', 'like', "%{$q}%"))
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get(['id', 'sale_no', 'created_at', 'total_amount']);

        return response()->json($sales->map(fn($s) => [
            'id'     => $s->id,
            'sale_no'=> $s->sale_no,
            'date'   => $s->created_at->format('d/m/Y H:i'),
            'total'  => $s->total_amount,
        ]));
    }
}
