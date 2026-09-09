<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\DurationType;
use App\Enums\InspectionType;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/** FR-M2-01 s.d. FR-M2-27 — peminjaman kendaraan operasional. */
class Booking extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'requester_id',
        'department_id',
        'booking_date',
        'destination',
        'purpose',
        'odd_even_zone',
        'duration_type',
        'additional_note',
        'is_urgent',
        'vehicle_id',
        'driver_id',
        'self_drive',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'assignment_note',
        'actual_start_datetime',
        'actual_end_datetime',
        'odometer_start',
        'odometer_end',
        'distance_traveled',
        'fuel_level_start',
        'fuel_level_end',
        'is_overdue',
        'correction_reason',
        'status',
        'cancelled_at',
        'cancellation_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'duration_type' => DurationType::class,
            'status' => BookingStatus::class,
            'odd_even_zone' => 'boolean',
            'is_urgent' => 'boolean',
            'self_drive' => 'boolean',
            'is_overdue' => 'boolean',
            'approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'overdue_notified_at' => 'datetime',
            'actual_start_datetime' => 'datetime',
            'actual_end_datetime' => 'datetime',
            'odometer_start' => 'integer',
            'odometer_end' => 'integer',
            'distance_traveled' => 'integer',
            'fuel_level_start' => 'decimal:2',
            'fuel_level_end' => 'decimal:2',
        ];
    }

    // ------------------------------------------------------------------
    // Relasi
    // ------------------------------------------------------------------

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(BookingInspection::class);
    }

    public function checkoutInspection(): HasOne
    {
        return $this->hasOne(BookingInspection::class)->where('type', InspectionType::Checkout);
    }

    public function checkinInspection(): HasOne
    {
        return $this->hasOne(BookingInspection::class)->where('type', InspectionType::Checkin);
    }

    public function fuelTransactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class);
    }

    public function tollTransactions(): HasMany
    {
        return $this->hasMany(TollTransaction::class);
    }

    // ------------------------------------------------------------------
    // Aturan bisnis
    // ------------------------------------------------------------------

    /** BR-20 — pengajuan disetujui wajib memiliki kendaraan. */
    public function hasVehicleAssigned(): bool
    {
        return $this->vehicle_id !== null;
    }

    /** FR-M2-15 — penugasan masih dapat diubah selama belum serah terima. */
    public function isAssignmentEditable(): bool
    {
        return $this->status->is(BookingStatus::MenungguApproval, BookingStatus::Disetujui);
    }

    public function isCancellable(): bool
    {
        return $this->status->isCancellable();
    }

    /** BR-04 — jarak tempuh peminjaman. */
    public function calculateDistance(): ?int
    {
        if ($this->odometer_start === null || $this->odometer_end === null) {
            return null;
        }

        return max(0, $this->odometer_end - $this->odometer_start);
    }

    /** FR-M2-16 — penanda pengajuan bertanggal hari ini atau besok. */
    public function isImminent(): bool
    {
        return $this->booking_date->betweenIncluded(Carbon::today(), Carbon::tomorrow());
    }

    /** BR-02 — apakah alokasi ini mengunci unit sepanjang tanggal. */
    public function locksVehicleForWholeDay(): bool
    {
        return $this->duration_type->locksVehicleForWholeDay();
    }

    // ------------------------------------------------------------------
    // Scope
    // ------------------------------------------------------------------

    public function scopeOfStatus(Builder $query, BookingStatus|string ...$statuses): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    /** FR-M2-16 — antrean approval Admin GA. */
    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::MenungguApproval);
    }

    /** Alokasi yang masih menahan unit pada tanggal tertentu. */
    public function scopeActiveOn(Builder $query, Carbon|string $date): Builder
    {
        return $query->whereDate('booking_date', $date)
            ->whereIn('status', [BookingStatus::Disetujui, BookingStatus::SedangDigunakan]);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('requester_id', $user->getKey());
    }

    /** FR-M2-24 — peminjaman lewat tanggal namun belum dikembalikan. */
    public function scopeOverdueCandidates(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::SedangDigunakan)
            ->whereDate('booking_date', '<', Carbon::today());
    }

    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('booking_date', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('booking_date', '<=', $to));
    }
}
