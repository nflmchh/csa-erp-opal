<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductType;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductTypeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view master');

        $productTypes = ProductType::with('category')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->status !== null && $request->status !== '', fn($q) => $q->where('is_active', $request->status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('master.product-types.index', compact('productTypes', 'categories'));
    }

    public function create()
    {
        $this->authorize('create master');
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('master.product-types.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create master');

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:10', 'unique:product_types,code'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'sort_order'  => ['integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);
        $validated['slug']      = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['code']      = strtoupper($validated['code']);

        $type = ProductType::create($validated);
        AuditLogService::log('create', 'product_types', "Jenis produk '{$type->name}' dibuat", null, $validated, ProductType::class, $type->id);

        return redirect()->route('master.product-types.index')->with('success', "Jenis produk '{$type->name}' berhasil ditambahkan.");
    }

    public function edit(ProductType $productType)
    {
        $this->authorize('update master');
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('master.product-types.form', ['productType' => $productType, 'categories' => $categories]);
    }

    public function update(Request $request, ProductType $productType)
    {
        $this->authorize('update master');

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:10', Rule::unique('product_types', 'code')->ignore($productType->id)->whereNull('deleted_at')],
            'category_id' => ['nullable', 'exists:categories,id'],
            'sort_order'  => ['integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);
        $validated['slug']      = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['code']      = strtoupper($validated['code']);

        $old = $productType->toArray();
        $productType->update($validated);
        AuditLogService::log('update', 'product_types', "Jenis produk '{$productType->name}' diubah", $old, $validated, ProductType::class, $productType->id);

        return redirect()->route('master.product-types.index')->with('success', "Jenis produk '{$productType->name}' berhasil diperbarui.");
    }

    public function destroy(ProductType $productType)
    {
        $this->authorize('delete master');
        $name = $productType->name;
        $productType->delete();
        AuditLogService::log('delete', 'product_types', "Jenis produk '{$name}' dihapus");

        return redirect()->route('master.product-types.index')->with('success', "Jenis produk '{$name}' berhasil dihapus.");
    }
}
