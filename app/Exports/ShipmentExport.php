<?php

namespace App\Exports;

use App\Models\Shipment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShipmentExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        protected ?string $warehouseId = null,
        protected ?string $storeId     = null,
        protected ?string $status      = null,
        protected ?string $dateFrom    = null,
        protected ?string $dateTo      = null,
    ) {}

    public function collection(): Collection
    {
        return Shipment::with(['warehouse', 'store', 'items'])
            ->when($this->warehouseId, fn($q) => $q->where('warehouse_id', $this->warehouseId))
            ->when($this->storeId,     fn($q) => $q->where('store_id', $this->storeId))
            ->when($this->status,      fn($q) => $q->where('status', $this->status))
            ->when($this->dateFrom,    fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,      fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['No. SHP', 'Gudang', 'Toko Tujuan', 'Status', 'Total Qty', 'Tanggal'];
    }

    public function map($shipment): array
    {
        $statusLabel = ['draft' => 'Draft', 'sent' => 'Terkirim', 'received' => 'Diterima'];

        return [
            $shipment->shipment_no,
            $shipment->warehouse->name,
            $shipment->store->name,
            $statusLabel[$shipment->status] ?? $shipment->status,
            $shipment->items->sum('qty'),
            $shipment->created_at->format('d/m/Y H:i'),
        ];
    }

    public function title(): string { return 'Laporan Pengiriman'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
