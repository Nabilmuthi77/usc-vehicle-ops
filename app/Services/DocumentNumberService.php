<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * FR-M2-02, FR-M4-16 & FR-M1-08 — penomoran dokumen otomatis.
 *
 * Format diambil dari pengaturan sistem, mis. `PJM/{YYYY}/{MM}/{urut}`.
 * Nomor urut di-reset setiap bulan dan diambil di dalam transaksi
 * ber-lock agar dua permintaan bersamaan tidak menghasilkan nomor kembar.
 */
class DocumentNumberService
{
    public function __construct(private readonly SettingService $settings) {}

    /**
     * @param  string  $settingKey  kunci format, mis. `document_prefix_booking`
     * @param  string  $table  tabel penyimpan nomor
     * @param  string  $column  kolom nomor dokumen
     */
    public function generate(
        string $settingKey,
        string $table,
        string $column,
        ?Carbon $date = null,
    ): string {
        $date ??= Carbon::now();
        $format = $this->settings->string($settingKey);
        $padding = (int) config('usc_vehicle_ops.numbering.sequence_padding', 3);

        $prefix = $this->render($format, $date, null);
        // Bagian sebelum placeholder {urut} menjadi pola pencarian nomor terakhir.
        $searchPrefix = substr($prefix, 0, strpos($prefix, '{urut}') ?: strlen($prefix));

        return DB::transaction(function () use ($table, $column, $searchPrefix, $format, $date, $padding) {
            $last = DB::table($table)
                ->where($column, 'like', $searchPrefix.'%')
                ->lockForUpdate()
                ->orderByDesc($column)
                ->value($column);

            $sequence = $last === null
                ? 1
                : ((int) $this->extractSequence($last)) + 1;

            return $this->render($format, $date, str_pad((string) $sequence, $padding, '0', STR_PAD_LEFT));
        });
    }

    /** Isi placeholder format penomoran. */
    private function render(string $format, Carbon $date, ?string $sequence): string
    {
        return str_replace(
            ['{YYYY}', '{YY}', '{MM}', '{DD}', '{urut}'],
            [
                $date->format('Y'),
                $date->format('y'),
                $date->format('m'),
                $date->format('d'),
                $sequence ?? '{urut}',
            ],
            $format,
        );
    }

    /** Ambil potongan angka terakhir dari sebuah nomor dokumen. */
    private function extractSequence(string $documentNumber): string
    {
        $segments = preg_split('/[^0-9]+/', $documentNumber, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return $segments === [] ? '0' : (string) end($segments);
    }
}
