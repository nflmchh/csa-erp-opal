<?php

namespace App\Exports;

use App\Models\Stock;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        protected string  $locationType = 'warehouse',
        protected ?int    $locationId   = null,
    ) {}

    public function collection(): Collection
    {
        return Stock::with(['variant.product.brand', 'variant.color', 'variant.size'])
            ->where('location_type', $this->locationType)
            ->when($this->locationId, fn($q) => $q->where('location_id', $this->locationId))
            ->where('qty', '>', 0)
            ->orderByDesc('qty')
            ->get();
    }

    public function headings(): array
    {
        return ['SKU', 'Produk', 'Brand', 'Warna', 'Ukuran', 'Tipe Lokasi', 'ID Lokasi', 'Qty'];
    }

    public function map($stock): array
    {
        return [
            $stock->variant->sku,
            $stock->variant->product->name,
            $stock->variant->product->brand?->name ?? '-',
            $stock->variant->color->name,
            $stock->variant->size->name,
            $stock->location_type,
            $stock->location_id,
            $stock->qty,
        ];
    }

    public function title(): string { return 'Laporan Stok'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
