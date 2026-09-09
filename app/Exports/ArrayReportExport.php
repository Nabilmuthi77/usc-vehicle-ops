<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * FR-M6-05 — export laporan ke Excel.
 *
 * Menerima data yang sudah diagregasi service layer sehingga satu kelas
 * dapat melayani seluruh laporan (biaya, utilisasi, aktivitas driver).
 */
class ArrayReportExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, string>  $columns  kunci kolom => judul kolom
     */
    public function __construct(
        private readonly array $rows,
        private readonly array $columns,
        private readonly string $title = 'Laporan',
        private readonly ?array $totals = null,
    ) {}

    /** @return array<int, array<int, mixed>> */
    public function array(): array
    {
        $data = array_map(
            fn (array $row) => array_map(
                fn (string $key) => $row[$key] ?? null,
                array_keys($this->columns),
            ),
            $this->rows,
        );

        if ($this->totals) {
            $totalRow = [];
            $first = true;
            foreach (array_keys($this->columns) as $key) {
                if ($first) {
                    $totalRow[] = 'TOTAL';
                    $first = false;
                } else {
                    $totalRow[] = $this->totals[$key] ?? '';
                }
            }
            $data[] = $totalRow;
        }

        return $data;
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return array_values($this->columns);
    }

    public function title(): string
    {
        // Nama sheet Excel dibatasi 31 karakter.
        return mb_substr($this->title, 0, 31);
    }

    /** @return array<int, array<string, mixed>> */
    public function styles(Worksheet $sheet): array
    {
        $styles = [
            1 => ['font' => ['bold' => true]],
        ];
        
        if ($this->totals) {
            // +1 for headings, +1 for totals row
            $lastRow = count($this->rows) + 2; 
            $styles[$lastRow] = ['font' => ['bold' => true]];
        }
        
        return $styles;
    }
}
