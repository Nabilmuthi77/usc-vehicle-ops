<?php

namespace App\Models;

use App\Enums\ServiceScheduleStatus;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/** FR-M4-04 & BR-06 — jadwal servis berkala per unit kendaraan. */
class VehicleServiceSchedule extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'vehicle_id',
        'service_type_id',
        'interval_km',
        'interval_months',
        'last_service_odometer',
        'last_service_date',
        'next_due_odometer',
        'next_due_date',
        'remaining_km',
        'estimated_due_date',
        'status',
        'status_changed_at',
        'last_notified_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'interval_km' => 'integer',
            'interval_months' => 'integer',
            'last_service_odometer' => 'integer',
            'last_service_date' => 'date',
            'next_due_odometer' => 'integer',
            'next_due_date' => 'date',
            'remaining_km' => 'integer',
            'estimated_due_date' => 'date',
            'status' => ServiceScheduleStatus::class,
            'status_changed_at' => 'datetime',
            'last_notified_at' => 'datetime',
            'is_active' => 'boolean',
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

    /** BR-07 — jatuh tempo waktu tercapai lebih dulu daripada KM. */
    public function isDueByTime(): bool
    {
        return $this->next_due_date !== null
            && $this->next_due_date->lte(Carbon::today());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** FR-M4-06 — jadwal yang perlu ditindaklanjuti, paling mendesak dahulu. */
    public function scopeNeedsAttention(Builder $query): Builder
    {
        return $query->active()
            ->whereIn('status', [ServiceScheduleStatus::JatuhTempo, ServiceScheduleStatus::Segera])
            ->orderByRaw("CASE status WHEN 'jatuh_tempo' THEN 0 ELSE 1 END")
            ->orderBy('remaining_km');
    }

    public function scopeOfStatus(Builder $query, ServiceScheduleStatus|string $status): Builder
    {
        return $query->where('status', $status);
    }
}
