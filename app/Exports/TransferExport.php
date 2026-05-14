<?php

namespace App\Exports;

use App\Models\Transfer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransferExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        protected ?string $fromStoreId = null,
        protected ?string $toStoreId   = null,
        protected ?string $status      = null,
        protected ?string $dateFrom    = null,
        protected ?string $dateTo      = null,
    ) {}

    public function collection(): Collection
    {
        return Transfer::with(['fromStore', 'toStore', 'items'])
            ->when($this->fromStoreId, fn($q) => $q->where('from_store_id', $this->fromStoreId))
            ->when($this->toStoreId,   fn($q) => $q->where('to_store_id', $this->toStoreId))
            ->when($this->status,      fn($q) => $q->where('status', $this->status))
            ->when($this->dateFrom,    fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,      fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['No. Transfer', 'Toko Asal', 'Toko Tujuan', 'Status', 'Qty Diminta', 'Qty Diterima', 'Tanggal'];
    }

    public function map($transfer): array
    {
        return [
            $transfer->transfer_no,
            $transfer->fromStore->name,
            $transfer->toStore->name,
            $transfer->statusLabel(),
            $transfer->items->sum('qty_requested'),
            $transfer->isReceived() ? $transfer->items->sum('qty_received') : '-',
            $transfer->created_at->format('d/m/Y H:i'),
        ];
    }

    public function title(): string { return 'Laporan Transfer Toko'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
