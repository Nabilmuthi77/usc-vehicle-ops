<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FR-M3-09 — verifikasi klaim BBM oleh Admin GA.
 *
 * Koreksi nominal wajib disertai catatan koreksi yang tercatat.
 */
class VerifyFuelClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('verify', $this->route('fuel'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'approved_amount' => ['nullable', 'numeric', 'gt:0'],
            'correction_note' => [
                'nullable', 'string', 'max:2000',
                // Wajib bila nominal disetujui berbeda dari nominal diajukan.
                function ($attribute, $value, $fail) {
                    $approved = $this->input('approved_amount');
                    $claimed = (float) $this->route('fuel')->total_cost;

                    if ($approved !== null && (float) $approved !== $claimed && blank($value)) {
                        $fail('Catatan koreksi wajib diisi bila nominal klaim diubah.');
                    }
                },
            ],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'approved_amount' => 'nominal disetujui',
            'correction_note' => 'catatan koreksi',
        ];
    }
}
