<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, LogsModelActivity, Notifiable, SoftDeletes;

    /** Nama peran baku sistem (PRD §5.1). */
    public const ROLE_ADMIN_SISTEM = 'Admin Sistem';

    public const ROLE_ADMIN_GA = 'Admin GA';

    public const ROLE_KARYAWAN = 'Karyawan';

    public const ROLE_DRIVER = 'Driver';

    public const ROLE_VIEWER = 'Viewer';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'department_id',
        'password',
        'is_active',
        'photo_profile',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ------------------------------------------------------------------
    // Relasi
    // ------------------------------------------------------------------

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class);
    }

    /** Peminjaman yang diajukan pengguna ini. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'requester_id');
    }

    /** Klaim BBM yang diajukan pengguna ini (FR-M3-13). */
    public function fuelClaims(): HasMany
    {
        return $this->hasMany(FuelTransaction::class, 'claimant_id');
    }

    public function reimbursementBatches(): HasMany
    {
        return $this->hasMany(ReimbursementBatch::class, 'claimant_id');
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    // ------------------------------------------------------------------
    // Scope & helper
    // ------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** FR-M2-08 & FR-M2-17 — penerima antrean approval peminjaman. */
    public function scopeApprovers(Builder $query): Builder
    {
        return $query->active()->role([self::ROLE_ADMIN_GA, self::ROLE_ADMIN_SISTEM]);
    }

    public function isAdminGa(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN_GA);
    }

    public function isAdminSistem(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN_SISTEM);
    }

    /** FR-M2-17 — Admin Sistem berperan sebagai backup approver. */
    public function canApproveBookings(): bool
    {
        return $this->hasAnyRole([self::ROLE_ADMIN_GA, self::ROLE_ADMIN_SISTEM]);
    }

    /**
     * FR-M6-06 — Karyawan & Driver hanya melihat aktivitas dirinya sendiri.
     */
    public function seesOnlyOwnRecords(): bool
    {
        return ! $this->hasAnyRole([
            self::ROLE_ADMIN_SISTEM,
            self::ROLE_ADMIN_GA,
            self::ROLE_VIEWER,
        ]);
    }

    /** FR-M7-04 — kanal notifikasi efektif untuk sebuah kejadian. */
    public function notificationChannelsFor(string $eventKey): array
    {
        $preference = $this->notificationPreferences
            ->firstWhere('event_key', $eventKey);

        $channels = [];

        if (! $preference || $preference->via_database) {
            $channels[] = 'database';
            $channels[] = 'broadcast';
        }

        if ((! $preference || $preference->via_mail) && filled($this->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }
}
