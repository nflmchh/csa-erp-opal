<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CustomerReturn;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index()
    {
        $this->authorize('view finance');

        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');

        $user = auth()->user();
        $isGlobal = $user->hasGlobalFinanceAccess();

        $storeIds = [];
        $warehouseIds = [];
        if (!$isGlobal) {
            $storeIds = $user->stores()->pluck('stores.id')->toArray();
            $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();
        }

        $todaySalesQuery = Sale::whereDate('created_at', $today);
        $monthSalesQuery = Sale::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$thisMonth]);
        $totalOrdersQuery = Sale::whereDate('created_at', $today);

        $todayRefundsQuery = CustomerReturn::whereDate('created_at', $today);
        $monthRefundsQuery = CustomerReturn::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$thisMonth]);

        if (!$isGlobal) {
            if (empty($storeIds)) {
                $todaySalesQuery->whereRaw('1 = 0');
                $monthSalesQuery->whereRaw('1 = 0');
                $totalOrdersQuery->whereRaw('1 = 0');
                $todayRefundsQuery->whereRaw('1 = 0');
                $monthRefundsQuery->whereRaw('1 = 0');
            } else {
                $todaySalesQuery->whereIn('store_id', $storeIds);
                $monthSalesQuery->whereIn('store_id', $storeIds);
                $totalOrdersQuery->whereIn('store_id', $storeIds);
                $todayRefundsQuery->whereIn('store_id', $storeIds);
                $monthRefundsQuery->whereIn('store_id', $storeIds);
            }
        }

        $todaySalesGross = $todaySalesQuery->sum('total_amount');
        $monthSalesGross = $monthSalesQuery->sum('total_amount');
        
        $todayRefunds = $todayRefundsQuery->sum('refund_amount');
        $monthRefunds = $monthRefundsQuery->sum('refund_amount');

        $todaySales = max(0, $todaySalesGross - $todayRefunds);
        $monthSales = max(0, $monthSalesGross - $monthRefunds);

        $totalOrders = $totalOrdersQuery->count();

        $storeStockQuery = Stock::where('location_type', 'store')
            ->join('product_variants', 'stocks.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id');

        if (!$isGlobal) {
            if (empty($storeIds)) {
                $storeStockQuery->whereRaw('1 = 0');
            } else {
                $storeStockQuery->whereIn('stocks.location_id', $storeIds);
            }
        }

        $storeStockValue = $storeStockQuery->select(DB::raw('SUM(stocks.qty * (products.sell_price + product_variants.price_adjustment)) as value'))
            ->value('value') ?? 0;

        $warehouseStockQuery = Stock::where('location_type', 'warehouse')
            ->join('product_variants', 'stocks.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id');

        if (!$isGlobal) {
            if (empty($warehouseIds)) {
                $warehouseStockQuery->whereRaw('1 = 0');
            } else {
                $warehouseStockQuery->whereIn('stocks.location_id', $warehouseIds);
            }
        }

        $warehouseStockValue = $warehouseStockQuery->select(DB::raw('SUM(stocks.qty * (products.sell_price + product_variants.price_adjustment)) as value'))
            ->value('value') ?? 0;

        return view('finance.index', compact('todaySales', 'monthSales', 'totalOrders', 'storeStockValue', 'warehouseStockValue', 'isGlobal', 'storeIds', 'warehouseIds'));
    }

    public function stockValue(Request $r)
    {
        $this->authorize('view finance');
        $user = auth()->user();
        $isGlobal = $user->hasGlobalFinanceAccess();

        $storeIds = [];
        $warehouseIds = [];
        if (!$isGlobal) {
            $storeIds = $user->stores()->pluck('stores.id')->toArray();
            $warehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();
        }

        $stores = $isGlobal ? Store::orderBy('name')->get() : $user->stores()->orderBy('name')->get();

        $locationType = $r->location_type;
        if (!$locationType) {
            if (!$isGlobal && empty($storeIds) && !empty($warehouseIds)) {
                $locationType = 'warehouse';
            } else {
                $locationType = 'store';
            }
        }
        if (!$isGlobal) {
            if ($locationType === 'store' && empty($storeIds)) {
                $locationType = 'warehouse';
            }
            if ($locationType === 'warehouse' && empty($warehouseIds)) {
                $locationType = 'store';
            }
        }

        $locationId = $r->location_id;

        $stocksQuery = Stock::where('stocks.location_type', $locationType);
        $totalQuery = Stock::where('stocks.location_type', $locationType);

        if ($locationId) {
            if (!$isGlobal) {
                if ($locationType === 'store' && !in_array($locationId, $storeIds)) {
                    $stocksQuery->whereRaw('1 = 0');
                    $totalQuery->whereRaw('1 = 0');
                }
                if ($locationType === 'warehouse' && !in_array($locationId, $warehouseIds)) {
                    $stocksQuery->whereRaw('1 = 0');
                    $totalQuery->whereRaw('1 = 0');
                }
            }
            $stocksQuery->where('stocks.location_id', $locationId);
            $totalQuery->where('stocks.location_id', $locationId);
        } else {
            if (!$isGlobal) {
                if ($locationType === 'store') {
                    if (empty($storeIds)) {
                        $stocksQuery->whereRaw('1 = 0');
                        $totalQuery->whereRaw('1 = 0');
                    } else {
                        $stocksQuery->whereIn('stocks.location_id', $storeIds);
                        $totalQuery->whereIn('stocks.location_id', $storeIds);
                    }
                } else if ($locationType === 'warehouse') {
                    if (empty($warehouseIds)) {
                        $stocksQuery->whereRaw('1 = 0');
                        $totalQuery->whereRaw('1 = 0');
                    } else {
                        $stocksQuery->whereIn('stocks.location_id', $warehouseIds);
                        $totalQuery->whereIn('stocks.location_id', $warehouseIds);
                    }
                }
            }
        }

        $stocks = $stocksQuery->where('stocks.qty', '>', 0)
            ->join('product_variants', 'stocks.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('colors', 'product_variants.color_id', '=', 'colors.id')
            ->join('sizes', 'product_variants.size_id', '=', 'sizes.id')
            ->select(
                'stocks.id',
                'stocks.qty',
                'stocks.location_id',
                'product_variants.sku',
                'product_variants.price_adjustment',
                'products.name as product_name',
                'products.sell_price',
                'colors.name as color_name',
                'sizes.name as size_name',
                DB::raw('stocks.qty * (products.sell_price + product_variants.price_adjustment) as total_value')
            )
            ->orderByDesc('total_value')
            ->paginate(50)->withQueryString();

        $grandTotal = $totalQuery->join('product_variants', 'stocks.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(DB::raw('SUM(stocks.qty * (products.sell_price + product_variants.price_adjustment)) as value'))
            ->value('value') ?? 0;

        return view('finance.stock-value', compact('stocks', 'stores', 'locationType', 'locationId', 'grandTotal', 'isGlobal', 'storeIds', 'warehouseIds'));
    }

    public function rewards(Request $r)
    {
        $this->authorize('view finance');

        $user = auth()->user();
        $isGlobal = $user->hasGlobalFinanceAccess();

        $month = $r->input('month', now()->format('m'));
        $year = $r->input('year', now()->format('Y'));

        $stores = $isGlobal ? Store::orderBy('name')->get() : $user->stores()->orderBy('name')->get();

        $storeRewards = [];

        foreach ($stores as $store) {
            // Hitung total quantity dan total reward_store untuk bulan & tahun yang dipilih
            $salesData = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.store_id', $store->id)
                ->whereMonth('sales.created_at', $month)
                ->whereYear('sales.created_at', $year)
                ->selectRaw('SUM(sale_items.qty) as total_qty, SUM(sale_items.reward_store) as total_reward')
                ->first();

            $totalQty = $salesData->total_qty ?? 0;
            $regularReward = $salesData->total_reward ?? 0;
            $target = $store->getTargetForMonth((int) $month, (int) $year);

            $excess = 0;
            $bonus = 0;

            if ($target > 0 && $totalQty > $target) {
                $excess = $totalQty - $target;
                $bonusMultiplier = floor($excess / 1000);
                $bonus = $bonusMultiplier * 1000000;
            }

            $storeRewards[] = [
                'store' => $store,
                'target' => $target,
                'total_qty' => $totalQty,
                'excess' => $excess,
                'regular_reward' => $regularReward,
                'bonus' => $bonus,
                'total_reward' => $regularReward + $bonus,
            ];
        }

        return view('finance.rewards', compact('storeRewards', 'month', 'year'));
    }

    public function sales(Request $r)
    {
        return redirect()->route('reports.sales');
    }
    public function export()
    {
        return back()->with('warning', 'Export belum tersedia.');
    }
}
