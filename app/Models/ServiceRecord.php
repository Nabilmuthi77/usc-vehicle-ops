<?php

namespace App\Models;

use App\Enums\CostBorneBy;
use App\Enums\ServiceCategory;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** FR-M4-08 s.d. FR-M4-11 — realisasi servis kendaraan. */
class ServiceRecord extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'service_type_id',
        'service_request_id',
        'category',
        'service_date',
        'odometer',
        'vendor_id',
        'invoice_number',
        'total_cost',
        'cost_borne_by',
        'cost_borne_by_reason',
        'next_due_odometer',
        'damage_category',
        'description',
        'attachment_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'category' => ServiceCategory::class,
            'cost_borne_by' => CostBorneBy::class,
            'service_date' => 'date',
            'odometer' => 'integer',
            'next_due_odometer' => 'integer',
            'total_cost' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceRecordItem::class);
    }

    public function odometerLogs(): MorphMany
    {
        return $this->morphMany(OdometerLog::class, 'sourceable');
    }

    /** Total biaya dari rincian item; dipakai bila total tidak diisi manual. */
    public function calculateItemsTotal(): float
    {
        return (float) $this->items()->sum('subtotal');
    }

    /**
     * BR-09 — hanya biaya yang ditanggung perusahaan yang masuk perhitungan
     * biaya operasional.
     */
    public function getCompanyCostAttribute(): float
    {
        return $this->cost_borne_by->countsAsCompanyCost()
            ? (float) ($this->total_cost ?? 0)
            : 0.0;
    }

    /** BR-09 — biaya servis yang ditanggung perusahaan saja. */
    public function scopeBorneByCompany(Builder $query): Builder
    {
        return $query->where('cost_borne_by', CostBorneBy::Perusahaan);
    }

    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('service_date', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('service_date', '<=', $to));
    }
}
