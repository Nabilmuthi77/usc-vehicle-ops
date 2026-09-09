<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** FR-M5-06 — jejak import mutasi transaksi tol. */
class TollImportBatch extends Model
{
    protected $fillable = [
        'toll_card_id',
        'file_name',
        'file_path',
        'column_mapping',
        'row_total',
        'row_imported',
        'row_skipped',
        'errors',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'column_mapping' => 'array',
            'errors' => 'array',
            'row_total' => 'integer',
            'row_imported' => 'integer',
            'row_skipped' => 'integer',
        ];
    }

    public function tollCard(): BelongsTo
    {
        return $this->belongsTo(TollCard::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(TollTransaction::class, 'import_batch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
