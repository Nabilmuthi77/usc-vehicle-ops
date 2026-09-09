<?php

namespace App\Models;

use App\Enums\OdometerSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** FR-M4-03 — jejak pembaruan odometer dari seluruh sumber. */
class OdometerLog extends Model
{
    protected $fillable = [
        'vehicle_id',
        'odometer',
        'delta_km',
        'recorded_at',
        'source',
        'sourceable_type',
        'sourceable_id',
        'recorded_by',
        'is_correction',
        'correction_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'source' => OdometerSource::class,
            'odometer' => 'integer',
            'delta_km' => 'integer',
            'is_correction' => 'boolean',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** Dokumen sumber pencatatan (peminjaman, transaksi BBM, servis). */
    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }
}
