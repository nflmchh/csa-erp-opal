<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashSessionController extends Controller
{
    public function index()
    {
        $this->authorize('view cash session');
        $user    = Auth::user();
        $store   = $user->primaryStore();

        if (!$store) {
            return redirect()->route('dashboard')->with('error', 'Anda belum ditugaskan ke toko manapun.');
        }

        $active = CashSession::where('user_id', $user->id)->where('status', 'open')->first();

        $history = CashSession::where('store_id', $store->id)
            ->where('status', 'closed')
            ->with('user')
            ->orderBy('opened_at', 'desc')
            ->paginate(15);

        return view('pos.session.index', compact('store', 'active', 'history'));
    }

    public function open(Request $r)
    {
        $this->authorize('open cash session');
        $user  = Auth::user();
        $store = $user->primaryStore();

        if (!$store) {
            return back()->with('error', 'Anda belum ditugaskan ke toko.');
        }

        $existing = CashSession::where('user_id', $user->id)->where('status', 'open')->first();
        if ($existing) {
            return back()->with('error', 'Anda masih memiliki sesi yang aktif.');
        }

        $r->validate([
            'opening_amount' => 'required|numeric|min:0',
            'notes'          => 'nullable|string|max:300',
        ]);

        $session = CashSession::create([
            'store_id'       => $store->id,
            'user_id'        => $user->id,
            'status'         => 'open',
            'opening_amount' => $r->opening_amount,
            'notes'          => $r->notes,
            'opened_at'      => now(),
        ]);

        AuditLogService::log('open', 'CashSession', "Buka sesi kasir di {$store->name}", null, null, CashSession::class, $session->id);

        return redirect()->route('pos.index')->with('success', 'Sesi kasir dibuka. Selamat bekerja!');
    }

    public function close(Request $r)
    {
        $this->authorize('close cash session');
        $user    = Auth::user();
        $session = CashSession::where('user_id', $user->id)->where('status', 'open')->firstOrFail();

        $r->validate([
            'closing_amount' => 'required|numeric|min:0',
            'notes'          => 'nullable|string|max:500',
        ]);

        $session->load('sales.paymentMethod');
        $totalSales    = $session->totalSales();
        $cashSalesTotal = $session->sales
            ->filter(fn($s) => optional($s->paymentMethod)->type === 'cash')
            ->sum('total_amount');
        $expectedAmount = (float) $session->opening_amount + $cashSalesTotal;

        $session->update([
            'status'          => 'closed',
            'closing_amount'  => $r->closing_amount,
            'expected_amount' => $expectedAmount,
            'notes'           => $r->notes,
            'closed_at'       => now(),
        ]);

        AuditLogService::log('close', 'CashSession', "Tutup sesi kasir #{$session->id}", null, null, CashSession::class, $session->id);

        return redirect()->route('pos.session.index')->with('success', "Sesi ditutup. Total penjualan: Rp " . number_format($totalSales, 0, ',', '.'));
    }
}
