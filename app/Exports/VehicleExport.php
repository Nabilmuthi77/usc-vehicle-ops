<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehicleExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private readonly Collection $vehicles) {}

    public function collection(): Collection
    {
        return $this->vehicles;
    }

    public function headings(): array
    {
        return [
            'Plat Nomor',
            'Merk/Tipe',
            'Tahun',
            'Kepemilikan',
            'Departemen',
            'Odometer (km)',
            'Ganjil/Genap',
            'Status',
        ];
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->plate_number,
            $vehicle->full_name,
            $vehicle->year,
            $vehicle->ownership->label(),
            $vehicle->department?->name ?? '-',
            $vehicle->current_odometer,
            $vehicle->plate_parity ?: '-',
            $vehicle->status->label(),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
