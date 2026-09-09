<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Pelanggaran aturan bisnis USC_VEHICLE_OPS (PRD §8).
 *
 * Dilempar oleh service layer agar controller dapat menerjemahkannya
 * menjadi pesan kesalahan berbahasa Indonesia bagi pengguna.
 */
class BusinessRuleException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $ruleCode = null,
    ) {
        parent::__construct($message);
    }

    public static function rule(string $code, string $message): self
    {
        return new self($message, $code);
    }
}
