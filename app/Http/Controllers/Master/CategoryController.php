<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view master');

        $categories = Category::with('parent')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->status !== null && $request->status !== '', fn($q) => $q->where('is_active', $request->status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('master.categories.index', compact('categories'));
    }

    public function create()
    {
        $this->authorize('create master');
        $parents = Category::whereNull('parent_id')->where('is_active', true)->orderBy('name')->get();
        return view('master.categories.form', compact('parents'));
    }

    public function store(Request $request)
    {
        $this->authorize('create master');

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'code'      => ['required', 'string', 'max:10', 'unique:categories,code'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'sort_order'=> ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        $validated['slug']      = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['code']      = strtoupper($validated['code']);

        $category = Category::create($validated);
        AuditLogService::log('create', 'categories', "Kategori '{$category->name}' dibuat", null, $validated, Category::class, $category->id);

        return redirect()->route('master.categories.index')->with('success', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    public function edit(Category $category)
    {
        $this->authorize('update master');
        $parents = Category::whereNull('parent_id')->where('is_active', true)->where('id', '!=', $category->id)->orderBy('name')->get();
        return view('master.categories.form', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update master');

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'code'      => ['required', 'string', 'max:10', Rule::unique('categories', 'code')->ignore($category->id)->whereNull('deleted_at')],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'sort_order'=> ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        $validated['slug']      = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['code']      = strtoupper($validated['code']);

        $old = $category->toArray();
        $category->update($validated);
        AuditLogService::log('update', 'categories', "Kategori '{$category->name}' diubah", $old, $validated, Category::class, $category->id);

        return redirect()->route('master.categories.index')->with('success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete master');
        $name = $category->name;
        $category->delete();
        AuditLogService::log('delete', 'categories', "Kategori '{$name}' dihapus");

        return redirect()->route('master.categories.index')->with('success', "Kategori '{$name}' berhasil dihapus.");
    }
}
