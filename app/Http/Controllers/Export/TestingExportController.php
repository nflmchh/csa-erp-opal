<?php

namespace App\Http\Controllers\Export;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * TestingExportController
 *
 * Halaman testing TANPA AUTH untuk menguji export file agar kompatibel
 * dengan Bluefy (browser iOS/iPadOS berbasis WebKit).
 *
 * ⚠️  HANYA UNTUK TESTING — nonaktifkan route ini setelah selesai!
 *
 * Masalah Bluefy:
 *  - Bluefy (WebKit iOS) menolak download file jika Content-Type tidak tepat
 *  - File Excel (.xlsx) dari Maatwebsite/Excel sering tidak terdeteksi format-nya
 *    karena WebKit melihat stream biner tanpa header yang jelas
 *  - Solusi 1 (terbaik): gunakan CSV — selalu dikenali semua browser iOS
 *  - Solusi 2: simpan .xlsx ke disk dulu lalu kirim dengan header Content-Type eksplisit
 */
class TestingExportController extends Controller
{
    /**
     * Halaman testing utama — tampilkan pilihan filter & tombol export
     */
    public function index(Request $request)
    {
        $stores = Store::orderBy('name')->get();

        $dateFrom = $request->date_from ?? now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->format('Y-m-d');
        $storeId = $request->store_id;

        return view('testing.export', compact('stores', 'dateFrom', 'dateTo', 'storeId'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CSV EXPORT — format paling kompatibel dengan Bluefy / Safari iOS
    // ─────────────────────────────────────────────────────────────────────────

    public function salesCsv(Request $request, $filename = null)
    {
        $filename = $filename ?? ('laporan-penjualan-' . now()->format('Y-m-d_H-i-s') . '.csv');
        $query = Sale::with([
            'store',
            'paymentMethod',
            'items.variant' => fn($q) => $q->withTrashed(),
            'items.variant.product' => fn($q) => $q->withTrashed(),
            'items.variant.color',
            'items.variant.size',
            'creator',
        ])
            ->when($request->store_id, fn($q) => $q->where('store_id', $request->store_id))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('created_at', 'desc')
            ->limit(2000);

        $sales = $query->get();

        // ── Header HTTP yang kompatibel dengan Bluefy / iOS WebKit ──────────
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ];

        $callback = function () use ($sales) {
            $handle = fopen('php://output', 'w');

            // ── BOM UTF-8 ─────────────────────────────────────────────────
            // Wajib agar Excel & Numbers (iOS) membaca teks Indonesia
            // (nama produk dengan huruf khusus) dengan encoding yang benar.
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ── Baris header kolom ────────────────────────────────────────
            fputcsv($handle, [
                'No. Penjualan',
                'Toko',
                'Metode Bayar',
                'Kasir',
                'Subtotal',
                'Diskon',
                'Total Transaksi',
                'Status Bayar',
                'Tanggal',
                'Produk',
                'SKU',
                'Warna',
                'Ukuran',
                'Qty',
                'Harga Satuan',
                'Subtotal Item',
            ]);

            // ── Data baris ────────────────────────────────────────────────
            foreach ($sales as $sale) {
                foreach ($sale->items as $idx => $item) {
                    fputcsv($handle, [
                        // Kolom transaksi — hanya isi di baris pertama tiap sale
                        $idx === 0 ? $sale->sale_no : '',
                        $idx === 0 ? ($sale->store?->name ?? '-') : '',
                        $idx === 0 ? ($sale->paymentMethod?->name ?? '-') : '',
                        $idx === 0 ? ($sale->creator?->name ?? '-') : '',
                        $idx === 0 ? (int) $sale->subtotal : '',
                        $idx === 0 ? (int) ($sale->discount_amount ?? 0) : '',
                        $idx === 0 ? (int) $sale->total_amount : '',
                        $idx === 0 ? ($sale->payment_status ?? 'lunas') : '',
                        $idx === 0 ? $sale->created_at->format('d/m/Y H:i') : '',
                        // Kolom item
                        $item->variant?->product?->name ?? 'Produk Terhapus',
                        $item->variant?->sku ?? '-',
                        $item->variant?->color?->name ?? '-',
                        $item->variant?->size?->name ?? '-',
                        (int) $item->qty,
                        (int) $item->unit_price,
                        (int) $item->subtotal,
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXCEL EXPORT — simpan ke disk dulu, lalu kirim dengan header eksplisit
    // ─────────────────────────────────────────────────────────────────────────

    public function salesExcel(Request $request, $filename = null)
    {
        $filename = $filename ?? ('laporan-penjualan-' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $diskPath = 'exports/' . $filename;        // relatif terhadap storage/app/
        $fullPath = storage_path('app/' . $diskPath);

        $export = new \App\Exports\SalesExport(
            $request->store_id,
            $request->date_from,
            $request->date_to,
        );

        // Simpan file ke storage/app/exports/ dulu
        Excel::store($export, $diskPath, 'local');

        // Jika gagal tersimpan, fallback ke streaming biasa
        if (!file_exists($fullPath)) {
            return Excel::download($export, $filename);
        }

        // Kirim dengan header HTTP eksplisit agar Bluefy mengenali format-nya
        $response = response()
            ->download($fullPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ])
            ->deleteFileAfterSend(true);

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
