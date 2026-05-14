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
        protected string  $locationType   = 'warehouse',
        protected ?int    $locationId     = null,
        protected ?string $searchProduct  = null,
        protected ?int    $brandId        = null,
        protected ?int    $colorId        = null,
        protected ?int    $sizeId         = null,
    ) {}

    public function collection(): Collection
    {
        return Stock::with(['variant.product.brand', 'variant.color', 'variant.size'])
            ->join('product_variants as pv', 'stocks.product_variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->where('stocks.location_type', $this->locationType)
            ->when($this->locationId,    fn($q) => $q->where('stocks.location_id', $this->locationId))
            ->when($this->searchProduct, fn($q) => $q->where('p.name', 'like', '%' . $this->searchProduct . '%'))
            ->when($this->brandId,       fn($q) => $q->where('p.brand_id', $this->brandId))
            ->when($this->colorId,       fn($q) => $q->where('pv.color_id', $this->colorId))
            ->when($this->sizeId,        fn($q) => $q->where('pv.size_id', $this->sizeId))
            ->where('stocks.qty', '>', 0)
            ->select('stocks.*')
            ->orderByDesc('stocks.qty')
            ->get();
    }

    public function headings(): array
    {
        return ['SKU', 'Produk', 'Brand', 'Warna', 'Ukuran', 'Tipe Lokasi', 'Lokasi', 'Qty'];
    }

    public function map($stock): array
    {
        $locName = '-';
        if ($stock->location_type === 'warehouse') {
            $locName = \App\Models\Warehouse::find($stock->location_id)?->name ?? $stock->location_id;
        } else {
            $locName = \App\Models\Store::find($stock->location_id)?->name ?? $stock->location_id;
        }

        return [
            $stock->variant->sku,
            $stock->variant->product->name,
            $stock->variant->product->brand?->name ?? '-',
            $stock->variant->color->name,
            $stock->variant->size->name,
            $stock->location_type === 'warehouse' ? 'Gudang' : 'Toko',
            $locName,
            $stock->qty,
        ];
    }

    public function title(): string { return 'Laporan Stok'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
