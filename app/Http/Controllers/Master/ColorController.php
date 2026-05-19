<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view master');
        $colors = Color::when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->status !== null && $request->status !== '', fn($q) => $q->where('is_active', $request->status))
            ->orderBy('name')->paginate(20)->withQueryString();
        return view('master.colors.index', compact('colors'));
    }

    public function create()
    {
        $this->authorize('create master');
        return view('master.colors.form');
    }

    public function store(Request $request)
    {
        $this->authorize('create master');
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:50'],
            'code'     => ['required', 'string', 'max:10', \Illuminate\Validation\Rule::unique('colors')],
            'hex_code' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active'=> ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['code']      = strtoupper($validated['code']);
        $color = Color::create($validated);
        AuditLogService::log('create', 'colors', "Warna '{$color->name}' dibuat");
        return redirect()->route('master.colors.index')->with('success', "Warna '{$color->name}' berhasil ditambahkan.");
    }

    public function edit(Color $color)
    {
        $this->authorize('update master');
        return view('master.colors.form', compact('color'));
    }

    public function update(Request $request, Color $color)
    {
        $this->authorize('update master');
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:50'],
            'code'     => ['required', 'string', 'max:10', Rule::unique('colors', 'code')->ignore($color->id)],
            'hex_code' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active'=> ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['code']      = strtoupper($validated['code']);
        $color->update($validated);
        AuditLogService::log('update', 'colors', "Warna '{$color->name}' diubah");
        return redirect()->route('master.colors.index')->with('success', "Warna '{$color->name}' berhasil diperbarui.");
    }

    public function destroy(Color $color)
    {
        $this->authorize('delete master');
        $name = $color->name;
        $color->delete();
        return redirect()->route('master.colors.index')->with('success', "Warna '{$name}' berhasil dihapus.");
    }
}
