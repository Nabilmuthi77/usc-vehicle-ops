<x-app-layout title="Detail Klaim BBM">
    <x-slot name="header">
        <x-page-header :title="'Nota '.$transaction->receipt_number"
                       :subtitle="$transaction->station_name.' — '.$transaction->transaction_datetime->format('d-m-Y H:i')"
                       :crumbs="['Operasional', 'BBM', $transaction->receipt_number]" />
    </x-slot>


    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-card title="Rincian Transaksi" class="xl:col-span-2">
            <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                <div><dt class="text-gray-500">Status</dt><dd class="mt-1"><x-status-badge :status="$transaction->status" /></dd></div>
                <div><dt class="text-gray-500">Kendaraan</dt><dd class="mt-1 font-medium text-gray-900">{{ $transaction->vehicle?->plate_number }}</dd></div>
                <div><dt class="text-gray-500">Pengaju</dt><dd class="mt-1 text-gray-900">{{ $transaction->claimant?->name }}</dd></div>
                <div><dt class="text-gray-500">Jumlah</dt><dd class="mt-1 tabular-nums text-gray-900">{{ number_format((float) $transaction->liters, 2, ',', '.') }} L</dd></div>
                <div><dt class="text-gray-500">Harga/Liter</dt><dd class="mt-1 tabular-nums text-gray-900">Rp {{ number_format((float) $transaction->price_per_liter, 0, ',', '.') }}</dd></div>
                <div><dt class="text-gray-500">Total Diajukan</dt><dd class="mt-1 tabular-nums font-medium text-gray-900">Rp {{ number_format((float) $transaction->total_cost, 0, ',', '.') }}</dd></div>
                <div><dt class="text-gray-500">Odometer</dt><dd class="mt-1 tabular-nums text-gray-900">{{ number_format((int) $transaction->odometer, 0, ',', '.') }} km</dd></div>
                <div><dt class="text-gray-500">Jenis Pengisian</dt><dd class="mt-1 text-gray-900">{{ $transaction->is_full_tank ? 'Penuh' : 'Sebagian' }}</dd></div>
                <div>
                    <dt class="text-gray-500">Konsumsi</dt>
                    <dd class="mt-1 tabular-nums text-gray-900">
                        {{ $transaction->consumption_km_per_liter
                            ? number_format((float) $transaction->consumption_km_per_liter, 2, ',', '.').' km/L'
                            : '—' }}
                    </dd>
                </div>
            </dl>

            @if ($transaction->approved_amount !== null)
                <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                    <p><span class="font-medium">Nominal disetujui:</span>
                        Rp {{ number_format((float) $transaction->approved_amount, 0, ',', '.') }}</p>
                    @if (filled($transaction->correction_note))
                        <p class="mt-1">Catatan koreksi: {{ $transaction->correction_note }}</p>
                    @endif
                </div>
            @endif

            {{-- FR-M3-05 — penandaan anomali untuk review. --}}
            @if ($transaction->is_anomaly)
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    <p class="font-medium">Transaksi ditandai anomali</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($transaction->anomaly_reason ?? [] as $reason)
                            <li>{{ $reason }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($transaction->is_late_claim)
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    Klaim melewati batas waktu pengajuan dan memerlukan persetujuan khusus.
                </div>
            @endif

            @if (filled($transaction->rejection_reason))
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                    <p class="font-medium">Alasan penolakan</p>
                    <p>{{ $transaction->rejection_reason }}</p>
                </div>
            @endif

            @if ($previousFullTank)
                <p class="mt-4 text-xs text-gray-500">
                    Pengisian penuh sebelumnya: {{ $previousFullTank->transaction_datetime->format('d-m-Y') }}
                    pada odometer {{ number_format((int) $previousFullTank->odometer, 0, ',', '.') }} km.
                    @if ($historicalAverage)
                        Rata-rata konsumsi historis unit ini {{ number_format($historicalAverage, 2, ',', '.') }} km/L.
                    @endif
                </p>
            @endif
        </x-card>

        <div class="space-y-4">
            {{-- FR-M3-06 — pengajuan klaim oleh pengaju. --}}
            @can('submitClaim', $transaction)
                <x-card title="Ajukan Klaim">
                    <p class="mb-3 text-sm text-gray-600">
                        Klaim akan masuk antrean verifikasi Admin GA. Pastikan foto nota sudah terlampir.
                    </p>
                    <form method="POST" action="{{ route('fuel.submit-claim', $transaction) }}">
                        @csrf
                        <x-btn-primary icon="check-circle" type="submit">Ajukan Klaim</x-btn-primary>
                    </form>
                </x-card>
            @endcan

            {{-- FR-M3-09 — verifikasi Admin GA dengan opsi koreksi nominal. --}}
            @can('verify', $transaction)
                <x-card title="Verifikasi Klaim">
                    <form method="POST" action="{{ route('fuel.verify', $transaction) }}" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="approved_amount" value="Nominal Disetujui" />
                            <x-text-input id="approved_amount" name="approved_amount" type="number" step="0.01"
                                          class="mt-1 block w-full" :value="old('approved_amount', $transaction->total_cost)" />
                            <x-input-error :messages="$errors->get('approved_amount')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="correction_note" value="Catatan Koreksi (wajib bila nominal diubah)" />
                            <textarea id="correction_note" name="correction_note" rows="2"
                                      class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-usc-400 focus:ring-usc-400">{{ old('correction_note') }}</textarea>
                            <x-input-error :messages="$errors->get('correction_note')" class="mt-1" />
                        </div>
                        <x-btn-primary icon="check-circle" type="submit">Setujui Klaim</x-btn-primary>
                    </form>

                    <form method="POST" action="{{ route('fuel.reject', $transaction) }}" class="mt-4 space-y-3 border-t border-gray-100 pt-4">
                        @csrf
                        <x-input-label for="rejection_reason" value="Tolak Klaim (alasan wajib)" />
                        <textarea id="rejection_reason" name="rejection_reason" rows="2"
                                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-400 focus:ring-red-400">{{ old('rejection_reason') }}</textarea>
                        <x-input-error :messages="$errors->get('rejection_reason')" />
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                            Tolak Klaim
                        </button>
                    </form>
                </x-card>
            @endcan
            
            {{-- Quick action to create reimbursement batch --}}
            @if ($transaction->status === \App\Enums\FuelClaimStatus::Terverifikasi && !$transaction->reimbursement_batch_id)
                @can('create', \App\Models\ReimbursementBatch::class)
                    <x-card title="Tindakan Lanjut">
                        <p class="mb-3 text-sm text-gray-600">
                            Klaim ini sudah diverifikasi. Anda dapat langsung menyusun batch reimbursement untuk periode klaim ini.
                        </p>
                        <a href="{{ route('reimbursements.create', [
                            'claimant_id' => $transaction->claimant_id,
                            'period_start' => $transaction->transaction_datetime->startOfMonth()->format('Y-m-d'),
                            'period_end' => $transaction->transaction_datetime->endOfMonth()->format('Y-m-d'),
                        ]) }}" class="flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            <x-nav-icon name="folder-plus" class="h-4 w-4" />
                            Susun Batch Reimbursement
                        </a>
                    </x-card>
                @endcan
            @endif

            @if ($transaction->receipt_photo_path)
                <x-card title="Foto Nota" class="overflow-hidden">
                    <x-image-viewer 
                        url="{{ Storage::disk(config('USC Vehicle Ops.uploads.disk'))->url($transaction->receipt_photo_path) }}" 
                        alt="Foto nota {{ $transaction->receipt_number }}" 
                    />
                </x-card>
            @endif

            @can('update', $transaction)
                @if ($transaction->isEditableByClaimant())
                    <a href="{{ route('fuel.edit', $transaction) }}"
                       class="block rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Ubah Transaksi
                    </a>
                @endif
            @endcan
        </div>
    </div>
</x-app-layout>

