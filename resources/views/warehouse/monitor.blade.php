@extends('layouts.app')
@section('title', 'Monitor Gudang')
@section('page-title', 'Monitor Gudang')
@section('breadcrumb', 'Gudang / Monitor')

@section('content')
@php 
    // Penanda apakah ini layar TV (Fullscreen) atau layar Laptop
    $isFs = request()->query('fs') == '1'; 
@endphp

{{-- =========================================================================
     CSS PEMBUNUH LAYOUT BAWAAN (Hanya aktif di Layar Extend)
     Menyembunyikan segala hal saat loading agar sidebar tidak terlihat berkedip
     ========================================================================= --}}
@if($isFs)
<style>
    /* Sembunyikan segalanya di body secara default */
    body { visibility: hidden !important; overflow: hidden !important; background-color: #f8fafc !important; }
    /* Paksa HANYA wrapper kita yang terlihat dan memenuhi layar */
    #fs-monitor-wrapper { 
        visibility: visible !important; 
        position: fixed !important; top: 0 !important; left: 0 !important; 
        width: 100vw !important; height: 100vh !important; 
        z-index: 2147483647 !important; 
        background-color: #f8fafc !important; 
    }
    /* Hilangkan scrollbar */
    ::-webkit-scrollbar { display: none; }
</style>
@endif

{{-- =========================================================================
     KONTEN MONITOR
     Dibungkus dengan ID unik agar bisa ditarik paksa oleh Javascript nanti
     ========================================================================= --}}
<div id="fs-monitor-wrapper" class="{{ $isFs ? 'p-8 overflow-y-auto text-slate-800' : 'space-y-5' }}" x-data="{ refreshIn: 60 }" x-init="setInterval(() => { if(--refreshIn <= 0) location.reload(); }, 1000)">

    {{-- Header Khusus TV/Monitor --}}
    @if($isFs)
    <header class="flex justify-between items-center border-b border-gray-200 pb-4 mb-6 shrink-0">
        <div>
            <h1 class="text-3xl font-black text-gray-800 tracking-widest uppercase">Live Monitor Gudang</h1>
            <p class="text-gray-500 mt-1 font-mono">Status Pengiriman & Transfer Barang</p>
        </div>
        <div class="text-right">
            <p class="text-2xl font-bold text-indigo-600" id="liveClock">00:00:00</p>
            <p class="text-sm text-gray-400 uppercase font-bold">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
    </header>
    @endif

    {{-- Auto-refresh indicator --}}
    <div class="flex items-center justify-between text-xs text-gray-500">
        <span>Data diperbarui: {{ now()->format('H:i:s') }}</span>
        <span>Auto-refresh dalam <span class="font-mono font-semibold text-indigo-600" x-text="refreshIn"></span>s</span>
    </div>

    {{-- Kotak Statistik Atas --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach($warehouses as $wh)
        @php
            $total = \App\Models\Stock::where('location_type', 'warehouse')->where('location_id', $wh->id)->sum('qty');
            $skuCount = \App\Models\Stock::where('location_type', 'warehouse')->where('location_id', $wh->id)->where('qty', '>', 0)->count();
        @endphp
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-500">{{ $wh->name }}</p>
            <p class="text-2xl font-bold mt-1 text-gray-900">{{ number_format($total) }}</p>
            <p class="text-xs mt-0.5 text-gray-400">{{ $skuCount }} SKU</p>
        </div>
        @endforeach
        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-indigo-500">Penerimaan Hari Ini</p>
            <p class="text-2xl font-bold mt-1 text-indigo-700">{{ $todayInbounds }}</p>
            <p class="text-xs mt-0.5 text-indigo-400">dokumen</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Kolom Kiri: Pengiriman --}}
        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Pengiriman Dalam Perjalanan</h2>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium text-orange-600 bg-orange-50 border border-orange-100">{{ $inTransit->count() }} aktif</span>
            </div>
            @if($inTransit->isEmpty())
            <div class="py-10 text-center text-sm text-gray-400">Tidak ada pengiriman dalam perjalanan</div>
            @else
            <div class="divide-y divide-gray-100">
                @foreach($inTransit as $s)
                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                    <div>
                        <a href="{{ route('warehouse.shipments.show', $s) }}"
                            class="font-mono text-xs font-semibold hover:underline text-indigo-600">{{ $s->shipment_no }}</a>
                        <p class="text-xs mt-0.5 text-gray-500">{{ optional($s->warehouse)->name ?? '—' }} → {{ optional($s->store)->name ?? '—' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $s->statusColor() }}">{{ $s->statusLabel() }}</span>
                        <p class="text-xs mt-0.5 text-gray-400">{{ $s->shipped_at?->diffForHumans() ?? '—' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Kolom Kanan: Stok Habis --}}
        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Stok Hampir Habis</h2>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium text-red-600 bg-red-50 border border-red-100">≤ 5 pcs</span>
            </div>
            @if($lowStock->isEmpty())
            <div class="py-10 text-center text-sm text-gray-400">Tidak ada stok kritis</div>
            @else
            <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                @foreach($lowStock as $stock)
                @php $v = $stock->variant; @endphp
                <div class="px-5 py-2.5 flex items-center justify-between hover:bg-gray-50">
                    <div>
                        <p class="font-mono text-xs text-gray-700">{{ optional($v)->sku ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $v?->product?->name ?? '—' }} · {{ $stock->location?->name ?? '—' }}</p>
                    </div>
                    <span class="text-sm font-bold {{ $stock->qty === 0 ? 'text-red-600' : 'text-orange-500' }}">
                        {{ $stock->qty }} pcs
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Tombol Kembali Kiosk (Hanya di mode Laptop) --}}
@if(!$isFs && request()->query('kiosk') == 1)
    <div class="fixed bottom-6 right-6 z-50">
        <a href="{{ route('dashboard') }}" class="bg-red-600 hover:bg-red-700 text-white shadow-xl rounded-full px-6 py-3 flex items-center gap-2 font-medium transition-transform hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Tutup Monitor & Kembali Kerja
        </a>
    </div>
@endif

{{-- =========================================================================
     JAVASCRIPT EKSTRAKTOR (Hanya berjalan di Layar TV Extend)
     ========================================================================= --}}
@if($isFs)
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Cabut tabel kita dari kurungan Layout Laravel
            const wrapper = document.getElementById('fs-monitor-wrapper');
            document.body.appendChild(wrapper);

            // 2. HAPUS MUTLAK (Delete) Sidebar, Topbar, dan sisa Layout dari DOM browser
            Array.from(document.body.children).forEach(child => {
                if (child.id !== 'fs-monitor-wrapper' && child.tagName !== 'SCRIPT' && child.tagName !== 'STYLE') {
                    child.remove();
                }
            });

            // 3. Kembalikan fungsionalitas scroll & warna background pada body yang murni
            document.body.style.visibility = 'visible';
            document.body.style.overflow = 'hidden'; // Matikan scroll layar bawaan

            // 4. Skrip Jam Digital
            if (document.getElementById('liveClock')) {
                setInterval(() => {
                    const now = new Date();
                    document.getElementById('liveClock').innerText = now.toLocaleTimeString('id-ID', { hour12: false });
                }, 1000);
            }

            // 5. Skrip Paksa Fullscreen
            setTimeout(() => { try { document.documentElement.requestFullscreen(); } catch (e) {} }, 500);
            document.body.addEventListener('click', () => {
                if (!document.fullscreenElement) document.documentElement.requestFullscreen();
            }, { once: true });
        });
    </script>
@endif
@endsection