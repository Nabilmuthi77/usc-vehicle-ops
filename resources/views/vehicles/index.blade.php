<x-app-layout title="Kendaraan">
    <x-slot name="header">
        <x-page-header title="Master Data Kendaraan" subtitle="Daftar seluruh unit kendaraan operasional" :crumbs="['Master Data', 'Kendaraan']">
            <x-slot name="actions">
                @can('viewAny', App\Models\Vehicle::class)
                    <a href="{{ route('vehicles.export', request()->query()) }}"
                       class="flex-1 sm:flex-none justify-center inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-700 px-3.5 py-2 text-sm font-medium text-white shadow-md shadow-emerald-600/30 hover:from-emerald-700 hover:to-teal-800 hover:shadow-lg hover:shadow-emerald-600/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                        <x-nav-icon name="document-text" class="h-4 w-4" />
                        Export Excel
                    </a>
                @endcan
                @can('kendaraan.tambah')
                    <x-btn-primary href="{{ route('vehicles.create') }}" icon="plus" class="flex-1 justify-center sm:flex-none">Tambah Kendaraan</x-btn-primary>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <x-card :padded="false">
        <form method="GET" action="{{ route('vehicles.index') }}">
            <x-filter-bar searchPlaceholder="Cari plat nomor, merk, atau tipe..." :value="$filters['search'] ?? ''">
                <x-filter-select name="status" :options="['' => 'Semua Status'] + $statuses"
                                 :selected="$filters['status'] ?? ''" />
                <x-filter-select name="ownership" :options="['' => 'Semua Kepemilikan'] + $ownerships"
                                 :selected="$filters['ownership'] ?? ''" />
                <x-filter-select name="department_id"
                                 :options="['' => 'Semua Departemen'] + $departments->pluck('name', 'id')->all()"
                                 :selected="$filters['department_id'] ?? ''" />
            </x-filter-bar>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Plat Nomor</th>
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">Kepemilikan</th>
                        <th class="px-5 py-3">Departemen</th>
                        <th class="px-5 py-3">Odometer</th>
                        <th class="px-5 py-3">Ganjil/Genap</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($vehicles as $v)
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <a href="{{ route('vehicles.show', $v) }}" class="font-medium text-gray-900 hover:text-usc-700">
                                    {{ $v->plate_number }}
                                </a>
                                @if ($v->service_status->needsAttention())
                                    <div class="mt-0.5 text-[11px] text-amber-600">{{ $v->service_status->label() }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">
                                {{ $v->full_name }}
                                <span class="text-gray-400">&middot; {{ $v->year }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                                    {{ $v->ownership->label() }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $v->department?->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 tabular-nums text-gray-600">
                                {{ number_format((int) $v->current_odometer, 0, ',', '.') }} km
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                @if ($v->plate_parity)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $v->plate_parity === 'Ganjil' ? 'bg-orange-50 text-orange-700' : 'bg-sky-50 text-sky-700' }}">
                                        {{ $v->plate_parity }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5"><x-status-badge :status="$v->status" /></td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                <a href="{{ route('vehicles.show', $v) }}"
                                   class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="8" message="Belum ada kendaraan yang cocok dengan filter." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$vehicles" />
    </x-card>
</x-app-layout>

