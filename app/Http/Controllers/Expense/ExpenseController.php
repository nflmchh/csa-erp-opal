<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Store;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $query = \App\Models\Expense::with(['store', 'warehouse', 'creator']);

        if ($user->hasAnyRole(['superadmin', 'owner'])) {
            // Bisa melihat semua
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
        }

        $expenses = $query->latest('expense_date')->paginate(10);
        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $user = Auth::user();
        $stores = [];
        $warehouses = [];

        if ($user->hasAnyRole(['superadmin', 'owner'])) {
            $stores = Store::all();
            $warehouses = Warehouse::all();
        }

        return view('expenses.create', compact('stores', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'expense_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'receipt' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
            'source_type' => 'required_if:user_role,superadmin,owner|in:store,warehouse',
            'source_id' => 'required_if:user_role,superadmin,owner',
        ]);

        $user = Auth::user();
        $data = $request->only(['title', 'description', 'expense_type', 'amount', 'expense_date']);
        $data['created_by'] = $user->id;

        // --- Logika Penentuan Source (Toko / Gudang) ---
        if ($user->hasRole('kepala toko')) {
            // FIX BUG: Ambil toko pertama yang ditugaskan ke Kepala Toko ini
            $assignedStore = $user->stores()->first();
            
            if (!$assignedStore) {
                return redirect()->back()->with('error', 'Gagal! Anda belum ditugaskan ke toko manapun. Hubungi Superadmin.');
            }
            $data['store_id'] = $assignedStore->id; 
            
        } elseif ($user->hasRole('admin gudang')) {
            $assignedWarehouse = $user->warehouses()->first();
            if (!$assignedWarehouse) {
                return redirect()->back()->with('error', 'Gagal! Anda belum ditugaskan ke gudang manapun.');
            }
            $data['warehouse_id'] = $assignedWarehouse->id; 
        } else {
            if ($request->source_type === 'store') {
                $data['store_id'] = $request->source_id;
            } else {
                $data['warehouse_id'] = $request->source_id;
            }
        }

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('expenses', 'public');
        }

        Expense::create($data);
        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dicatat!');
    }

    // --- TAMBAHAN BARU: FUNGSI EDIT & DELETE --- //

    public function edit(Expense $expense)
    {
        // Hanya Superadmin dan Owner yang boleh edit
        if (!Auth::user()->hasAnyRole(['superadmin', 'owner'])) {
            abort(403, 'Hanya Superadmin dan Owner yang dapat mengedit pengeluaran.');
        }

        $stores = Store::all();
        $warehouses = Warehouse::all();
        return view('expenses.edit', compact('expense', 'stores', 'warehouses'));
    }

    public function update(Request $request, Expense $expense)
    {
        if (!Auth::user()->hasAnyRole(['superadmin', 'owner'])) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'expense_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'receipt' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
            'source_type' => 'required|in:store,warehouse',
            'source_id' => 'required',
        ]);

        $data = $request->only(['title', 'description', 'expense_type', 'amount', 'expense_date']);

        // Reset asal usul (karena mungkin dipindah dari toko ke gudang atau sebaliknya)
        $data['store_id'] = null;
        $data['warehouse_id'] = null;

        if ($request->source_type === 'store') {
            $data['store_id'] = $request->source_id;
        } else {
            $data['warehouse_id'] = $request->source_id;
        }

        if ($request->hasFile('receipt')) {
            // Hapus struk lama jika ada
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $data['receipt_path'] = $request->file('receipt')->store('expenses', 'public');
        }

        $expense->update($data);
        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil diperbarui!');
    }

    public function destroy(Expense $expense)
    {
        if (!Auth::user()->hasAnyRole(['superadmin', 'owner'])) {
            abort(403, 'Akses ditolak.');
        }

        // Hapus file gambar dari storage
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }
        
        $expense->delete();
        return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}