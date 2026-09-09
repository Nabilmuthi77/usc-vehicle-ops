<?php

namespace App\Http\Requests;

use App\Enums\DurationType;
use App\Models\Booking;
use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

/**
 * FR-M2-01 s.d. FR-M2-06 — pengajuan peminjaman oleh pemohon.
 *
 * BR-19 — kendaraan & driver sengaja tidak divalidasi di sini karena
 * penetapannya sepenuhnya kewenangan Admin GA pada tahap approval.
 */
class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Booking::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            // FR-M2-06 — tidak dapat mengajukan untuk tanggal yang sudah lewat.
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'destination' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:2000'],
            'odd_even_zone' => ['required', 'boolean'],
            'duration_type' => ['required', DurationType::rule()],
            // FR-M2-05 — catatan tambahan bersifat opsional.
            'additional_note' => ['nullable', 'string', 'max:2000'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'booking_date' => 'tanggal pemakaian',
            'destination' => 'tujuan',
            'purpose' => 'keperluan',
            'odd_even_zone' => 'melewati area ganjil–genap',
            'duration_type' => 'durasi',
            'additional_note' => 'catatan tambahan',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'booking_date.after_or_equal' => 'Tanggal pemakaian tidak boleh sebelum hari ini.',
        ];
    }

    /**
     * FR-M2-06 — pengajuan minimal H-1; lebih mendadak dari itu tetap
     * diterima namun otomatis ditandai `urgent`.
     */
    protected function passedValidation(): void
    {
        $leadDays = app(SettingService::class)->integer('booking_lead_days', 1);
        $minimumDate = Carbon::today()->addDays($leadDays);

        $this->merge([
            'is_urgent' => Carbon::parse($this->input('booking_date'))->lt($minimumDate),
        ]);
    }

    /** @return array<string, mixed> */
    public function validatedData(): array
    {
        return array_merge($this->validated(), [
            'is_urgent' => (bool) $this->input('is_urgent'),
        ]);
    }
}
