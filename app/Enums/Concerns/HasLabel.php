<?php

namespace App\Enums\Concerns;

/**
 * Helper bersama untuk seluruh enum status USC_VEHICLE_OPS (PRD Lampiran 16).
 *
 * Menyediakan daftar nilai, opsi untuk dropdown, dan aturan validasi
 * sehingga Form Request cukup memanggil `Enum::rule()`.
 */
trait HasLabel
{
    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return array<string, string> nilai => label Bahasa Indonesia */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /** Aturan validasi `in:...` untuk Form Request. */
    public static function rule(): string
    {
        return 'in:'.implode(',', self::values());
    }

    public function is(self ...$cases): bool
    {
        return in_array($this, $cases, true);
    }
}
