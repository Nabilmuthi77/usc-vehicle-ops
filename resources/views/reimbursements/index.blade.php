<x-app-layout title="Reimbursement">
    <x-slot name="header">
        <x-page-header title="Batch Reimbursement" subtitle="Penggabungan klaim BBM terverifikasi per periode per pengaju" :crumbs="['Operasional', 'Reimbursement']">
            <x-slot name="actions">
                @can('reimbursement.kelola')
                    <a href="{{ route('reimbursements.create') }}" class="w-full sm:w-auto">
                        <x-btn-primary icon="plus" class="w-full justify-center">Susun Batch</x-btn-primary>
                    </a>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <x-card :padded="false" class="mb-4">
        <form method="GET" action="{{ route('reimbursements.index') }}">
            <x-filter-bar searchPlaceholder="Cari no. batch, nama pengaju..." name="search" :value="$filters['search'] ?? ''">
                <x-filter-select name="status" :options="['' => 'Semua Status'] + $statuses"
                                 :selected="$filters['status'] ?? ''" />
            </x-filter-bar>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">No. Batch</th>
                        <th class="px-5 py-3">Pengaju</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3 text-right">Item</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($batches as $batch)
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3.5 font-medium text-gray-900">{{ $batch->batch_number }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $batch->claimant?->name }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">
                                {{ $batch->period_start->format('d-m-Y') }} &ndash; {{ $batch->period_end->format('d-m-Y') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right tabular-nums text-gray-600">{{ $batch->item_count }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right tabular-nums font-medium text-gray-900">
                                Rp {{ number_format((float) $batch->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5"><x-status-badge :status="$batch->status" /></td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                <a href="{{ route('reimbursements.show', $batch) }}"
                                   class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="7" message="Belum ada batch reimbursement." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$batches" />
    </x-card>

    {{-- FR-M3-18 — outstanding per pengaju beserta umur klaim. --}}
    <x-card :padded="false">
        <div class="border-b border-gray-100 px-5 py-3">
            <h3 class="text-sm font-semibold text-gray-900">Outstanding Reimbursement</h3>
            <p class="text-xs text-gray-500">Klaim terverifikasi yang belum masuk batch atau belum dibayar.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Pengaju</th>
                        <th class="px-5 py-3 text-right">Jumlah Klaim</th>
                        <th class="px-5 py-3 text-right">Total Nominal</th>
                        <th class="px-5 py-3 text-right">Umur Klaim Terlama</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($outstanding as $row)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $row['claimant'] }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">{{ $row['item_count'] }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums font-medium text-gray-900">
                                Rp {{ number_format($row['total_amount'], 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">{{ $row['age_days'] }} hari</td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="4" message="Tidak ada klaim outstanding." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>
