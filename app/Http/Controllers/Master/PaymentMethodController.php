<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view master');
        $paymentMethods = PaymentMethod::when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('sort_order')->paginate(20)->withQueryString();
        return view('master.payment-methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        $this->authorize('create master');
        return view('master.payment-methods.form');
    }

    public function store(Request $request)
    {
        $this->authorize('create master');
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'code'       => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('payment_methods')->whereNull('deleted_at')],
            'type'       => ['required', Rule::in(['cash', 'transfer', 'qris', 'card', 'other'])],
            'sort_order' => ['integer', 'min:0'],
            'is_active'  => ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        PaymentMethod::create($validated);
        return redirect()->route('master.payment-methods.index')->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        $this->authorize('update master');
        return view('master.payment-methods.form', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $this->authorize('update master');
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'code'       => ['required', 'string', 'max:20', Rule::unique('payment_methods', 'code')->ignore($paymentMethod->id)],
            'type'       => ['required', Rule::in(['cash', 'transfer', 'qris', 'card', 'other'])],
            'sort_order' => ['integer', 'min:0'],
            'is_active'  => ['boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $paymentMethod->update($validated);
        return redirect()->route('master.payment-methods.index')->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $this->authorize('delete master');
        $paymentMethod->delete();
        return redirect()->route('master.payment-methods.index')->with('success', 'Metode pembayaran berhasil dihapus.');
    }
}
