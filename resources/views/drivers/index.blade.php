<x-app-layout title="Driver">
    <x-slot name="header">
        <x-page-header title="Master Data Driver" subtitle="Daftar pengemudi dan masa berlaku SIM" :crumbs="['Master Data', 'Driver']">
            <x-slot name="actions">
                @can('master-data.kelola')
                    <a href="{{ route('drivers.create') }}">
                        <x-btn-primary icon="plus">Tambah Driver</x-btn-primary>
                    </a>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <x-card :padded="false">
        <form method="GET" action="{{ route('drivers.index') }}">
            <x-filter-bar searchPlaceholder="Cari nama, NIK, atau nomor SIM..." :value="$filters['search'] ?? ''">
                <x-filter-select name="is_active" :options="['' => 'Semua Status', '1' => 'Aktif', '0' => 'Nonaktif']"
                                 :selected="$filters['is_active'] ?? ''" />
            </x-filter-bar>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">NIK</th>
                        <th class="px-5 py-3">SIM</th>
                        <th class="px-5 py-3">Masa Berlaku</th>
                        <th class="px-5 py-3">Kontak</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($drivers as $d)
                        @php $daysToExpiry = (int) now()->startOfDay()->diffInDays($d->license_expiry, false); @endphp
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <a href="{{ route('drivers.show', $d) }}" class="font-medium text-gray-900 hover:text-usc-700">
                                    {{ $d->name }}
                                </a>
                                @if ($d->user)
                                    <p class="text-xs text-gray-500">{{ $d->user->email }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $d->employee_id ?? '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">
                                {{ $d->license_type }}
                                <p class="text-xs text-gray-400">{{ $d->license_number }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <span class="{{ $daysToExpiry < 0 ? 'font-semibold text-red-600' : ($daysToExpiry <= 30 ? 'font-medium text-amber-600' : 'text-gray-600') }}">
                                    {{ $d->license_expiry->format('d-m-Y') }}
                                </span>
                                <p class="text-[11px] text-gray-400">
                                    {{ $daysToExpiry < 0 ? 'Sudah lewat '.abs($daysToExpiry).' hari' : 'Sisa '.$daysToExpiry.' hari' }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $d->phone ?? '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <x-status-badge :status="$d->is_active ? 'aktif' : 'nonaktif'" />
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                @can('master-data.kelola')
                                    <a href="{{ route('drivers.edit', $d) }}"
                                       class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                        Ubah
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="7" message="Belum ada data driver." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$drivers" />
    </x-card>
</x-app-layout>

