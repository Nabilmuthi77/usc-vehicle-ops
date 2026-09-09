<x-app-layout title="Servis Berkala">
    <x-slot name="header">
        <x-page-header title="Servis Berkala Berbasis KM" subtitle="Pemantauan jadwal servis seluruh kendaraan" :crumbs="['Operasional', 'Servis']">
            <x-slot name="actions">
                <a href="{{ route('service-requests.index') }}"
                   class="inline-flex w-[45%] sm:w-auto justify-center items-center gap-1.5 rounded-lg bg-white px-2 sm:px-3.5 py-2 text-sm font-medium text-gray-600 hover:text-usc-700 hover:bg-usc-50">
                    <x-nav-icon name="briefcase" class="h-4 w-4 shrink-0" />
                    <span class="truncate">Permintaan Vendor</span>
                </a>
                @can('servis.kelola')
                    <x-btn-primary href="{{ route('service.create') }}" icon="plus" class="w-[55%] sm:w-auto justify-center px-2 sm:px-3.5 text-sm">
                        <span class="truncate">Input Realisasi Servis</span>
                    </x-btn-primary>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <div class="mb-4 grid grid-cols-2 gap-3 sm:gap-4 sm:grid-cols-3">
        <x-stat-card label="Jatuh Tempo" :value="$summary['jatuh_tempo']" icon="exclamation" accent="critical" />
        <x-stat-card label="Segera Servis" :value="$summary['segera']" icon="wrench" accent="warning" />
        <x-stat-card label="Aman" :value="$summary['aman']" icon="check-circle" accent="good" />
    </div>

    <x-card :padded="false">
        <form method="GET" action="{{ route('service.index') }}">
            <x-filter-bar searchPlaceholder="Gunakan filter status di samping..." name="q" :value="$filters['q'] ?? ''">
                <x-filter-select name="status" :options="['' => 'Semua Status'] + $statuses"
                                 :selected="$filters['status'] ?? ''" />
                <x-filter-select name="ownership" :options="[
                    '' => 'Semua Kepemilikan',
                    'milik' => 'Milik',
                    'sewa' => 'Sewa',
                    'leasing' => 'Leasing',
                ]" :selected="$filters['ownership'] ?? ''" />
            </x-filter-bar>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">Jenis Servis</th>
                        <th class="px-5 py-3">Interval</th>
                        <th class="px-5 py-3">Sisa KM</th>
                        <th class="px-5 py-3">Estimasi Jatuh Tempo</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($schedules as $s)
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <a href="{{ route('service.history', $s->vehicle) }}" class="font-medium text-gray-900 hover:text-usc-700">
                                    {{ $s->vehicle?->plate_number }}
                                </a>
                                <p class="text-xs text-gray-500">
                                    {{ $s->vehicle?->ownership->label() }}
                                    @if ($s->vehicle?->isRented() && $s->vehicle?->rentalContract?->vendor)
                                        &middot; {{ $s->vehicle->rentalContract->vendor->name }}
                                    @endif
                                </p>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $s->serviceType?->name }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">
                                {{ $s->interval_km ? number_format((int) $s->interval_km, 0, ',', '.').' km' : '-' }}
                                @if ($s->interval_months)
                                    <p class="text-xs text-gray-400">/ {{ $s->interval_months }} bulan</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 tabular-nums {{ (int) $s->remaining_km <= 0 ? 'font-semibold text-red-600' : 'text-gray-900' }}">
                                {{ $s->remaining_km === null ? '-' : number_format((int) $s->remaining_km, 0, ',', '.').' km' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">
                                {{ $s->estimated_due_date?->format('d-m-Y') ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5"><x-status-badge :status="$s->status" /></td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('servis.kelola')
                                        {{-- BR-17 — tindak lanjut berbeda menurut kepemilikan kendaraan. --}}
                                        @if ($s->vehicle?->isRented())
                                            <a href="{{ route('service-requests.create', ['vehicle_id' => $s->vehicle_id, 'service_type_id' => $s->service_type_id]) }}"
                                               class="rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                                                Minta ke Vendor
                                            </a>
                                        @else
                                            <a href="{{ route('service.create', ['vehicle_id' => $s->vehicle_id, 'service_type_id' => $s->service_type_id, 'category' => 'berkala']) }}"
                                               class="rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                                                Input Servis
                                            </a>
                                        @endif
                                    @endcan
                                    <a href="{{ route('service.history', $s->vehicle) }}"
                                       class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="7" message="Belum ada jadwal servis." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$schedules" />
    </x-card>
</x-app-layout>

