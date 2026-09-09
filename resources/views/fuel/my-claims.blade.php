<x-app-layout title="Klaim Saya">
    <x-slot name="header">
        <x-page-header title="Klaim BBM Saya" subtitle="Pantau status setiap klaim dan total yang belum dibayar" :crumbs="['Operasional', 'BBM', 'Klaim Saya']">
            <x-slot name="actions">
                @can('bbm.ajukan-klaim')
                    <a href="{{ route('fuel.create') }}" class="w-full sm:w-auto">
                        <x-btn-primary icon="plus" class="w-full justify-center">Input Pengisian BBM</x-btn-primary>
                    </a>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <div class="mb-4 grid grid-cols-2 gap-4 xl:grid-cols-4">
        <x-stat-card label="Menunggu Verifikasi" value="Rp {{ number_format($summary['diajukan'], 0, ',', '.') }}" icon="exclamation" accent="warning" />
        <x-stat-card label="Belum Dibayar" value="Rp {{ number_format($summary['outstanding'], 0, ',', '.') }}" icon="chart-bar" accent="brand" />
        <x-stat-card label="Sudah Dibayar" value="Rp {{ number_format($summary['dibayar'], 0, ',', '.') }}" icon="check-circle" accent="good" />
        <x-stat-card label="Klaim Ditolak" value="{{ $summary['ditolak'] }}" icon="exclamation" accent="critical" />
    </div>

    <x-card :padded="false">
        <form method="GET" action="{{ route('fuel.my-claims') }}">
            <x-filter-bar searchPlaceholder="Cari tanggal, plat, SPBU, nominal, batch, status..." name="search" :value="request('search')" />
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">SPBU / Nota</th>
                        <th class="px-5 py-3 text-right">Nominal</th>
                        <th class="px-5 py-3">Batch</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($claims as $c)
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $c->transaction_datetime->format('d-m-Y') }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 font-medium text-gray-900">{{ $c->vehicle?->plate_number }}</td>
                            <td class="px-5 py-3.5 text-gray-600">
                                {{ $c->station_name }}
                                <p class="text-xs text-gray-400">{{ $c->receipt_number }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right tabular-nums font-medium text-gray-900">
                                Rp {{ number_format($c->claimable_amount, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $c->batch?->batch_number ?? '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5"><x-status-badge :status="$c->status" /></td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                <a href="{{ route('fuel.show', $c) }}"
                                   class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="7" message="Anda belum memiliki klaim BBM." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$claims" />
    </x-card>
</x-app-layout>
