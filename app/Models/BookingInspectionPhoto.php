<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** FR-M2-20 — foto kondisi kendaraan per posisi. */
class BookingInspectionPhoto extends Model
{
    protected $fillable = [
        'inspection_id',
        'photo_path',
        'position',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(BookingInspection::class, 'inspection_id');
    }
}
