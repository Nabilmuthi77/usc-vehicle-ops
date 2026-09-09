<x-app-layout title="Detail Driver">
    <x-slot name="header">
        <x-page-header :title="$driver->name" :subtitle="$driver->license_type.' · berlaku s.d. '.$driver->license_expiry->format('d-m-Y')"
                       :crumbs="['Master Data', 'Driver', $driver->name]">
            <x-slot name="actions">
                @can('master-data.kelola')
                    <a href="{{ route('drivers.edit', $driver) }}">
                        <x-btn-primary icon="wrench">Ubah Data</x-btn-primary>
                    </a>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-card title="Data Driver">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">NIK</dt><dd class="text-gray-900">{{ $driver->employee_id ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Nomor SIM</dt><dd class="text-gray-900">{{ $driver->license_number }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Jenis SIM</dt><dd class="text-gray-900">{{ $driver->license_type }}</dd></div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Masa Berlaku</dt>
                    <dd class="{{ $driver->hasValidLicense() ? 'text-gray-900' : 'font-semibold text-red-600' }}">
                        {{ $driver->license_expiry->format('d-m-Y') }}
                    </dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500">Kontak</dt><dd class="text-gray-900">{{ $driver->phone ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Akun Sistem</dt><dd class="text-gray-900">{{ $driver->user?->email ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd><x-status-badge :status="$driver->is_active ? 'aktif' : 'nonaktif'" /></dd></div>
            </dl>
        </x-card>

        <x-card title="Penugasan Terakhir" :padded="false" class="xl:col-span-2">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">No. Pengajuan</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">Pemohon</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentBookings as $b)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-3">
                                <a href="{{ route('bookings.show', $b) }}" class="font-medium text-gray-900 hover:text-usc-700">{{ $b->booking_number }}</a>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $b->booking_date->format('d-m-Y') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $b->vehicle?->plate_number }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $b->requester?->name }}</td>
                            <td class="whitespace-nowrap px-5 py-3"><x-status-badge :status="$b->status" /></td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="5" message="Belum ada penugasan." />
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>
</x-app-layout>
