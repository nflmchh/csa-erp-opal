<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Services\AuditLogService;
use App\Services\ReferenceNumberService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class POSController extends Controller
{
    public function index()
    {
        $this->authorize('access pos');
        $user = \Illuminate\Support\Facades\Auth::user();
        $session = \App\Models\CashSession::where('user_id', $user->id)->where('status', 'open')->first();

        if (!$session) {
            return redirect()->route('pos.session.index')->with('warning', 'Buka sesi kasir terlebih dahulu.');
        }

        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();
        $store = $session->store;

        // TAMBAHKAN 'product.images' PADA QUERY DI BAWAH
        $catalog = \App\Models\ProductVariant::with(['product.brand', 'color', 'size', 'product.images'])
            ->where('is_active', true)
            ->whereHas('product', fn($q) => $q->where('is_active', true))
            ->get()
            ->map(function ($v) use ($store) {
                $stockRecord = \App\Models\Stock::where('product_variant_id', $v->id)
                    ->where('location_type', 'store')
                    ->where('location_id', $store->id)
                    ->first();
                
                $stock = $stockRecord ? $stockRecord->qty : 0;

                // AMBIL DATA GAMBAR (Gambar utama atau gambar pertama)
                $image = $v->product->images->where('is_primary', true)->first() ?? $v->product->images->first();
                $imageUrl = $image ? asset('storage/' . $image->path) : 'https://via.placeholder.com/300x300.png?text=No+Image';

                return [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'name' => $v->product->name . ' · ' . $v->color->name . ' / ' . $v->size->name,
                    'price' => $v->sellPrice(),
                    'price_formatted' => 'Rp ' . number_format($v->sellPrice(), 0, ',', '.'),
                    'stock' => $stock,
                    'image' => $imageUrl, // <- Variabel gambar dikirim ke tampilan
                ];
            });

        return view('pos.index', compact('session', 'store', 'paymentMethods', 'catalog'));
    }

    public function exportReport(Request $r)
    {
        $this->authorize('view pos');
        $user = Auth::user();
        $store = $user->primaryStore(); // Laporan dibatasi per toko kasir

        $period = $r->input('period', 'today');
        $format = $r->input('format', 'pdf');

        $query = \App\Models\Sale::with(['paymentMethod', 'items'])
            ->where('store_id', $store->id);

        if ($period == 'today') {
            $dateFrom = now()->startOfDay();
            $dateTo = now()->endOfDay();
            $query->whereDate('created_at', now());
            $title = "Harian (" . now()->format('d/m/Y') . ")";
        } elseif ($period == 'weekly') {
            $dateFrom = now()->startOfWeek();
            $dateTo = now()->endOfWeek();
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            $title = "Mingguan (" . $dateFrom->format('d/m') . " - " . $dateTo->format('d/m') . ")";
        } elseif ($period == 'monthly') {
            $dateFrom = now()->startOfMonth();
            $dateTo = now()->endOfMonth();
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            $title = "Bulanan (" . now()->format('F Y') . ")";
        } else {
            $dateFrom = now()->startOfDay();
            $dateTo = now()->endOfDay();
            $query->whereDate('created_at', now());
            $title = "Harian (" . now()->format('d/m/Y') . ")";
        }

        if (in_array($format, ['excel', 'csv'])) {
            $export = new \App\Exports\SalesExport($store->id, $dateFrom->format('Y-m-d H:i:s'), $dateTo->format('Y-m-d H:i:s'));
            $filename = "Laporan_Penjualan_{$period}_" . now()->format('Ymd');
            if ($format == 'excel') {
                return \Maatwebsite\Excel\Facades\Excel::download($export, $filename . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
            } else {
                return \Maatwebsite\Excel\Facades\Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
            }
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_revenue' => $sales->sum('total_amount'),
            'total_items' => $sales->sum(fn($s) => $s->items->sum('qty')),
            'count' => $sales->count()
        ];

        $pdf = Pdf::loadView('pos.reports.sales_pdf', compact('sales', 'store', 'title', 'summary'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Laporan_Penjualan_{$period}_" . now()->format('Ymd') . ".pdf");
    }

    public function processSale(Request $r)
    {
        $this->authorize('process sale');
        $user = Auth::user();
        $session = CashSession::where('user_id', $user->id)->where('status', 'open')->firstOrFail();

        $r->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount_paid' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:300',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $discountAmount = (float) ($r->discount_amount ?? 0);
        if ($discountAmount > 0) {
            $this->authorize('apply discount');
        }

        try {
            $sale = DB::transaction(function () use ($r, $session, $discountAmount) {
                $subtotal = 0;
                $itemsData = [];

                foreach ($r->items as $row) {
                    $variant = ProductVariant::with('product')->findOrFail($row['variant_id']);
                    $qty = (int) $row['qty'];
                    $unitPrice = (float) $row['unit_price'];
                    $lineTotal = $unitPrice * $qty;

                    $stock = Stock::where('product_variant_id', $variant->id)
                        ->where('location_type', 'store')
                        ->where('location_id', $session->store_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$stock || $stock->qty < $qty) {
                        throw new \RuntimeException("Stok tidak cukup untuk SKU {$variant->sku}. Tersedia: " . ($stock?->qty ?? 0));
                    }

                    $subtotal += $lineTotal;
                    $itemsData[] = compact('variant', 'qty', 'unitPrice', 'lineTotal');
                }

                $totalAmount = max(0, $subtotal - $discountAmount);
                $amountPaid = (float) $r->amount_paid;
                $changeAmount = max(0, $amountPaid - $totalAmount);

                if ($amountPaid < $totalAmount) {
                    throw new \RuntimeException("Jumlah pembayaran kurang dari total transaksi.");
                }

                $sale = Sale::create([
                    'sale_no' => ReferenceNumberService::sale(),
                    'cash_session_id' => $session->id,
                    'store_id' => $session->store_id,
                    'payment_method_id' => $r->payment_method_id,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'amount_paid' => $amountPaid,
                    'change_amount' => $changeAmount,
                    'notes' => $r->notes,
                    'created_by' => Auth::id(),
                ]);

                foreach ($itemsData as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_variant_id' => $item['variant']->id,
                        'qty' => $item['qty'],
                        'unit_price' => $item['unitPrice'],
                        'subtotal' => $item['lineTotal'],
                        'reward_store' => ($item['variant']->product->reward_store ?? 500) * $item['qty'],
                        'reward_owner' => ($item['variant']->product->reward_owner ?? 4500) * $item['qty'],
                    ]);

                    StockService::mutate(
                        $item['variant'],
                        'store',
                        $session->store_id,
                        -$item['qty'],
                        'sale',
                        "Penjualan {$sale->sale_no}",
                        Sale::class,
                        $sale->id
                    );
                }

                AuditLogService::log('create', 'Sale', "Transaksi {$sale->sale_no} Rp " . number_format($totalAmount, 0, ',', '.'), null, null, Sale::class, $sale->id);

                return $sale;
            });
            // --- PERUBAHAN BARU: Cek jika request dari AJAX (Pop-up) ---
            if ($r->ajax() || $r->wantsJson()) {
                $sale->load(['store', 'paymentMethod', 'creator', 'items.variant.product']);
                $receiptHtml = view('pos.partials.receipt_html', compact('sale'))->render();

                return response()->json([
                    'success' => true,
                    'sale' => $sale,
                    'html' => $receiptHtml
                ]);
            }
            return redirect()->route('pos.receipt', $sale)->with('success', "Transaksi {$sale->sale_no} berhasil.");
        } catch (\RuntimeException $e) {
            if ($r->ajax() || $r->wantsJson())
                return response()->json(['success' => false, 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function receipt(Sale $sale)
    {
        $this->authorize('access pos');
        $sale->load(['store', 'paymentMethod', 'creator', 'items.variant.product', 'items.variant.color', 'items.variant.size']);

        if (request()->ajax() || request()->wantsJson()) {
            $receiptHtml = view('pos.partials.receipt_html', compact('sale'))->render();
            return response()->json([
                'success' => true,
                'sale' => $sale,
                'html' => $receiptHtml
            ]);
        }

        return view('pos.receipt', compact('sale'));
    }

    public function history(Request $r)
    {
        $this->authorize('view pos');
        $user = Auth::user();
        $stores = collect();

        if ($user->hasAnyRole(['superadmin', 'owner', 'finance', 'admin gudang'])) {
            $stores = \App\Models\Store::orderBy('name')->get();
        }

        $q = Sale::with(['store', 'paymentMethod', 'creator', 'items'])
            ->when($r->store_id, fn($q) => $q->where('store_id', $r->store_id))
            ->when($r->date_from, fn($q) => $q->whereDate('created_at', '>=', $r->date_from))
            ->when($r->date_to, fn($q) => $q->whereDate('created_at', '<=', $r->date_to))
            ->orderBy('created_at', 'desc');

        if (!$user->hasAnyRole(['superadmin', 'owner', 'finance', 'admin gudang'])) {
            $storeIds = $user->stores->pluck('id');
            $q->whereIn('store_id', $storeIds);
        }

        $sales = $q->paginate(25)->withQueryString();

        return view('pos.history', compact('sales', 'stores'));
    }

    public function searchProduct(Request $r)
    {
        $storeId = (int) $r->store_id;
        $term = trim($r->q ?? '');

        if (strlen($term) < 1 || !$storeId) {
            return response()->json([]);
        }

        $variants = ProductVariant::with(['product.brand', 'color', 'size'])
            ->where('is_active', true)
            ->whereHas('product', fn($q) => $q->where('is_active', true))
            ->where(
                fn($q) => $q
                    ->where('sku', 'like', "%{$term}%")
                    ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$term}%"))
            )
            ->limit(12)
            ->get()
            ->map(function ($v) use ($storeId) {
                $stockRecord = Stock::where('product_variant_id', $v->id)
                    ->where('location_type', 'store')
                    ->where('location_id', $storeId)
                    ->first();
                
                $stock = $stockRecord ? $stockRecord->qty : 0;

                return [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'name' => $v->product->name . ' · ' . $v->color->name . ' / ' . $v->size->name,
                    'price' => $v->sellPrice(),
                    'price_formatted' => 'Rp ' . number_format($v->sellPrice(), 0, ',', '.'),
                    'stock' => $stock,
                ];
            });

        return response()->json($variants);
    }

    public function getStatus($id)
    {
        return response()->json(['status' => 'ok']);
    }
    public function poll()
    {
        return response()->json(['ok' => true]);
    }
}
