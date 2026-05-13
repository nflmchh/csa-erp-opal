<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Size;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view master');
        $sizes = Size::when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(30)->withQueryString();
        return view('master.sizes.index', compact('sizes'));
    }

    public function create()
    {
        $this->authorize('create master');
        return view('master.sizes.form');
    }

    public function store(Request $request)
    {
        $this->authorize('create master');
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:10'],
            'code'       => ['required', 'string', 'max:10', \Illuminate\Validation\Rule::unique('sizes')->whereNull('deleted_at')],
            'sort_order' => ['integer', 'min:0'],
            'is_active'  => ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $size = Size::create($validated);
        AuditLogService::log('create', 'sizes', "Ukuran '{$size->name}' dibuat");
        return redirect()->route('master.sizes.index')->with('success', "Ukuran '{$size->name}' berhasil ditambahkan.");
    }

    public function edit(Size $size)
    {
        $this->authorize('update master');
        return view('master.sizes.form', compact('size'));
    }

    public function update(Request $request, Size $size)
    {
        $this->authorize('update master');
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:10'],
            'code'       => ['required', 'string', 'max:10', Rule::unique('sizes', 'code')->ignore($size->id)],
            'sort_order' => ['integer', 'min:0'],
            'is_active'  => ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $size->update($validated);
        return redirect()->route('master.sizes.index')->with('success', "Ukuran '{$size->name}' berhasil diperbarui.");
    }

    public function destroy(Size $size)
    {
        $this->authorize('delete master');
        $name = $size->name;
        $size->delete();
        return redirect()->route('master.sizes.index')->with('success', "Ukuran '{$name}' berhasil dihapus.");
    }
}
