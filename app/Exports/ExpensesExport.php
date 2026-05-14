<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(
        protected ?string $sourceFilter = null,
        protected ?string $expenseType  = null,
        protected ?string $dateFrom     = null,
        protected ?string $dateTo       = null,
    ) {}

    public function collection(): Collection
    {
        $user  = auth()->user();
        $query = Expense::with(['store', 'warehouse', 'creator']);

        if ($user->hasAnyRole(['superadmin', 'owner'])) {
            if ($this->sourceFilter) {
                $source = explode('_', $this->sourceFilter);
                if ($source[0] === 'store') {
                    $query->where('store_id', $source[1]);
                } elseif ($source[0] === 'warehouse') {
                    $query->where('warehouse_id', $source[1]);
                }
            }
        } elseif ($user->hasRole('kepala toko')) {
            $storeIds = $user->stores()->pluck('stores.id');
            $query->whereIn('store_id', $storeIds);
        } elseif ($user->hasRole('admin gudang')) {
            $warehouseIds = $user->warehouses()->pluck('warehouses.id');
            $query->whereIn('warehouse_id', $warehouseIds);
        }

        if ($this->expenseType) {
            $query->where('expense_type', $this->expenseType);
        }
        if ($this->dateFrom) {
            $query->whereDate('expense_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('expense_date', '<=', $this->dateTo);
        }

        return $query->latest('expense_date')->get();
    }

    public function headings(): array
    {
        return ['Tanggal', 'Jenis', 'Judul Pengeluaran', 'Keterangan', 'Sumber', 'Nominal (Rp)'];
    }

    public function map($expense): array
    {
        $sumber = '-';
        if ($expense->store_id) {
            $sumber = 'Toko: ' . ($expense->store->name ?? '-');
        } elseif ($expense->warehouse_id) {
            $sumber = 'Gudang: ' . ($expense->warehouse->name ?? '-');
        }

        return [
            \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y'),
            $expense->expense_type,
            $expense->title,
            $expense->description ?? '',
            $sumber,
            $expense->amount,
        ];
    }

    public function title(): string { return 'Laporan Pengeluaran'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
