<?php

namespace App\Exports;

use App\Models\Inbound;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InboundExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        protected ?int    $warehouseId = null,
        protected ?string $status      = null,
        protected ?string $dateFrom    = null,
        protected ?string $dateTo      = null,
    ) {}

    public function collection(): Collection
    {
        return Inbound::with(['warehouse', 'creator', 'items'])
            ->when($this->warehouseId, fn($q) => $q->where('warehouse_id', $this->warehouseId))
            ->when($this->status,      fn($q) => $q->where('status', $this->status))
            ->when($this->dateFrom,    fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,      fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['No. Referensi', 'Gudang', 'Supplier', 'Status', 'Total Item', 'Total Qty', 'Tanggal Masuk', 'Dibuat Oleh'];
    }

    public function map($inbound): array
    {
        return [
            $inbound->reference_no,
            $inbound->warehouse->name,
            $inbound->supplier_name ?? '-',
            ucfirst($inbound->status),
            $inbound->items->count(),
            $inbound->items->sum('qty'),
            $inbound->created_at->format('d/m/Y H:i'),
            $inbound->creator->name,
        ];
    }

    public function title(): string { return 'Laporan Barang Masuk'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
