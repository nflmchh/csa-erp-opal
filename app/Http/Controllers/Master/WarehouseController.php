<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view master');
        $warehouses = Warehouse::when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->status !== null && $request->status !== '', fn($q) => $q->where('is_active', $request->status))
            ->orderBy('name')->paginate(20)->withQueryString();
        return view('master.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $this->authorize('create master');
        return view('master.warehouses.form');
    }

    public function store(Request $request)
    {
        $this->authorize('create master');
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'code'     => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('warehouses')->whereNull('deleted_at')],
            'address'  => ['nullable', 'string'],
            'city'     => ['nullable', 'string', 'max:100'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'is_active'=> ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $wh = Warehouse::create($validated);
        AuditLogService::log('create', 'warehouses', "Gudang '{$wh->name}' dibuat");
        return redirect()->route('master.warehouses.index')->with('success', "Gudang '{$wh->name}' berhasil ditambahkan.");
    }

    public function edit(Warehouse $warehouse)
    {
        $this->authorize('update master');
        return view('master.warehouses.form', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $this->authorize('update master');
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'code'     => ['required', 'string', 'max:20', Rule::unique('warehouses', 'code')->ignore($warehouse->id)->whereNull('deleted_at')],
            'address'  => ['nullable', 'string'],
            'city'     => ['nullable', 'string', 'max:100'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'pic_name' => ['nullable', 'string', 'max:100'],
            'is_active'=> ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $warehouse->update($validated);
        AuditLogService::log('update', 'warehouses', "Gudang '{$warehouse->name}' diubah");
        return redirect()->route('master.warehouses.index')->with('success', "Gudang '{$warehouse->name}' berhasil diperbarui.");
    }

    public function destroy(Warehouse $warehouse)
    {
        $this->authorize('delete master');
        $name = $warehouse->name;
        $warehouse->delete();
        return redirect()->route('master.warehouses.index')->with('success', "Gudang '{$name}' berhasil dihapus.");
    }
}
