<x-app-layout title="Susun Batch Reimbursement">
    <x-slot name="header">
        <x-page-header title="Susun Batch Reimbursement" subtitle="Gabungkan klaim terverifikasi milik satu pengaju pada satu periode" :crumbs="['Operasional', 'Reimbursement', 'Susun']" />
    </x-slot>

    @php
        $claimantOptions = ['' => '— Pilih pengaju —'];
        foreach($claimants as $c) {
            $claimantOptions[$c->id] = $c->name;
        }
    @endphp

    <x-card class="mb-4">
        <form method="GET" action="{{ route('reimbursements.create') }}" class="flex flex-wrap items-end gap-3">
            <div class="w-full flex-1 sm:w-auto min-w-[250px]">
                <x-input-label for="claimant_id" value="Pengaju" />
                <div class="mt-1">
                    <x-filter-select name="claimant_id" id="claimant_id" :options="$claimantOptions" :selected="$selectedClaimant?->id ?? ''" containerClass="w-full" class="border-slate-200" required />
                </div>
            </div>
            <div class="w-full flex-1 sm:w-auto min-w-[250px]">
                <x-input-label for="period_start" value="Periode Mulai" />
                <div class="mt-1">
                    <x-date-picker id="period_start" name="period_start" :value="$periodStart" />
                </div>
            </div>
            <div class="w-full flex-1 sm:w-auto min-w-[250px]">
                <x-input-label for="period_end" value="Periode Selesai" />
                <div class="mt-1">
                    <x-date-picker id="period_end" name="period_end" :value="$periodEnd" />
                </div>
            </div>
            <x-btn-primary type="submit" class="w-full sm:w-auto justify-center">
                Tampilkan Klaim
            </x-btn-primary>
        </form>
    </x-card>

    @if ($selectedClaimant)
        <x-card :padded="false">
            <div class="border-b border-gray-100 px-5 py-3">
                <h3 class="text-sm font-semibold text-gray-900">
                    Klaim Terverifikasi — {{ $selectedClaimant->name }}
                </h3>
                <p class="text-xs text-gray-500">{{ $eligibleClaims->count() }} klaim siap digabung ke batch.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-white/80 backdrop-blur">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Kendaraan</th>
                            <th class="px-5 py-3 min-w-[300px]">SPBU / Nota</th>
                            <th class="px-5 py-3 text-right min-w-[200px]">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($eligibleClaims as $claim)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $claim->transaction_datetime->format('d-m-Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-900">{{ $claim->vehicle?->plate_number }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $claim->station_name }} &middot; {{ $claim->receipt_number }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-900">
                                    Rp {{ number_format($claim->claimable_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <x-empty-row :colspan="4" message="Tidak ada klaim terverifikasi yang belum masuk batch pada periode ini." />
                        @endforelse
                        @if ($eligibleClaims->isNotEmpty())
                            <tr class="bg-gray-50 font-semibold">
                                <td colspan="3" class="px-5 py-3 text-right text-gray-700">Total</td>
                                <td class="px-5 py-3 text-right tabular-nums text-gray-900">
                                    Rp {{ number_format($eligibleClaims->sum(fn ($c) => $c->claimable_amount), 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if ($eligibleClaims->isNotEmpty())
                <form method="POST" action="{{ route('reimbursements.store') }}" class="flex justify-end border-t border-gray-100 px-5 py-3">
                    @csrf
                    <input type="hidden" name="claimant_id" value="{{ $selectedClaimant->id }}">
                    <input type="hidden" name="period_start" value="{{ $periodStart }}">
                    <input type="hidden" name="period_end" value="{{ $periodEnd }}">
                    <x-btn-primary icon="check-circle" type="submit" class="w-full sm:w-auto justify-center">Buat Batch</x-btn-primary>
                </form>
            @endif
        </x-card>
    @endif
</x-app-layout>
