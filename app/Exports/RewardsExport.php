<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RewardsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        protected array $storeRewards = [],
    ) {}

    public function collection(): Collection
    {
        return collect($this->storeRewards);
    }

    public function headings(): array
    {
        return ['Nama Toko', 'Target (Pcs)', 'Terjual (Pcs)', 'Kelebihan (Pcs)', 'Reward Reguler (Rp)', 'Bonus Target (Rp)', 'Total Reward (Rp)'];
    }

    public function map($data): array
    {
        return [
            $data['store']->name,
            $data['target'],
            $data['total_qty'],
            $data['excess'],
            $data['regular_reward'],
            $data['bonus'],
            $data['total_reward'],
        ];
    }

    public function title(): string { return 'Reward & Bonus Toko'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
