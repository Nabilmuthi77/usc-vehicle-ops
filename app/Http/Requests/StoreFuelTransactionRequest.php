<?php

namespace App\Http\Requests;

use App\Enums\FuelType;
use App\Enums\PaymentMethod;
use App\Models\FuelTransaction;
use App\Services\FuelClaimService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

/**
 * FR-M3-01 s.d. FR-M3-08 — input transaksi pengisian BBM.
 *
 * BR-15 — kombinasi SPBU + nomor nota + tanggal bersifat unik untuk
 * mencegah satu nota diklaim dua kali.
 */
class StoreFuelTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $transaction = $this->route('fuel');

        return $transaction
            ? $this->user()->can('update', $transaction)
            : $this->user()->can('create', FuelTransaction::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxSize = (int) config('usc_vehicle_ops.uploads.max_size_kb', 5120);
        $mimes = 'mimes:'.implode(',', config('usc_vehicle_ops.uploads.document_mimes', ['jpg', 'png', 'pdf']));

        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'transaction_datetime' => ['required', 'date', 'before_or_equal:now'],
            'station_name' => ['required', 'string', 'max:255'],
            'station_vendor_id' => ['nullable', 'exists:vendors,id'],
            'fuel_type' => ['required', FuelType::rule()],
            'liters' => ['required', 'numeric', 'gt:0', 'max:9999'],
            'price_per_liter' => ['required', 'numeric', 'gt:0'],
            // FR-M3-02 — total dihitung otomatis, boleh dioverride manual.
            'total_cost' => ['nullable', 'numeric', 'gt:0'],
            'payment_method' => ['required', PaymentMethod::rule()],
            'odometer' => ['required', 'integer', 'min:0'],
            // FR-M3-03 — penanda pengisian penuh vs sebagian.
            'is_full_tank' => ['required', 'boolean'],
            'receipt_number' => ['required', 'string', 'max:60'],
            // FR-M3-07 — nota wajib saat pembuatan; saat menyunting boleh tetap.
            'receipt_photo' => [
                $this->routeIs('fuel.store') ? 'required' : 'nullable',
                'file', $mimes, 'max:'.$maxSize,
            ],
        ];
    }

    /** BR-15 — validasi nota duplikat lintas seluruh transaksi. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $duplicate = app(FuelClaimService::class)->findDuplicate(
                $this->input('station_name'),
                $this->input('receipt_number'),
                Carbon::parse($this->input('transaction_datetime')),
                $this->route('fuel')?->getKey(),
            );

            if ($duplicate !== null) {
                $validator->errors()->add('receipt_number', sprintf(
                    'Nota ini sudah pernah diklaim (%s, %s). Satu nota hanya boleh diklaim satu kali.',
                    $duplicate->station_name,
                    $duplicate->transaction_datetime->format('d-m-Y'),
                ));
            }
        });
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'vehicle_id' => 'kendaraan',
            'booking_id' => 'peminjaman terkait',
            'driver_id' => 'driver',
            'transaction_datetime' => 'tanggal & jam pengisian',
            'station_name' => 'SPBU',
            'fuel_type' => 'jenis BBM',
            'liters' => 'jumlah liter',
            'price_per_liter' => 'harga per liter',
            'total_cost' => 'total biaya',
            'payment_method' => 'metode bayar',
            'odometer' => 'odometer saat pengisian',
            'is_full_tank' => 'jenis pengisian',
            'receipt_number' => 'nomor nota',
            'receipt_photo' => 'foto nota',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'receipt_photo.required' => 'Foto nota wajib diunggah untuk mengajukan klaim.',
            'transaction_datetime.before_or_equal' => 'Tanggal pengisian tidak boleh di masa depan.',
        ];
    }
}
