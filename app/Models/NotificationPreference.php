<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** FR-M7-04 — preferensi kanal notifikasi per pengguna. */
class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'event_key',
        'via_database',
        'via_mail',
    ];

    protected function casts(): array
    {
        return [
            'via_database' => 'boolean',
            'via_mail' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
