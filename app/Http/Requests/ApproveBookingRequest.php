<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FR-M2-09 & FR-M2-10 — persetujuan sekaligus penugasan unit.
 *
 * BR-20 — `vehicle_id` wajib pada transisi ke status disetujui, sedangkan
 * `driver_id` opsional (kosong berarti `self_drive`).
 */
class ApproveBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('approve', $this->route('booking'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'assignment_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'vehicle_id' => 'kendaraan',
            'driver_id' => 'driver',
            'assignment_note' => 'catatan penugasan',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Pengajuan tidak dapat disetujui tanpa menetapkan kendaraan.',
        ];
    }
}
