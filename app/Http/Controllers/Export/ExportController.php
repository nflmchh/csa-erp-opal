<?php

namespace App\Http\Controllers\Export;

use App\Exports\SalesExport;
use App\Exports\StockExport;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\Store;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function salesPdf(Request $request)
    {
        $this->authorize('export report');

        $sales = Sale::with(['store', 'paymentMethod', 'items.variant.product'])
            ->when($request->store_id,  fn($q) => $q->where('store_id', $request->store_id))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        $totalSales  = $sales->sum('total_amount');
        $totalOrders = $sales->count();
        $store       = $request->store_id ? Store::find($request->store_id) : null;

        $pdf = Pdf::loadView('exports.pdf.sales', compact('sales', 'totalSales', 'totalOrders', 'store', 'request'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-penjualan-' . now()->format('Ymd') . '.pdf');
    }

    public function salesExcel(Request $request)
    {
        $this->authorize('export report');

        return Excel::download(
            new SalesExport($request->store_id, $request->date_from, $request->date_to),
            'laporan-penjualan-' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function salesCsv(Request $request)
    {
        $this->authorize('export report');

        $sales = Sale::with(['store', 'paymentMethod', 'items', 'creator'])
            ->when($request->store_id,  fn($q) => $q->where('store_id', $request->store_id))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'laporan-penjualan-' . now()->format('Ymd') . '.csv';
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];

        $callback = function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No. Penjualan', 'Toko', 'Metode Bayar', 'Kasir', 'Items', 'Subtotal', 'Diskon', 'Total', 'Bayar', 'Kembalian', 'Tanggal']);
            foreach ($sales as $s) {
                fputcsv($handle, [
                    $s->sale_no, $s->store->name, $s->paymentMethod?->name ?? '-', $s->creator?->name ?? '-',
                    $s->items->sum('qty'), $s->subtotal, $s->discount_amount, $s->total_amount,
                    $s->amount_paid, $s->change_amount, $s->created_at->format('d/m/Y H:i'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function stockPdf(Request $request)
    {
        $this->authorize('export report');

        $locationType = $request->location_type ?? 'warehouse';
        $locationId   = $request->location_id;

        $stocks = Stock::with(['variant.product.brand', 'variant.color', 'variant.size'])
            ->where('location_type', $locationType)
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->where('qty', '>', 0)
            ->orderByDesc('qty')
            ->limit(500)
            ->get();

        $totalQty  = $stocks->sum('qty');
        $location  = $locationId
            ? ($locationType === 'warehouse' ? Warehouse::find($locationId) : Store::find($locationId))
            : null;

        $pdf = Pdf::loadView('exports.pdf.stock', compact('stocks', 'totalQty', 'location', 'locationType'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-stok-' . now()->format('Ymd') . '.pdf');
    }

    public function stockExcel(Request $request)
    {
        $this->authorize('export report');

        return Excel::download(
            new StockExport($request->location_type ?? 'warehouse', $request->location_id),
            'laporan-stok-' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function stockCsv(Request $request)
    {
        $this->authorize('export report');

        $stocks = Stock::with(['variant.product.brand', 'variant.color', 'variant.size'])
            ->where('location_type', $request->location_type ?? 'warehouse')
            ->when($request->location_id, fn($q) => $q->where('location_id', $request->location_id))
            ->where('qty', '>', 0)
            ->orderByDesc('qty')
            ->get();

        $filename = 'laporan-stok-' . now()->format('Ymd') . '.csv';
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];

        $callback = function () use ($stocks) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['SKU', 'Produk', 'Brand', 'Warna', 'Ukuran', 'Tipe Lokasi', 'ID Lokasi', 'Qty']);
            foreach ($stocks as $s) {
                fputcsv($handle, [
                    $s->variant->sku, $s->variant->product->name, $s->variant->product->brand?->name ?? '-',
                    $s->variant->color->name, $s->variant->size->name, $s->location_type, $s->location_id, $s->qty,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
