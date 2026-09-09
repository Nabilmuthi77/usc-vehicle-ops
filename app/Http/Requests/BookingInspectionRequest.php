<?php

namespace App\Http\Requests;

use App\Models\BookingInspection;
use Illuminate\Foundation\Http\FormRequest;

/**
 * FR-M2-20 & FR-M2-21 — serah terima (check-in) dan pengembalian (check-out).
 *
 * Saat check-out sistem mewajibkan foto kondisi minimal 4 sisi beserta
 * konfirmasi; saat check-in foto bersifat opsional namun catatan
 * kerusakan tetap wajib bila ada temuan.
 */
class BookingInspectionRequest extends FormRequest
{
    /** Empat sisi wajib difoto saat serah terima (FR-M2-20). */
    private const REQUIRED_PHOTO_POSITIONS = ['depan', 'belakang', 'kanan', 'kiri'];

    public function authorize(): bool
    {
        $ability = $this->isCheckout() ? 'checkOut' : 'checkIn';

        return $this->user()->can($ability, $this->route('booking'));
    }

    public function isCheckout(): bool
    {
        return $this->routeIs('*.check-out');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxSize = (int) config('usc_vehicle_ops.uploads.max_size_kb', 5120);
        $mimes = 'mimes:'.implode(',', config('usc_vehicle_ops.uploads.image_mimes', ['jpg', 'png']));

        $rules = [
            'odometer' => ['required', 'integer', 'min:0'],
            // Level BBM dicatat sebagai fraksi tangki 0–1.
            'fuel_level' => ['nullable', 'numeric', 'between:0,1'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['boolean'],
            'condition_notes' => ['nullable', 'string', 'max:2000'],
            'damage_found' => ['nullable', 'boolean'],
            'damage_notes' => ['nullable', 'string', 'max:2000', 'required_if:damage_found,1'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['file', $mimes, 'max:'.$maxSize],
        ];

        if ($this->isCheckout()) {
            $rules['confirmed'] = ['accepted'];

            foreach (self::REQUIRED_PHOTO_POSITIONS as $position) {
                $rules["photos.{$position}"] = ['required', 'file', $mimes, 'max:'.$maxSize];
            }
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        $attributes = [
            'odometer' => 'odometer',
            'fuel_level' => 'level BBM',
            'condition_notes' => 'catatan kondisi',
            'damage_notes' => 'catatan kerusakan',
            'confirmed' => 'konfirmasi serah terima',
        ];

        foreach (self::REQUIRED_PHOTO_POSITIONS as $position) {
            $attributes["photos.{$position}"] = "foto sisi {$position}";
        }

        return $attributes;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'confirmed.accepted' => 'Konfirmasi serah terima wajib dicentang.',
            'photos.*.required' => 'Foto kondisi kendaraan wajib diunggah.',
            'damage_notes.required_if' => 'Jelaskan kerusakan yang ditemukan.',
        ];
    }

    /** Item kelengkapan yang tersedia untuk ditampilkan pada form. */
    public function checklistItems(): array
    {
        return BookingInspection::CHECKLIST_ITEMS;
    }
}
