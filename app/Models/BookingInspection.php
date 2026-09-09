<?php

namespace App\Models;

use App\Enums\InspectionType;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** FR-M2-20 & FR-M2-21 — pemeriksaan serah terima dan pengembalian. */
class BookingInspection extends Model
{
    use LogsModelActivity;

    /** Item kelengkapan yang diperiksa (FR-M2-20). */
    public const CHECKLIST_ITEMS = ['ban_serep', 'dongkrak', 'p3k', 'stnk', 'apar'];

    /** Posisi foto kondisi kendaraan; 4 sisi pertama wajib saat check-out. */
    public const PHOTO_POSITIONS = ['depan', 'belakang', 'kanan', 'kiri', 'interior', 'odometer'];

    protected $fillable = [
        'booking_id',
        'type',
        'checklist',
        'odometer',
        'fuel_level',
        'condition_notes',
        'damage_found',
        'damage_notes',
        'confirmed',
        'inspected_by',
        'inspected_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => InspectionType::class,
            'checklist' => 'array',
            'odometer' => 'integer',
            'fuel_level' => 'decimal:2',
            'damage_found' => 'boolean',
            'confirmed' => 'boolean',
            'inspected_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(BookingInspectionPhoto::class, 'inspection_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }
}
