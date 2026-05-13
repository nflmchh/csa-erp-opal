<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
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

        $todaySales = Sale::whereDate('created_at', $today)->sum('total_amount');
        $monthSales = Sale::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$thisMonth])->sum('total_amount');
        $totalOrders = Sale::whereDate('created_at', $today)->count();

        $storeStockValue = Stock::where('location_type', 'store')
            ->join('product_variants', 'stocks.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(DB::raw('SUM(stocks.qty * (products.sell_price + product_variants.price_adjustment)) as value'))
            ->value('value') ?? 0;

        $warehouseStockValue = Stock::where('location_type', 'warehouse')
            ->join('product_variants', 'stocks.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(DB::raw('SUM(stocks.qty * (products.sell_price + product_variants.price_adjustment)) as value'))
            ->value('value') ?? 0;

        return view('finance.index', compact('todaySales', 'monthSales', 'totalOrders', 'storeStockValue', 'warehouseStockValue'));
    }

    public function stockValue(Request $r)
    {
        $this->authorize('view finance');
        $stores = Store::orderBy('name')->get();

        $locationType = $r->location_type ?? 'store';
        $locationId = $r->location_id;

        $stocks = Stock::where('stocks.location_type', $locationType)
            ->when($locationId, fn($q) => $q->where('stocks.location_id', $locationId))
            ->where('stocks.qty', '>', 0)
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

        $grandTotal = Stock::where('stocks.location_type', $locationType)
            ->when($locationId, fn($q) => $q->where('stocks.location_id', $locationId))
            ->join('product_variants', 'stocks.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select(DB::raw('SUM(stocks.qty * (products.sell_price + product_variants.price_adjustment)) as value'))
            ->value('value') ?? 0;

        return view('finance.stock-value', compact('stocks', 'stores', 'locationType', 'locationId', 'grandTotal'));
    }

    public function rewards(Request $r)
    {
        $this->authorize('view finance');

        $month = $r->input('month', now()->format('m'));
        $year = $r->input('year', now()->format('Y'));

        $stores = Store::orderBy('name')->get();

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
