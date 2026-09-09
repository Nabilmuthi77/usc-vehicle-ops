<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** FR-M4-01 — master jenis servis beserta interval bawaan. */
class ServiceType extends Model
{
    use LogsModelActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'default_interval_km',
        'default_interval_months',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_interval_km' => 'integer',
            'default_interval_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(VehicleServiceSchedule::class);
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
