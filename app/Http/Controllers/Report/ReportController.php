<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Shipment;
use App\Models\Stock;
use App\Models\Store;
use App\Models\Transfer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $this->authorize('view report');
        return view('reports.index');
    }

    public function sales(Request $r)
    {
        $this->authorize('view report');
        $user   = Auth::user();
        $stores = $user->hasAnyRole(['superadmin', 'owner', 'finance']) ? Store::orderBy('name')->get() : collect();

        $q = Sale::with(['store', 'paymentMethod', 'items'])
            ->when($r->store_id,  fn($q) => $q->where('store_id', $r->store_id))
            ->when($r->date_from, fn($q) => $q->whereDate('created_at', '>=', $r->date_from))
            ->when($r->date_to,   fn($q) => $q->whereDate('created_at', '<=', $r->date_to))
            ->orderBy('created_at', 'desc');

        if (!$user->hasAnyRole(['superadmin', 'owner', 'finance'])) {
            $storeIds = $user->stores->pluck('id');
            $q->whereIn('store_id', $storeIds);
        }

        $sales       = $q->paginate(30)->withQueryString();
        $totalSales  = $q->toBase()->sum('total_amount');
        $totalOrders = $q->toBase()->count();

        return view('reports.sales', compact('sales', 'stores', 'totalSales', 'totalOrders'));
    }

    public function stock(Request $r)
    {
        $this->authorize('view report');
        $warehouses = Warehouse::orderBy('name')->get();
        $stores     = Store::orderBy('name')->get();

        $locationType = $r->location_type ?? 'warehouse';
        $locationId   = $r->location_id;

        $q = Stock::with(['variant.product.brand', 'variant.color', 'variant.size'])
            ->where('qty', '>', 0)
            ->where('location_type', $locationType)
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->orderBy('qty', 'desc');

        $stocks     = $q->paginate(50)->withQueryString();
        $totalQty   = $q->toBase()->sum('qty');

        return view('reports.stock', compact('stocks', 'warehouses', 'stores', 'locationType', 'locationId', 'totalQty'));
    }

    public function shipment(Request $r)
    {
        $this->authorize('view report');
        $warehouses = Warehouse::orderBy('name')->get();
        $stores     = Store::orderBy('name')->get();

        $shipments = Shipment::with(['warehouse', 'store', 'items'])
            ->when($r->warehouse_id, fn($q) => $q->where('warehouse_id', $r->warehouse_id))
            ->when($r->store_id,     fn($q) => $q->where('store_id', $r->store_id))
            ->when($r->status,       fn($q) => $q->where('status', $r->status))
            ->when($r->date_from,    fn($q) => $q->whereDate('created_at', '>=', $r->date_from))
            ->when($r->date_to,      fn($q) => $q->whereDate('created_at', '<=', $r->date_to))
            ->orderBy('created_at', 'desc')
            ->paginate(30)->withQueryString();

        return view('reports.shipment', compact('shipments', 'warehouses', 'stores'));
    }

    public function transfer(Request $r)
    {
        $this->authorize('view report');
        $stores = Store::orderBy('name')->get();

        $transfers = Transfer::with(['fromStore', 'toStore', 'items'])
            ->when($r->from_store_id, fn($q) => $q->where('from_store_id', $r->from_store_id))
            ->when($r->to_store_id,   fn($q) => $q->where('to_store_id', $r->to_store_id))
            ->when($r->status,        fn($q) => $q->where('status', $r->status))
            ->when($r->date_from,     fn($q) => $q->whereDate('created_at', '>=', $r->date_from))
            ->when($r->date_to,       fn($q) => $q->whereDate('created_at', '<=', $r->date_to))
            ->orderBy('created_at', 'desc')
            ->paginate(30)->withQueryString();

        return view('reports.transfer', compact('transfers', 'stores'));
    }

    public function expenses(Request $request)
    {
        $user = auth()->user();
        $query = \App\Models\Expense::with(['store', 'warehouse', 'creator']);

        // 1. Filter Akses Role (Wajib, tidak bisa di-override user)
        if ($user->hasAnyRole(['superadmin', 'owner'])) {
            if ($request->filled('source_filter')) {
                $source = explode('_', $request->source_filter); 
                if ($source[0] === 'store') {
                    $query->where('store_id', $source[1]);
                } elseif ($source[0] === 'warehouse') {
                    $query->where('warehouse_id', $source[1]);
                }
            }
        } 
        elseif ($user->hasRole('kepala toko')) {
            // FIX BUG: Ambil array ID Toko dari relasi stores()
            $storeIds = $user->stores()->pluck('stores.id');
            $query->whereIn('store_id', $storeIds);
        } 
        elseif ($user->hasRole('admin gudang')) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id');
            $query->where(function ($q) use ($warehouseIds) {
                $q->whereIn('warehouse_id', $warehouseIds)
                  ->orWhereNotNull('store_id');
            });
        } else {
            abort(403, 'Akses ditolak.');
        }

        // 2. Filter Jenis Pengeluaran
        if ($request->filled('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }

        // 3. Filter Tanggal (Dari - Sampai)
        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        // 4. Hitung Summary (Total Keseluruhan berdasarkan filter, BUKAN cuma yang tampil di 1 halaman)
        $totalAmount = $query->sum('amount');
        $totalTransactions = $query->count();

        // 5. Eksekusi query dengan pagination
        $expenses = $query->latest('expense_date')->paginate(20)->withQueryString();

        return view('reports.expenses', compact('expenses', 'totalAmount', 'totalTransactions'));
    }

    public function return(Request $r)
    {
        $this->authorize('view report');
        return redirect()->route('reports.sales');
    }

    public function exportPdf()   { return back()->with('warning', 'Export PDF belum tersedia.'); }
    public function exportExcel() { return back()->with('warning', 'Export Excel belum tersedia.'); }
}
