<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** FR-M4-08 — rincian item pekerjaan & sparepart. */
class ServiceRecordItem extends Model
{
    protected $fillable = [
        'service_record_id',
        'item_name',
        'type',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // Subtotal selalu diturunkan dari kuantitas x harga satuan.
        static::saving(function (self $item) {
            $item->subtotal = round((float) $item->quantity * (float) $item->unit_price, 2);
        });
    }

    public function serviceRecord(): BelongsTo
    {
        return $this->belongsTo(ServiceRecord::class);
    }
}
