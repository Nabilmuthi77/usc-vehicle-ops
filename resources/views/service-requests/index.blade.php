<x-app-layout title="Permintaan Servis Vendor">
    <x-slot name="header">
        <x-page-header title="Permintaan Servis ke Vendor" subtitle="Terutama untuk kendaraan sewa & leasing" :crumbs="['Operasional', 'Permintaan Servis']">
            <x-slot name="actions">
                @can('permintaan-servis.kelola')
                    <a href="{{ route('service-requests.create') }}" class="flex w-full sm:w-auto">
                        <x-btn-primary icon="plus" class="w-full justify-center">Buat Permintaan</x-btn-primary>
                    </a>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <x-card :padded="false" class="mb-4">
        <form method="GET" action="{{ route('service-requests.index') }}">
            <x-filter-bar searchPlaceholder="Cari no. permintaan, plat, vendor..." name="search" :value="$filters['search'] ?? ''">
                <x-filter-select name="status" :options="['' => 'Semua Status'] + $statuses"
                                 :selected="$filters['status'] ?? ''" />
                <x-filter-select name="vendor_id" :options="['' => 'Semua Vendor'] + $vendors->pluck('name', 'id')->all()"
                                 :selected="$filters['vendor_id'] ?? ''" />
            </x-filter-bar>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">No. Permintaan</th>
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">Vendor</th>
                        <th class="px-5 py-3">Jenis Servis</th>
                        <th class="px-5 py-3">Dikirim</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($requests as $r)
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3.5 font-medium text-gray-900">{{ $r->request_number }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $r->vehicle?->plate_number }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $r->vendor?->name }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $r->serviceType?->name ?? 'Pemeriksaan umum' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">
                                {{ $r->sent_at?->format('d-m-Y') ?? '—' }}
                                @if ($r->escalated_at)
                                    <p class="text-[11px] font-medium text-red-600">Dieskalasi</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5"><x-status-badge :status="$r->status" /></td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                <a href="{{ route('service-requests.show', $r) }}"
                                   class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="7" message="Belum ada permintaan servis." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$requests" />
    </x-card>

    {{-- FR-M4-21 — dashboard evaluasi vendor sewa. --}}
    <x-card :padded="false">
        <div class="border-b border-gray-100 px-5 py-3">
            <h3 class="text-sm font-semibold text-gray-900">Performa Vendor</h3>
            <p class="text-xs text-gray-500">Rata-rata waktu respons dan akumulasi downtime sebagai bahan evaluasi kontrak.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="px-5 py-3">Vendor</th>
                    <th class="px-5 py-3 text-right">Total Permintaan</th>
                    <th class="px-5 py-3 text-right">Terbuka</th>
                    <th class="px-5 py-3 text-right">Rata-rata Respons</th>
                    <th class="px-5 py-3 text-right">Total Downtime</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($vendorPerformance as $row)
                    <tr>
                        <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $row['vendor'] }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">{{ $row['total_requests'] }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">{{ $row['open_requests'] }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">
                            {{ $row['avg_response_days'] !== null ? $row['avg_response_days'].' hari' : '—' }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">{{ $row['total_downtime_days'] }} hari</td>
                    </tr>
                @empty
                    <x-empty-row :colspan="5" message="Belum ada data performa vendor." />
                @endforelse
            </tbody>
        </table>
        </div>
    </x-card>
</x-app-layout>

