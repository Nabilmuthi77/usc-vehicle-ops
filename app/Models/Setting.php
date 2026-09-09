<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

/** FR-M1-08 — pengaturan sistem bertipe. */
class Setting extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    /** Nilai yang sudah dikonversi sesuai tipe kolom. */
    public function getTypedValueAttribute(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'decimal' => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode((string) $this->value, true),
            default => $this->value,
        };
    }

    /** Ubah nilai PHP menjadi bentuk simpanan sesuai tipe. */
    public static function castForStorage(mixed $value, string $type): ?string
    {
        return match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => $value === null ? null : (string) $value,
        };
    }
}
