<?php

namespace App\Http\Requests;

use App\Enums\CostBorneBy;
use App\Enums\ServiceCategory;
use App\Models\ServiceRecord;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * FR-M4-08 — input realisasi servis.
 *
 * BR-16 — kendaraan sewa/leasing otomatis `cost_borne_by = vendor`;
 * perubahan ke `perusahaan` hanya oleh Admin dan wajib beralasan.
 */
class StoreServiceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $record = $this->route('service_record');

        return $record
            ? $this->user()->can('update', $record)
            : $this->user()->can('create', ServiceRecord::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxSize = (int) config('usc_vehicle_ops.uploads.max_size_kb', 5120);
        $mimes = 'mimes:'.implode(',', config('usc_vehicle_ops.uploads.document_mimes', ['jpg', 'png', 'pdf']));

        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'category' => ['required', ServiceCategory::rule()],
            // Servis berkala wajib merujuk jenis servis agar jadwal berikutnya
            // dapat di-generate (FR-M4-09).
            'service_type_id' => [
                'nullable', 'exists:service_types,id',
                'required_if:category,'.ServiceCategory::Berkala->value,
            ],
            'service_request_id' => ['nullable', 'exists:service_requests,id'],
            'service_date' => ['required', 'date', 'before_or_equal:today'],
            'odometer' => ['required', 'integer', 'min:0'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'invoice_number' => ['nullable', 'string', 'max:60'],
            // FR-M4-19 — biaya opsional (kosong untuk kendaraan sewa).
            'total_cost' => ['nullable', 'numeric', 'min:0'],
            'cost_borne_by' => ['nullable', CostBorneBy::rule()],
            'cost_borne_by_reason' => ['nullable', 'string', 'max:2000'],
            'damage_category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', $mimes, 'max:'.$maxSize],
            'items' => ['nullable', 'array'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.type' => ['required', 'in:jasa,sparepart'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /** BR-16 — penanggung biaya kendaraan sewa hanya boleh diubah beralasan. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $vehicle = Vehicle::find($this->input('vehicle_id'));

            if ($vehicle === null || ! $vehicle->isRented()) {
                return;
            }

            $requested = $this->input('cost_borne_by');

            if ($requested !== CostBorneBy::Perusahaan->value) {
                return;
            }

            if (! $this->user()->can('overrideCostBorneBy', ServiceRecord::class)) {
                $validator->errors()->add(
                    'cost_borne_by',
                    'Hanya Admin yang dapat mengubah penanggung biaya kendaraan sewa menjadi perusahaan.',
                );

                return;
            }

            if (blank($this->input('cost_borne_by_reason'))) {
                $validator->errors()->add(
                    'cost_borne_by_reason',
                    'Sertakan alasan mengapa biaya servis kendaraan sewa ditanggung perusahaan.',
                );
            }
        });
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'vehicle_id' => 'kendaraan',
            'service_type_id' => 'jenis servis',
            'service_date' => 'tanggal servis',
            'odometer' => 'odometer saat servis',
            'vendor_id' => 'vendor bengkel',
            'invoice_number' => 'nomor invoice',
            'total_cost' => 'total biaya',
            'cost_borne_by' => 'penanggung biaya',
            'cost_borne_by_reason' => 'alasan penanggung biaya',
            'attachment' => 'lampiran nota',
        ];
    }
}
