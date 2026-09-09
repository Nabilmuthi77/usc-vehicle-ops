<x-app-layout title="Riwayat Servis">
    <x-slot name="header">
        <x-page-header :title="'Kartu Riwayat — '.$vehicle->plate_number"
                       :subtitle="$vehicle->full_name.' · '.$vehicle->ownership->label()"
                       :crumbs="['Operasional', 'Servis', $vehicle->plate_number]" />
    </x-slot>


    {{-- FR-M4-12 — biaya perusahaan dipisahkan dari biaya vendor. --}}
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Odometer Saat Ini" value="{{ number_format((int) $vehicle->current_odometer, 0, ',', '.') }} km" icon="truck" accent="brand" />
        <x-stat-card label="Biaya Ditanggung Perusahaan" value="Rp {{ number_format($totalCompanyCost, 0, ',', '.') }}" icon="chart-bar" accent="warning" />
        <x-stat-card label="Biaya Ditanggung Vendor" value="Rp {{ number_format($totalVendorCost, 0, ',', '.') }}" icon="briefcase" accent="good" />
    </div>

    <x-card :padded="false" class="mb-4">
        <div class="border-b border-gray-100 px-5 py-3">
            <h3 class="text-sm font-semibold text-gray-900">Jadwal Servis Aktif</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-white/80 backdrop-blur">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="px-5 py-3">Jenis Servis</th>
                    <th class="px-5 py-3">Servis Terakhir</th>
                    <th class="px-5 py-3">Jatuh Tempo</th>
                    <th class="px-5 py-3 text-right">Sisa KM</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($schedules as $s)
                    <tr>
                        <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $s->serviceType?->name }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                            {{ $s->last_service_odometer ? number_format((int) $s->last_service_odometer, 0, ',', '.').' km' : '—' }}
                            @if ($s->last_service_date)
                                <p class="text-xs text-gray-400">{{ $s->last_service_date->format('d-m-Y') }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                            {{ $s->next_due_odometer ? number_format((int) $s->next_due_odometer, 0, ',', '.').' km' : '—' }}
                            @if ($s->next_due_date)
                                <p class="text-xs text-gray-400">/ {{ $s->next_due_date->format('d-m-Y') }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums {{ (int) $s->remaining_km <= 0 ? 'font-semibold text-red-600' : 'text-gray-900' }}">
                            {{ $s->remaining_km === null ? '—' : number_format((int) $s->remaining_km, 0, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3"><x-status-badge :status="$s->status" /></td>
                        <td class="whitespace-nowrap px-5 py-3 text-right">
                            @can('servis.kelola')
                                <a href="{{ route('service.edit-schedule', $s) }}" class="text-xs font-medium text-usc-700 hover:underline">Atur Interval</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <x-empty-row :colspan="6" message="Belum ada jadwal servis." />
                @endforelse
            </tbody>
        </table>
    </x-card>

    <x-card :padded="false">
        <div class="border-b border-gray-100 px-5 py-3">
            <h3 class="text-sm font-semibold text-gray-900">Riwayat Realisasi Servis</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-white/80 backdrop-blur">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="px-5 py-3">Jenis</th>
                    <th class="px-5 py-3">Vendor</th>
                    <th class="px-5 py-3 text-right">Odometer</th>
                    <th class="px-5 py-3 text-right">Biaya</th>
                    <th class="px-5 py-3">Ditanggung</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($records as $r)
                    <tr>
                        <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $r->service_date->format('d-m-Y') }}</td>
                        <td class="px-5 py-3 text-gray-900">
                            {{ $r->serviceType?->name ?? 'Insidental' }}
                            <p class="text-xs text-gray-500">{{ $r->category->label() }}</p>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $r->vendor?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">
                            {{ number_format((int) $r->odometer, 0, ',', '.') }} km
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-900">
                            {{ $r->total_cost !== null ? 'Rp '.number_format((float) $r->total_cost, 0, ',', '.') : '—' }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $r->cost_borne_by->countsAsCompanyCost() ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700' }}">
                                {{ $r->cost_borne_by->label() }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-right">
                            <div class="flex items-center justify-end space-x-3">
                                <a href="{{ route('service.show', $r) }}" class="text-xs font-medium text-usc-700 hover:underline">
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-empty-row :colspan="7" message="Belum ada riwayat servis." />
                @endforelse
            </tbody>
        </table>

        <x-pagination :paginator="$records" />
    </x-card>
</x-app-layout>
