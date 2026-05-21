<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StoreCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view store');
        $user = Auth::user();

        // Determine accessible stores
        if ($user->hasRole(['superadmin', 'owner', 'admin gudang', 'finance'])) {
            $stores = Store::where('is_active', true)->orderBy('name')->get();
            $storeId = $request->store_id; // null means 'Semua Toko'
            $allowedStoreIds = null;
        } else {
            $stores = $user->stores()->where('is_active', true)->orderBy('name')->get();
            $storeId = $request->store_id;
            
            // Security: limit to accessible stores
            if ($storeId && !$stores->contains('id', $storeId)) {
                $storeId = $stores->first()?->id;
            }
            
            $allowedStoreIds = $stores->pluck('id')->toArray();
        }

        // Build customers query from Sale table
        $query = Sale::query()
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '');

        // Apply store limits
        if ($storeId) {
            $query->where('store_id', $storeId);
        } elseif ($allowedStoreIds !== null) {
            $query->whereIn('store_id', $allowedStoreIds);
        }

        // Apply search filter
        if ($request->search) {
            $term = trim($request->search);
            $query->where(function($q) use ($term) {
                $q->where('customer_name', 'like', "%{$term}%")
                  ->orWhere('customer_phone', 'like', "%{$term}%");
            });
        }

        // Paginate unique customers
        $customers = $query->select(
            'customer_name',
            'customer_phone',
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('SUM(total_amount) as total_spent'),
            DB::raw("SUM(CASE WHEN payment_status != 'lunas' THEN total_amount - amount_paid ELSE 0 END) as total_debt")
        )
        ->groupBy('customer_name', 'customer_phone')
        ->orderBy('customer_name')
        ->paginate(25)
        ->withQueryString();

        return view('store.customers.index', compact('customers', 'stores', 'storeId'));
    }

    public function show(Request $request): View
    {
        $this->authorize('view store');
        $user = Auth::user();
        
        $customerName = $request->name;
        $customerPhone = $request->phone;

        if (!$customerName) {
            abort(404, 'Pelanggan tidak ditemukan.');
        }

        // Determine accessible stores
        if ($user->hasRole(['superadmin', 'owner', 'admin gudang', 'finance'])) {
            $stores = Store::where('is_active', true)->orderBy('name')->get();
            $storeId = $request->store_id; // null means 'Semua Toko'
            $allowedStoreIds = null;
        } else {
            $stores = $user->stores()->where('is_active', true)->orderBy('name')->get();
            $storeId = $request->store_id;
            
            // Security: limit to accessible stores
            if ($storeId && !$stores->contains('id', $storeId)) {
                $storeId = $stores->first()?->id;
            }
            
            $allowedStoreIds = $stores->pluck('id')->toArray();
        }

        // Query transactions for this customer
        $query = Sale::with(['paymentMethod', 'store', 'creator'])
            ->where('customer_name', $customerName);

        if ($customerPhone) {
            $query->where('customer_phone', $customerPhone);
        } else {
            $query->where(function($q) {
                $q->whereNull('customer_phone')->orWhere('customer_phone', '');
            });
        }

        // Apply store limits
        if ($storeId) {
            $query->where('store_id', $storeId);
        } elseif ($allowedStoreIds !== null) {
            $query->whereIn('store_id', $allowedStoreIds);
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $totalTransactions = $sales->count();
        $totalSpent = $sales->sum('total_amount');
        $totalDebt = $sales->filter(fn($s) => $s->payment_status !== 'lunas')->sum(fn($s) => max(0, $s->total_amount - $s->amount_paid));
        $netSpent = $totalSpent - $totalDebt;
        $averageSpent = $totalTransactions > 0 ? $totalSpent / $totalTransactions : 0;

        return view('store.customers.show', compact(
            'customerName',
            'customerPhone',
            'sales',
            'totalTransactions',
            'totalSpent',
            'totalDebt',
            'netSpent',
            'averageSpent',
            'stores',
            'storeId'
        ));
    }
}
