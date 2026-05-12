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
        return Sale::with(['store', 'paymentMethod', 'items.variant.product', 'creator'])
            ->when($this->storeId,  fn($q) => $q->where('store_id', $this->storeId))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['No. Penjualan', 'Toko', 'Metode Bayar', 'Kasir', 'Items', 'Subtotal', 'Diskon', 'Total', 'Bayar', 'Kembalian', 'Tanggal'];
    }

    public function map($sale): array
    {
        return [
            $sale->sale_no,
            $sale->store->name,
            $sale->paymentMethod?->name ?? '-',
            $sale->creator?->name ?? '-',
            $sale->items->sum('qty'),
            $sale->subtotal,
            $sale->discount_amount,
            $sale->total_amount,
            $sale->amount_paid,
            $sale->change_amount,
            $sale->created_at->format('d/m/Y H:i'),
        ];
    }

    public function title(): string { return 'Laporan Penjualan'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
