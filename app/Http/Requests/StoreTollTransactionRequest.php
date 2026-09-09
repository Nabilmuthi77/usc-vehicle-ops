<?php

namespace App\Http\Requests;

use App\Enums\VehicleClass;
use App\Models\TollTransaction;
use Illuminate\Foundation\Http\FormRequest;

/** FR-M5-03 & FR-M5-04 — pencatatan transaksi tol. */
class StoreTollTransactionRequest extends FormRequest
{
    protected $errorBag = 'storeTransaction';

    public function authorize(): bool
    {
        $transaction = $this->route('toll_transaction');

        return $transaction
            ? $this->user()->can('update', $transaction)
            : $this->user()->can('create', TollTransaction::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'toll_card_id' => ['required', 'exists:toll_cards,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            // FR-M5-04 — pengaitan ke peminjaman untuk alokasi biaya.
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'transaction_datetime' => ['required', 'date', 'before_or_equal:now'],
            'entry_gate' => ['nullable', 'string', 'max:255'],
            'exit_gate' => ['nullable', 'string', 'max:255'],
            'road_section' => ['nullable', 'string', 'max:255'],
            'vehicle_class' => ['required', VehicleClass::rule()],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reference_number' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'toll_card_id' => 'kartu e-toll',
            'vehicle_id' => 'kendaraan',
            'booking_id' => 'peminjaman terkait',
            'transaction_datetime' => 'tanggal & jam transaksi',
            'entry_gate' => 'gerbang masuk',
            'exit_gate' => 'gerbang keluar',
            'road_section' => 'ruas tol',
            'vehicle_class' => 'golongan kendaraan',
            'amount' => 'tarif',
        ];
    }
}
