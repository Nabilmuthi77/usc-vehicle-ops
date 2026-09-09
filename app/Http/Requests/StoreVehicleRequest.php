<?php

namespace App\Http\Requests;

use App\Enums\FuelType;
use App\Enums\Ownership;
use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** FR-M1-02 — CRUD master kendaraan. */
class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $vehicle = $this->route('vehicle');

        return $vehicle
            ? $this->user()->can('update', $vehicle)
            : $this->user()->can('create', Vehicle::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->getKey();
        $maxSize = (int) config('usc_vehicle_ops.uploads.max_size_kb', 5120);
        $mimes = 'mimes:'.implode(',', config('usc_vehicle_ops.uploads.image_mimes', ['jpg', 'png']));

        return [
            'plate_number' => [
                'required', 'string', 'max:20',
                Rule::unique('vehicles', 'plate_number')->ignore($vehicleId)->whereNull('deleted_at'),
            ],
            'brand' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'year' => ['required', 'integer', 'between:1980,'.(date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:40'],
            'chassis_number' => ['nullable', 'string', 'max:60'],
            'engine_number' => ['nullable', 'string', 'max:60'],
            'fuel_type' => ['required', FuelType::rule()],
            'tank_capacity' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'transmission' => ['nullable', 'string', 'max:20'],
            'ownership' => ['required', Ownership::rule()],
            // BR-16 — kendaraan sewa/leasing wajib terikat kontrak vendor.
            'rental_contract_id' => [
                'nullable', 'exists:rental_contracts,id',
                Rule::requiredIf(fn () => in_array($this->input('ownership'), ['sewa', 'leasing'], true)),
            ],
            'department_id' => ['nullable', 'exists:departments,id'],
            'initial_odometer' => ['required', 'integer', 'min:0'],
            'current_odometer' => ['required', 'integer', 'min:0', 'gte:initial_odometer'],
            'status' => ['required', VehicleStatus::rule()],
            'photo' => ['nullable', 'file', $mimes, 'max:'.$maxSize],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'plate_number' => 'nomor polisi',
            'brand' => 'merk',
            'model' => 'tipe',
            'year' => 'tahun',
            'fuel_type' => 'jenis BBM',
            'tank_capacity' => 'kapasitas tangki',
            'ownership' => 'kepemilikan',
            'rental_contract_id' => 'kontrak sewa',
            'department_id' => 'departemen pemegang',
            'initial_odometer' => 'odometer awal',
            'current_odometer' => 'odometer terakhir',
            'photo' => 'foto kendaraan',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'plate_number.unique' => 'Nomor polisi ini sudah terdaftar.',
            'rental_contract_id.required' => 'Kendaraan sewa/leasing wajib dikaitkan dengan kontrak sewa.',
            'current_odometer.gte' => 'Odometer terakhir tidak boleh lebih kecil dari odometer awal.',
        ];
    }
}
