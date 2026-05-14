<?php

namespace App\Http\Controllers\Export;

use App\Exports\ExpensesExport;
use App\Exports\RewardsExport;
use App\Exports\SalesExport;
use App\Exports\ShipmentExport;
use App\Exports\StockExport;
use App\Exports\TransferExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\Shipment;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\Store;
use App\Models\Transfer;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // LAPORAN PENJUALAN
    // ─────────────────────────────────────────────────────────────

    public function salesPdf(Request $request)
    {
        $this->authorize('export report');

        $user     = auth()->user();
        $isGlobal = $user->hasGlobalFinanceAccess() || $user->hasRole('superadmin') || $user->hasRole('owner');

        $query = Sale::with(['store', 'paymentMethod', 'items.variant.product', 'items.variant.color', 'items.variant.size'])
            ->when($request->store_id,  fn($q) => $q->where('store_id', $request->store_id))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to));

        if (!$isGlobal) {
            $storeIds = $user->stores()->pluck('stores.id')->toArray();
            if (empty($storeIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('store_id', $storeIds);
            }
        }

        $sales       = $query->orderBy('created_at', 'desc')->limit(500)->get();
        $totalSales  = $sales->sum('total_amount');
        $totalOrders = $sales->count();
        $store       = $request->store_id ? Store::find($request->store_id) : null;

        $pdf = Pdf::loadView('exports.pdf.sales', compact('sales', 'totalSales', 'totalOrders', 'store', 'request'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-penjualan-' . now()->format('Ymd-His') . '.pdf');
    }

    public function salesExcel(Request $request)
    {
        $this->authorize('export report');

        return Excel::download(
            new SalesExport($request->store_id, $request->date_from, $request->date_to),
            'laporan-penjualan-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function salesCsv(Request $request)
    {
        $this->authorize('export report');

        $user     = auth()->user();
        $isGlobal = $user->hasGlobalFinanceAccess() || $user->hasRole('superadmin') || $user->hasRole('owner');

        $query = Sale::with(['store', 'paymentMethod', 'items.variant.product', 'creator'])
            ->when($request->store_id,  fn($q) => $q->where('store_id', $request->store_id))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to));

        if (!$isGlobal) {
            $storeIds = $user->stores()->pluck('stores.id')->toArray();
            if (empty($storeIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('store_id', $storeIds);
            }
        }

        $sales    = $query->orderBy('created_at', 'desc')->get();
        $filename = 'laporan-penjualan-' . now()->format('Ymd-His') . '.csv';
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];

        $callback = function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No. Penjualan', 'Toko', 'Metode Bayar', 'Kasir', 'Total Transaksi', 'Tanggal', 'Item', 'SKU/Variant', 'Qty', 'Harga Satuan', 'Subtotal Item']);
            foreach ($sales as $s) {
                foreach ($s->items as $idx => $item) {
                    if ($idx === 0) {
                        fputcsv($handle, [
                            $s->sale_no,
                            $s->store->name,
                            $s->paymentMethod?->name ?? '-',
                            $s->creator?->name ?? '-',
                            $s->total_amount,
                            $s->created_at->format('d/m/Y H:i'),
                            $item->variant->product->name,
                            $item->variant->sku,
                            $item->qty,
                            $item->unit_price,
                            $item->subtotal
                        ]);
                    } else {
                        fputcsv($handle, [
                            '', '', '', '', '', '',
                            $item->variant->product->name,
                            $item->variant->sku,
                            $item->qty,
                            $item->unit_price,
                            $item->subtotal
                        ]);
                    }
                }
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─────────────────────────────────────────────────────────────
    // LAPORAN STOK
    // ─────────────────────────────────────────────────────────────

    public function stockPdf(Request $request)
    {
        $this->authorize('export report');

        $locationType = $request->location_type ?? 'warehouse';
        $locationId   = $request->location_id;

        $stocks = Stock::with(['variant.product.brand', 'variant.color', 'variant.size'])
            ->where('location_type', $locationType)
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->where('qty', '>', 0)
            ->orderByDesc('qty')
            ->limit(500)
            ->get();

        $totalQty = $stocks->sum('qty');
        $location = $locationId
            ? ($locationType === 'warehouse' ? Warehouse::find($locationId) : Store::find($locationId))
            : null;

        $pdf = Pdf::loadView('exports.pdf.stock', compact('stocks', 'totalQty', 'location', 'locationType'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-stok-' . now()->format('Ymd-His') . '.pdf');
    }

    public function stockExcel(Request $request)
    {
        $this->authorize('export report');

        return Excel::download(
            new StockExport($request->location_type ?? 'warehouse', $request->location_id),
            'laporan-stok-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function stockCsv(Request $request)
    {
        $this->authorize('export report');

        $stocks = Stock::with(['variant.product.brand', 'variant.color', 'variant.size'])
            ->where('location_type', $request->location_type ?? 'warehouse')
            ->when($request->location_id, fn($q) => $q->where('location_id', $request->location_id))
            ->where('qty', '>', 0)
            ->orderByDesc('qty')
            ->get();

        $filename = 'laporan-stok-' . now()->format('Ymd-His') . '.csv';
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];

        $callback = function () use ($stocks) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['SKU', 'Produk', 'Brand', 'Warna', 'Ukuran', 'Tipe Lokasi', 'ID Lokasi', 'Qty']);
            foreach ($stocks as $s) {
                fputcsv($handle, [
                    $s->variant->sku, $s->variant->product->name, $s->variant->product->brand?->name ?? '-',
                    $s->variant->color->name, $s->variant->size->name, $s->location_type, $s->location_id, $s->qty,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─────────────────────────────────────────────────────────────
    // LAPORAN PENGELUARAN
    // ─────────────────────────────────────────────────────────────

    public function expensesPdf(Request $request)
    {
        $this->authorize('export report');

        $user  = auth()->user();
        $query = Expense::with(['store', 'warehouse', 'creator']);

        if ($user->hasAnyRole(['superadmin', 'owner'])) {
            if ($request->filled('source_filter')) {
                $source = explode('_', $request->source_filter);
                if ($source[0] === 'store') {
                    $query->where('store_id', $source[1]);
                } elseif ($source[0] === 'warehouse') {
                    $query->where('warehouse_id', $source[1]);
                }
            }
        } elseif ($user->hasRole('kepala toko')) {
            $storeIds = $user->stores()->pluck('stores.id');
            $query->whereIn('store_id', $storeIds);
        } elseif ($user->hasRole('admin gudang')) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id');
            $query->whereIn('warehouse_id', $warehouseIds);
        } else {
            abort(403);
        }

        if ($request->filled('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        $expenses    = $query->latest('expense_date')->limit(500)->get();
        $totalAmount = $expenses->sum('amount');

        // Build source label for display
        $sourceFilter = null;
        if ($request->filled('source_filter')) {
            $parts = explode('_', $request->source_filter, 2);
            if ($parts[0] === 'store') {
                $s = Store::find($parts[1]);
                $sourceFilter = 'Toko: ' . ($s?->name ?? $parts[1]);
            } elseif ($parts[0] === 'warehouse') {
                $w = Warehouse::find($parts[1]);
                $sourceFilter = 'Gudang: ' . ($w?->name ?? $parts[1]);
            }
        }

        $expenseType = $request->expense_type;
        $dateFrom    = $request->date_from;
        $dateTo      = $request->date_to;

        $pdf = Pdf::loadView('exports.pdf.expenses', compact(
            'expenses', 'totalAmount', 'sourceFilter', 'expenseType', 'dateFrom', 'dateTo'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('laporan-pengeluaran-' . now()->format('Ymd-His') . '.pdf');
    }

    public function expensesExcel(Request $request)
    {
        $this->authorize('export report');

        return Excel::download(
            new ExpensesExport(
                $request->source_filter,
                $request->expense_type,
                $request->date_from,
                $request->date_to
            ),
            'laporan-pengeluaran-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    // ─────────────────────────────────────────────────────────────
    // LAPORAN PENGIRIMAN
    // ─────────────────────────────────────────────────────────────

    public function shipmentPdf(Request $request)
    {
        $this->authorize('export report');

        $shipments = Shipment::with(['warehouse', 'store', 'items'])
            ->when($request->warehouse_id, fn($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->store_id,     fn($q) => $q->where('store_id', $request->store_id))
            ->when($request->status,       fn($q) => $q->where('status', $request->status))
            ->when($request->date_from,    fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,      fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        $warehouse = $request->warehouse_id ? Warehouse::find($request->warehouse_id) : null;
        $store     = $request->store_id ? Store::find($request->store_id) : null;

        $pdf = Pdf::loadView('exports.pdf.shipment', compact('shipments', 'warehouse', 'store', 'request'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-pengiriman-' . now()->format('Ymd-His') . '.pdf');
    }

    public function shipmentExcel(Request $request)
    {
        $this->authorize('export report');

        return Excel::download(
            new ShipmentExport(
                $request->warehouse_id,
                $request->store_id,
                $request->status,
                $request->date_from,
                $request->date_to
            ),
            'laporan-pengiriman-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    // ─────────────────────────────────────────────────────────────
    // LAPORAN TRANSFER TOKO
    // ─────────────────────────────────────────────────────────────

    public function transferPdf(Request $request)
    {
        $this->authorize('export report');

        $transfers = Transfer::with(['fromStore', 'toStore', 'items'])
            ->when($request->from_store_id, fn($q) => $q->where('from_store_id', $request->from_store_id))
            ->when($request->to_store_id,   fn($q) => $q->where('to_store_id', $request->to_store_id))
            ->when($request->status,        fn($q) => $q->where('status', $request->status))
            ->when($request->date_from,     fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,       fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        $fromStore = $request->from_store_id ? Store::find($request->from_store_id) : null;
        $toStore   = $request->to_store_id   ? Store::find($request->to_store_id)   : null;

        $pdf = Pdf::loadView('exports.pdf.transfer', compact('transfers', 'fromStore', 'toStore', 'request'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-transfer-toko-' . now()->format('Ymd-His') . '.pdf');
    }

    public function transferExcel(Request $request)
    {
        $this->authorize('export report');

        return Excel::download(
            new TransferExport(
                $request->from_store_id,
                $request->to_store_id,
                $request->status,
                $request->date_from,
                $request->date_to
            ),
            'laporan-transfer-toko-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    // ─────────────────────────────────────────────────────────────
    // LAPORAN REWARD & BONUS TOKO
    // ─────────────────────────────────────────────────────────────

    public function rewardsPdf(Request $request)
    {
        $this->authorize('view finance');

        $user     = auth()->user();
        $isGlobal = $user->hasGlobalFinanceAccess();

        $month  = $request->input('month', now()->format('m'));
        $year   = $request->input('year', now()->format('Y'));
        $stores = $isGlobal ? Store::orderBy('name')->get() : $user->stores()->orderBy('name')->get();

        $storeRewards = [];
        foreach ($stores as $store) {
            $salesData = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.store_id', $store->id)
                ->whereMonth('sales.created_at', $month)
                ->whereYear('sales.created_at', $year)
                ->selectRaw('SUM(sale_items.qty) as total_qty, SUM(sale_items.reward_store) as total_reward')
                ->first();

            $totalQty      = $salesData->total_qty ?? 0;
            $regularReward = $salesData->total_reward ?? 0;
            $target        = $store->getTargetForMonth((int) $month, (int) $year);
            $excess        = 0;
            $bonus         = 0;

            if ($target > 0 && $totalQty > $target) {
                $excess           = $totalQty - $target;
                $bonusMultiplier  = floor($excess / 1000);
                $bonus            = $bonusMultiplier * 1000000;
            }

            $storeRewards[] = [
                'store'          => $store,
                'target'         => $target,
                'total_qty'      => $totalQty,
                'excess'         => $excess,
                'regular_reward' => $regularReward,
                'bonus'          => $bonus,
                'total_reward'   => $regularReward + $bonus,
            ];
        }

        $pdf = Pdf::loadView('exports.pdf.rewards', compact('storeRewards', 'month', 'year'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-reward-bonus-' . now()->format('Ymd-His') . '.pdf');
    }

    public function rewardsExcel(Request $request)
    {
        $this->authorize('view finance');

        $user     = auth()->user();
        $isGlobal = $user->hasGlobalFinanceAccess();

        $month  = $request->input('month', now()->format('m'));
        $year   = $request->input('year', now()->format('Y'));
        $stores = $isGlobal ? Store::orderBy('name')->get() : $user->stores()->orderBy('name')->get();

        $storeRewards = [];
        foreach ($stores as $store) {
            $salesData = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.store_id', $store->id)
                ->whereMonth('sales.created_at', $month)
                ->whereYear('sales.created_at', $year)
                ->selectRaw('SUM(sale_items.qty) as total_qty, SUM(sale_items.reward_store) as total_reward')
                ->first();

            $totalQty      = $salesData->total_qty ?? 0;
            $regularReward = $salesData->total_reward ?? 0;
            $target        = $store->getTargetForMonth((int) $month, (int) $year);
            $excess        = 0;
            $bonus         = 0;

            if ($target > 0 && $totalQty > $target) {
                $excess          = $totalQty - $target;
                $bonusMultiplier = floor($excess / 1000);
                $bonus           = $bonusMultiplier * 1000000;
            }

            $storeRewards[] = [
                'store'          => $store,
                'target'         => $target,
                'total_qty'      => $totalQty,
                'excess'         => $excess,
                'regular_reward' => $regularReward,
                'bonus'          => $bonus,
                'total_reward'   => $regularReward + $bonus,
            ];
        }

        return Excel::download(
            new RewardsExport($storeRewards),
            'laporan-reward-bonus-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}
