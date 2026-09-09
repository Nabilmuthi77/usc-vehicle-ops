<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * FR-M1-10 & BR-11 — audit log seluruh aksi create/update/delete.
 *
 * Mencatat nilai lama/baru untuk atribut yang benar-benar berubah saja
 * agar tabel `activity_log` tidak membengkak oleh penyimpanan berulang.
 */
trait LogsModelActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName(class_basename(static::class));
    }

    /** Deskripsi berbahasa Indonesia untuk halaman audit log. */
    public function getDescriptionForEvent(string $eventName): string
    {
        $label = match ($eventName) {
            'created' => 'dibuat',
            'updated' => 'diubah',
            'deleted' => 'dihapus',
            'restored' => 'dipulihkan',
            default => $eventName,
        };

        return class_basename(static::class).' '.$label;
    }
}
