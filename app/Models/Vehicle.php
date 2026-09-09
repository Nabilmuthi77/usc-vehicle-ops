<?php

namespace App\Models;

use App\Enums\CostBorneBy;
use App\Enums\FuelType;
use App\Enums\Ownership;
use App\Enums\ServiceScheduleStatus;
use App\Enums\VehicleStatus;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/** FR-M1-02 & FR-M1-11 — master kendaraan operasional. */
class Vehicle extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'plate_number',
        'brand',
        'model',
        'year',
        'color',
        'chassis_number',
        'engine_number',
        'fuel_type',
        'tank_capacity',
        'transmission',
        'ownership',
        'rental_contract_id',
        'department_id',
        'initial_odometer',
        'current_odometer',
        'status',
        'photo_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'fuel_type' => FuelType::class,
            'ownership' => Ownership::class,
            'status' => VehicleStatus::class,
            'tank_capacity' => 'decimal:2',
            'initial_odometer' => 'integer',
            'current_odometer' => 'integer',
            'plate_last_digit' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // FR-M1-11 — digit terakhir pelat selalu diturunkan dari nomor polisi.
        static::saving(function (self $vehicle) {
            $vehicle->plate_last_digit = self::extractLastDigit($vehicle->plate_number);
        });
    }

    // ------------------------------------------------------------------
    // Paritas pelat (FR-M1-11, FR-M2-11, BR-18)
    // ------------------------------------------------------------------

    /**
     * Mengambil digit terakhir dari rangkaian angka pada nomor polisi.
     * Contoh: "B 1234 XI" -> 4. Mengembalikan null bila pelat tanpa angka.
     */
    public static function extractLastDigit(?string $plateNumber): ?int
    {
        if (blank($plateNumber)) {
            return null;
        }

        preg_match_all('/\d/', $plateNumber, $matches);

        $digits = $matches[0] ?? [];

        return $digits === [] ? null : (int) end($digits);
    }

    /** Label 'Ganjil' / 'Genap' untuk daftar kendaraan. */
    public function getPlateParityAttribute(): ?string
    {
        if ($this->plate_last_digit === null) {
            return null;
        }

        return $this->plate_last_digit % 2 === 0 ? 'Genap' : 'Ganjil';
    }

    /**
     * BR-18 — kendaraan sesuai aturan ganjil–genap bila paritas digit
     * terakhir pelat sama dengan paritas tanggal pemakaian.
     */
    public function matchesOddEvenRule(Carbon|string $date): bool
    {
        if ($this->plate_last_digit === null) {
            return false;
        }

        $day = $date instanceof Carbon ? $date->day : Carbon::parse($date)->day;

        return $this->plate_last_digit % 2 === $day % 2;
    }

    // ------------------------------------------------------------------
    // Relasi
    // ------------------------------------------------------------------

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function rentalContract(): BelongsTo
    {
        return $this->belongsTo(RentalContract::class);
    }

    public function tollCard(): HasOne
    {
        return $this->hasOne(TollCard::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    public function odometerLogs(): HasMany
    {
        return $this->hasMany(OdometerLog::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function fuelTransactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class);
    }

    public function serviceSchedules(): HasMany
    {
        return $this->hasMany(VehicleServiceSchedule::class);
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function tollTransactions(): HasMany
    {
        return $this->hasMany(TollTransaction::class);
    }

    // ------------------------------------------------------------------
    // Aturan bisnis
    // ------------------------------------------------------------------

    /** BR-01 — kendaraan servis/nonaktif tidak dapat dipinjam. */
    public function isBookable(): bool
    {
        return $this->status->isBookable();
    }

    /** BR-16 — penanggung biaya servis mengikuti kepemilikan. */
    public function defaultCostBorneBy(): CostBorneBy
    {
        return $this->ownership->defaultCostBorneBy();
    }

    public function isRented(): bool
    {
        return $this->ownership->requiresRentalContract();
    }

    /** Status servis paling mendesak di antara seluruh jadwal aktif. */
    public function getServiceStatusAttribute(): ServiceScheduleStatus
    {
        $statuses = $this->serviceSchedules
            ->where('is_active', true)
            ->pluck('status');

        if ($statuses->contains(ServiceScheduleStatus::JatuhTempo)) {
            return ServiceScheduleStatus::JatuhTempo;
        }

        if ($statuses->contains(ServiceScheduleStatus::Segera)) {
            return ServiceScheduleStatus::Segera;
        }

        return ServiceScheduleStatus::Aman;
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->brand} {$this->model}");
    }

    // ------------------------------------------------------------------
    // Scope
    // ------------------------------------------------------------------

    public function scopeBookable(Builder $query): Builder
    {
        return $query->whereIn('status', [VehicleStatus::Tersedia, VehicleStatus::Dipinjam]);
    }

    public function scopeOfStatus(Builder $query, VehicleStatus|string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeOfOwnership(Builder $query, Ownership|string $ownership): Builder
    {
        return $query->where('ownership', $ownership);
    }

    /** BR-18 — saring unit yang paritas pelatnya cocok dengan tanggal. */
    public function scopeMatchingOddEven(Builder $query, Carbon|string $date): Builder
    {
        $day = $date instanceof Carbon ? $date->day : Carbon::parse($date)->day;

        return $query->whereNotNull('plate_last_digit')
            ->whereRaw('plate_last_digit % 2 = ?', [$day % 2]);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('plate_number', 'like', "%{$term}%")
                ->orWhere('brand', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%");
        });
    }
}
