<?php

namespace App\Http\Requests;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;

/** FR-M4-15 — pembuatan permintaan servis ke vendor. */
class StoreServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $request = $this->route('service_request');

        return $request
            ? $this->user()->can('update', $request)
            : $this->user()->can('create', ServiceRequest::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'service_type_id' => ['nullable', 'exists:service_types,id'],
            'current_odometer' => ['required', 'integer', 'min:0'],
            'due_odometer' => ['nullable', 'integer', 'min:0'],
            'complaint_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'vehicle_id' => 'kendaraan',
            'vendor_id' => 'vendor',
            'service_type_id' => 'jenis servis',
            'current_odometer' => 'odometer saat ini',
            'due_odometer' => 'KM jatuh tempo',
            'complaint_note' => 'catatan keluhan',
        ];
    }
}
