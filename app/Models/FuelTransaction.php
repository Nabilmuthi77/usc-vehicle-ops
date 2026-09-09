<?php

namespace App\Models;

use App\Enums\FuelClaimStatus;
use App\Enums\FuelType;
use App\Enums\PaymentMethod;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** FR-M3-01 s.d. FR-M3-14 — pengisian BBM sekaligus klaim reimbursement. */
class FuelTransaction extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'booking_id',
        'driver_id',
        'claimant_id',
        'transaction_datetime',
        'station_name',
        'station_vendor_id',
        'fuel_type',
        'liters',
        'price_per_liter',
        'total_cost',
        'approved_amount',
        'payment_method',
        'odometer',
        'is_full_tank',
        'receipt_number',
        'receipt_photo_path',
        'km_since_last_fill',
        'consumption_km_per_liter',
        'is_anomaly',
        'anomaly_reason',
        'status',
        'submitted_at',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'correction_note',
        'is_late_claim',
        'late_claim_approval_note',
        'reimbursement_batch_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_datetime' => 'datetime',
            'fuel_type' => FuelType::class,
            'payment_method' => PaymentMethod::class,
            'status' => FuelClaimStatus::class,
            'liters' => 'decimal:2',
            'price_per_liter' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'odometer' => 'integer',
            'km_since_last_fill' => 'integer',
            'consumption_km_per_liter' => 'decimal:2',
            'is_full_tank' => 'boolean',
            'is_anomaly' => 'boolean',
            'is_late_claim' => 'boolean',
            'anomaly_reason' => 'array',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    // ------------------------------------------------------------------
    // Relasi
    // ------------------------------------------------------------------

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function stationVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'station_vendor_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ReimbursementBatch::class, 'reimbursement_batch_id');
    }

    public function odometerLogs(): MorphMany
    {
        return $this->morphMany(OdometerLog::class, 'sourceable');
    }

    // ------------------------------------------------------------------
    // Aturan bisnis
    // ------------------------------------------------------------------

    /**
     * FR-M3-09 & BR-14 — nominal yang diakui perusahaan.
     * Memakai nominal terkoreksi bila Admin GA mengubahnya saat verifikasi.
     */
    public function getClaimableAmountAttribute(): float
    {
        return (float) ($this->approved_amount ?? $this->total_cost);
    }

    public function isEditableByClaimant(): bool
    {
        return $this->status->isEditableByClaimant();
    }

    public function isVerifiable(): bool
    {
        return $this->status === FuelClaimStatus::Diajukan;
    }

    // ------------------------------------------------------------------
    // Scope
    // ------------------------------------------------------------------

    /** BR-14 — hanya klaim terverifikasi & dibayar yang dihitung sebagai biaya. */
    public function scopeCountedAsCost(Builder $query): Builder
    {
        return $query->whereIn('status', [FuelClaimStatus::Terverifikasi, FuelClaimStatus::Dibayar]);
    }

    /** FR-M3-18 — klaim terverifikasi yang belum dibayar. */
    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->where('status', FuelClaimStatus::Terverifikasi);
    }

    public function scopeAwaitingVerification(Builder $query): Builder
    {
        return $query->where('status', FuelClaimStatus::Diajukan);
    }

    public function scopeFullTank(Builder $query): Builder
    {
        return $query->where('is_full_tank', true);
    }

    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('transaction_datetime', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('transaction_datetime', '<=', $to));
    }
}
