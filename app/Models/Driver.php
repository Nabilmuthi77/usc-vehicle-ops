<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\DurationType;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/** FR-M1-04 — master driver beserta masa berlaku SIM. */
class Driver extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'employee_id',
        'license_number',
        'license_type',
        'license_expiry',
        'phone',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function fuelTransactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class);
    }

    public function hasValidLicense(?Carbon $onDate = null): bool
    {
        return $this->license_expiry->gte($onDate ?? Carbon::today());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** FR-M2-13 — hanya driver ber-SIM berlaku yang boleh ditugaskan. */
    public function scopeWithValidLicense(Builder $query, ?Carbon $onDate = null): Builder
    {
        return $query->whereDate('license_expiry', '>=', $onDate ?? Carbon::today());
    }

    /**
     * BR-21 — driver tidak boleh menerima dua penugasan `seharian_standby`
     * pada tanggal yang sama. Scope mengecualikan satu peminjaman agar
     * penyuntingan penugasan tidak bentrok dengan dirinya sendiri.
     */
    public function scopeAvailableOn(
        Builder $query,
        Carbon|string $date,
        DurationType $durationType,
        ?int $exceptBookingId = null,
    ): Builder {
        if (! $durationType->locksVehicleForWholeDay()) {
            return $query;
        }

        return $query->whereDoesntHave('bookings', function (Builder $booking) use ($date, $exceptBookingId) {
            $booking->whereDate('booking_date', $date)
                ->where('duration_type', DurationType::SeharianStandby)
                ->whereIn('status', [BookingStatus::Disetujui, BookingStatus::SedangDigunakan])
                ->when($exceptBookingId, fn (Builder $q) => $q->whereKeyNot($exceptBookingId));
        });
    }
}
