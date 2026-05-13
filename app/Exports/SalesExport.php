<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        protected ?string $storeId  = null,
        protected ?string $dateFrom = null,
        protected ?string $dateTo   = null,
    ) {}

    public function collection(): Collection
    {
        $sales = Sale::with(['store', 'paymentMethod', 'items.variant.product', 'items.variant.color', 'items.variant.size', 'creator'])
            ->when($this->storeId,  fn($q) => $q->where('store_id', $this->storeId))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->get();

        $rows = [];
        foreach ($sales as $sale) {
            foreach ($sale->items as $idx => $item) {
                $rows[] = [
                    'sale_no' => $idx === 0 ? $sale->sale_no : '',
                    'store'   => $idx === 0 ? $sale->store->name : '',
                    'payment' => $idx === 0 ? ($sale->paymentMethod?->name ?? '-') : '',
                    'cashier' => $idx === 0 ? ($sale->creator?->name ?? '-') : '',
                    'total'   => $idx === 0 ? $sale->total_amount : '',
                    'date'    => $idx === 0 ? $sale->created_at->format('d/m/Y H:i') : '',
                    'product' => $item->variant->product->name,
                    'sku'     => $item->variant->sku . " ({$item->variant->color->name} / {$item->variant->size->name})",
                    'qty'     => $item->qty,
                    'price'   => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ];
            }
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return ['No. Penjualan', 'Toko', 'Metode Bayar', 'Kasir', 'Total Transaksi', 'Tanggal', 'Item', 'SKU / Variant', 'Qty', 'Harga Satuan', 'Subtotal Item'];
    }

    public function map($row): array
    {
        return array_values($row);
    }

    public function title(): string { return 'Laporan Penjualan'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
