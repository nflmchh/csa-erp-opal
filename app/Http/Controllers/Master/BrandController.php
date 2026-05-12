<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\BrandRequest;
use App\Models\Brand;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view master');

        $brands = Brand::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->status !== null && $request->status !== '', fn($q) => $q->where('is_active', $request->status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('master.brands.index', compact('brands'));
    }

    public function create()
    {
        $this->authorize('create master');
        return view('master.brands.form');
    }

    public function store(BrandRequest $request)
    {
        $this->authorize('create master');

        $brand = Brand::create($request->validated());

        AuditLogService::log('create', 'brands', "Brand '{$brand->name}' dibuat", null, $request->validated(), Brand::class, $brand->id);

        return redirect()->route('master.brands.index')->with('success', "Brand '{$brand->name}' berhasil ditambahkan.");
    }

    public function edit(Brand $brand)
    {
        $this->authorize('update master');
        return view('master.brands.form', compact('brand'));
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        $this->authorize('update master');

        $old = $brand->toArray();
        $brand->update($request->validated());

        AuditLogService::log('update', 'brands', "Brand '{$brand->name}' diubah", $old, $brand->toArray(), Brand::class, $brand->id);

        return redirect()->route('master.brands.index')->with('success', "Brand '{$brand->name}' berhasil diperbarui.");
    }

    public function destroy(Brand $brand)
    {
        $this->authorize('delete master');

        $name = $brand->name;
        $brand->delete();

        AuditLogService::log('delete', 'brands', "Brand '{$name}' dihapus", null, null, Brand::class, $brand->id);

        return redirect()->route('master.brands.index')->with('success', "Brand '{$name}' berhasil dihapus.");
    }
}
