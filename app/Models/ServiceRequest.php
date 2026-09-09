<?php

namespace App\Models;

use App\Enums\ServiceRequestStatus;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/** FR-M4-15 s.d. FR-M4-20 — permintaan servis ke vendor kendaraan sewa. */
class ServiceRequest extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'request_number',
        'vehicle_id',
        'vendor_id',
        'service_type_id',
        'current_odometer',
        'due_odometer',
        'complaint_note',
        'status',
        'sent_at',
        'sent_to_email',
        'scheduled_date',
        'completed_date',
        'response_days',
        'downtime_days',
        'vendor_response_note',
        'escalated_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ServiceRequestStatus::class,
            'current_odometer' => 'integer',
            'due_odometer' => 'integer',
            'sent_at' => 'datetime',
            'scheduled_date' => 'date',
            'completed_date' => 'date',
            'escalated_at' => 'datetime',
            'response_days' => 'integer',
            'downtime_days' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function serviceRecord(): HasOne
    {
        return $this->hasOne(ServiceRecord::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** FR-M4-18 — lama vendor merespons sejak permintaan dikirim. */
    public function daysSinceSent(): ?int
    {
        if ($this->sent_at === null) {
            return null;
        }

        return (int) $this->sent_at->startOfDay()->diffInDays(Carbon::today());
    }

    public function canTransitionTo(ServiceRequestStatus $target): bool
    {
        return in_array($target, $this->status->allowedTransitions(), true);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            ServiceRequestStatus::Selesai,
            ServiceRequestStatus::Dibatalkan,
        ]);
    }

    /** FR-M4-20 — permintaan terkirim yang belum direspons vendor. */
    public function scopeAwaitingVendor(Builder $query): Builder
    {
        return $query->where('status', ServiceRequestStatus::Dikirim)
            ->whereNotNull('sent_at');
    }
}
