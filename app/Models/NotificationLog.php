<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** FR-M7-05 — riwayat pengiriman notifikasi. */
class NotificationLog extends Model
{
    protected $fillable = [
        'notification_id',
        'user_id',
        'event_key',
        'channel',
        'notification_class',
        'recipient',
        'subject',
        'status',
        'error_message',
        'sent_at',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'gagal');
    }
}
