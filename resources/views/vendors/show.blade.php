<x-app-layout title="Detail Vendor">
    <x-slot name="header">
        <x-page-header :title="$vendor->name" :subtitle="$vendor->type->label()" :crumbs="['Master Data', 'Vendor', $vendor->name]">
            <x-slot name="actions">
                @can('master-data.kelola')
                    <a href="{{ route('vendors.edit', $vendor) }}">
                        <x-btn-primary icon="wrench">Ubah Data</x-btn-primary>
                    </a>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-card title="Informasi Vendor">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Jenis</dt><dd class="text-gray-900">{{ $vendor->type->label() }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">PIC</dt><dd class="text-gray-900">{{ $vendor->pic_name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Telepon</dt><dd class="text-gray-900">{{ $vendor->pic_phone ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="text-gray-900">{{ $vendor->pic_email ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd><x-status-badge :status="$vendor->is_active ? 'aktif' : 'nonaktif'" /></dd></div>
                @if ($vendor->address)
                    <div><dt class="text-gray-500">Alamat</dt><dd class="mt-1 text-gray-900">{{ $vendor->address }}</dd></div>
                @endif
            </dl>
        </x-card>

        <x-card title="Permintaan Servis Terakhir" :padded="false" class="xl:col-span-2">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">No. Permintaan</th>
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">Dikirim</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($serviceRequests as $r)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-3">
                                <a href="{{ route('service-requests.show', $r) }}" class="font-medium text-gray-900 hover:text-usc-700">{{ $r->request_number }}</a>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $r->vehicle?->plate_number }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $r->sent_at?->format('d-m-Y') ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3"><x-status-badge :status="$r->status" /></td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="4" message="Belum ada permintaan servis." />
                    @endforelse
                </tbody>
            </table>
        </x-card>

        <x-card title="Kontrak Sewa" :padded="false" class="xl:col-span-3">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">No. Kontrak</th>
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">Masa Berlaku</th>
                        <th class="px-5 py-3 text-right">Biaya / Bulan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($vendor->rentalContracts as $c)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $c->contract_number }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $c->vehicles->pluck('plate_number')->implode(', ') ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                                {{ $c->start_date->format('d-m-Y') }} &ndash; {{ $c->end_date->format('d-m-Y') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-900">
                                Rp {{ number_format((float) $c->monthly_cost, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="4" message="Belum ada kontrak sewa." />
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>
</x-app-layout>
