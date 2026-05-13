<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreTarget;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view master');
        $stores = Store::when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->status !== null && $request->status !== '', fn($q) => $q->where('is_active', $request->status))
            ->orderBy('name')->paginate(20)->withQueryString();

        // Untuk tampilkan target bulan ini di index
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return view('master.stores.index', compact('stores', 'currentMonth', 'currentYear'));
    }

    public function create()
    {
        $this->authorize('create master');
        return view('master.stores.form');
    }

    public function store(Request $request)
    {
        $this->authorize('create master');
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'code'     => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('stores')->whereNull('deleted_at')],
            'address'  => ['nullable', 'string'],
            'city'     => ['nullable', 'string', 'max:100'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'bank_name'=> ['nullable', 'string', 'max:50'],
            'bank_account'=> ['nullable', 'string', 'max:100'],
            'bank_account_name'=> ['nullable', 'string', 'max:100'],
            'is_active'=> ['boolean'],
            'monthly_target_qty' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['monthly_target_qty'] = $validated['monthly_target_qty'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);
        $store = Store::create($validated);

        // Simpan riwayat target bulan ini (hanya jika owner/superadmin)
        if (Auth::user()->hasAnyRole(['owner', 'superadmin']) && $validated['monthly_target_qty'] > 0) {
            StoreTarget::updateOrCreate(
                ['store_id' => $store->id, 'month' => now()->month, 'year' => now()->year],
                ['target_qty' => $validated['monthly_target_qty']]
            );
        }

        AuditLogService::log('create', 'stores', "Toko '{$store->name}' dibuat");
        return redirect()->route('master.stores.index')->with('success', "Toko '{$store->name}' berhasil ditambahkan.");
    }

    public function edit(Store $store)
    {
        $this->authorize('update master');

        // Cek apakah target bulan ini sudah pernah diubah (untuk peringatan di frontend)
        $targetThisMonth = StoreTarget::where('store_id', $store->id)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->first();

        $targetAlreadySetThisMonth = $targetThisMonth ? true : false;

        return view('master.stores.form', compact('store', 'targetAlreadySetThisMonth'));
    }

    public function update(Request $request, Store $store)
    {
        $this->authorize('update master');
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'code'     => ['required', 'string', 'max:20', Rule::unique('stores', 'code')->ignore($store->id)->whereNull('deleted_at')],
            'address'  => ['nullable', 'string'],
            'city'     => ['nullable', 'string', 'max:100'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'bank_name'=> ['nullable', 'string', 'max:50'],
            'bank_account'=> ['nullable', 'string', 'max:100'],
            'bank_account_name'=> ['nullable', 'string', 'max:100'],
            'is_active'=> ['boolean'],
            'monthly_target_qty' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['monthly_target_qty'] = $validated['monthly_target_qty'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);
        $store->update($validated);

        // Simpan riwayat target bulan ini (hanya jika owner/superadmin)
        if (Auth::user()->hasAnyRole(['owner', 'superadmin'])) {
            StoreTarget::updateOrCreate(
                ['store_id' => $store->id, 'month' => now()->month, 'year' => now()->year],
                ['target_qty' => $validated['monthly_target_qty']]
            );
        }

        AuditLogService::log('update', 'stores', "Toko '{$store->name}' diubah");
        return redirect()->route('master.stores.index')->with('success', "Toko '{$store->name}' berhasil diperbarui.");
    }

    public function destroy(Store $store)
    {
        $this->authorize('delete master');
        $name = $store->name;
        $store->delete();
        return redirect()->route('master.stores.index')->with('success', "Toko '{$name}' berhasil dihapus.");
    }
}
