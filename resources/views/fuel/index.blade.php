<x-app-layout title="Pemakaian BBM">
    <x-slot name="header">
        <x-page-header title="Pemakaian BBM" subtitle="Klaim reimbursement bahan bakar per driver" :crumbs="['Operasional', 'BBM']">
            <x-slot name="actions">
                @unless(auth()->user()->hasRole('Viewer'))
                    <a href="{{ route('fuel.my-claims') }}"
                       class="inline-flex w-[35%] sm:w-auto justify-center items-center gap-1.5 rounded-lg bg-white px-2 sm:px-3.5 py-2 text-sm font-medium text-gray-600 hover:text-usc-700 hover:bg-usc-50">
                        <x-nav-icon name="droplet" class="h-4 w-4 shrink-0" />
                        <span class="truncate">Klaim Saya</span>
                    </a>
                @endunless
                @can('bbm.ajukan-klaim')
                    <x-btn-primary href="{{ route('fuel.create') }}" icon="plus" class="w-[65%] sm:w-auto justify-center px-2 sm:px-3.5 text-sm">
                        <span class="truncate">Input Pengisian BBM</span>
                    </x-btn-primary>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <div class="mb-4 grid grid-cols-2 gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Menunggu Verifikasi" value="Rp {{ number_format($summary['diajukan'], 0, ',', '.') }}" icon="exclamation" accent="warning" />
        <x-stat-card label="Terverifikasi (Outstanding)" value="Rp {{ number_format($summary['outstanding'], 0, ',', '.') }}" icon="chart-bar" accent="brand" />
        <x-stat-card label="Sudah Dibayar" value="Rp {{ number_format($summary['dibayar'], 0, ',', '.') }}" icon="check-circle" accent="good" />
        <x-stat-card label="Ditandai Anomali" value="{{ $summary['anomali'] }} transaksi" icon="exclamation" accent="danger" />
    </div>

    <x-card :padded="false">
        <form method="GET" action="{{ route('fuel.index') }}">
            <x-filter-bar searchPlaceholder="Cari SPBU, nota, atau kendaraan..." :value="$filters['search'] ?? ''">
                <x-filter-select name="status" :options="['' => 'Semua Status'] + $statuses"
                                 :selected="$filters['status'] ?? ''" />
                <x-filter-select name="vehicle_id"
                                 :options="['' => 'Semua Kendaraan'] + $vehicles->pluck('plate_number', 'id')->all()"
                                 :selected="$filters['vehicle_id'] ?? ''" />
            </x-filter-bar>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Kendaraan / Driver</th>
                        <th class="px-5 py-3">SPBU</th>
                        <th class="px-5 py-3">Liter &amp; Konsumsi</th>
                        <th class="px-5 py-3">Total Biaya</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($transactions as $t)
                        <tr class="hover:bg-usc-50/50">
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">
                                {{ $t->transaction_datetime->format('d-m-Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $t->vehicle?->plate_number }}</p>
                                <p class="text-xs text-gray-500">{{ $t->driver?->name ?? $t->claimant?->name }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">
                                {{ $t->station_name }}
                                <p class="text-xs text-gray-400">Nota {{ $t->receipt_number }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <p class="tabular-nums text-gray-900">{{ number_format((float) $t->liters, 1, ',', '.') }} L</p>
                                <p class="text-xs text-gray-500">
                                    {{ $t->is_full_tank ? 'Penuh (full tank)' : 'Sebagian (partial)' }}
                                    @if ($t->consumption_km_per_liter)
                                        &middot; {{ number_format((float) $t->consumption_km_per_liter, 2, ',', '.') }} km/L
                                    @endif
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 tabular-nums font-medium text-gray-900">
                                Rp {{ number_format($t->claimable_amount, 0, ',', '.') }}
                                @if ($t->approved_amount !== null && (float) $t->approved_amount !== (float) $t->total_cost)
                                    <p class="text-[11px] font-normal text-gray-400">
                                        diajukan Rp {{ number_format((float) $t->total_cost, 0, ',', '.') }}
                                    </p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <x-status-badge :status="$t->status" />
                                @if ($t->is_anomaly)
                                    <div class="mt-1 flex items-center gap-1 text-[11px] text-amber-600">
                                        <x-nav-icon name="exclamation" class="h-3.5 w-3.5" />
                                        <span class="max-w-[160px] truncate" title="{{ implode(' ', $t->anomaly_reason ?? []) }}">
                                            {{ implode(' ', $t->anomaly_reason ?? []) }}
                                        </span>
                                    </div>
                                @endif
                                @if (filled($t->rejection_reason))
                                    <p class="mt-1 max-w-[160px] truncate text-[11px] text-gray-400" title="{{ $t->rejection_reason }}">
                                        {{ $t->rejection_reason }}
                                    </p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                <a href="{{ route('fuel.show', $t) }}"
                                   class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                    {{ $t->isVerifiable() && auth()->user()->can('bbm.verifikasi-klaim') ? 'Verifikasi' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="7" message="Belum ada transaksi BBM yang cocok dengan filter." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$transactions" />
    </x-card>
</x-app-layout>


