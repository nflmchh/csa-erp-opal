<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ReturnReason;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReturnReasonController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view master');
        $returnReasons = ReturnReason::when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')->paginate(20)->withQueryString();
        return view('master.return-reasons.index', compact('returnReasons'));
    }

    public function create()
    {
        $this->authorize('create master');
        return view('master.return-reasons.form');
    }

    public function store(Request $request)
    {
        $this->authorize('create master');
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'code'     => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('return_reasons')->whereNull('deleted_at')],
            'type'     => ['required', Rule::in(['customer', 'store', 'both'])],
            'is_active'=> ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        ReturnReason::create($validated);
        return redirect()->route('master.return-reasons.index')->with('success', 'Alasan retur berhasil ditambahkan.');
    }

    public function edit(ReturnReason $returnReason)
    {
        $this->authorize('update master');
        return view('master.return-reasons.form', compact('returnReason'));
    }

    public function update(Request $request, ReturnReason $returnReason)
    {
        $this->authorize('update master');
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'code'     => ['required', 'string', 'max:20', Rule::unique('return_reasons', 'code')->ignore($returnReason->id)],
            'type'     => ['required', Rule::in(['customer', 'store', 'both'])],
            'is_active'=> ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $returnReason->update($validated);
        return redirect()->route('master.return-reasons.index')->with('success', 'Alasan retur berhasil diperbarui.');
    }

    public function destroy(ReturnReason $returnReason)
    {
        $this->authorize('delete master');
        $returnReason->delete();
        return redirect()->route('master.return-reasons.index')->with('success', 'Alasan retur berhasil dihapus.');
    }
}
