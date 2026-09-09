<x-app-layout title="Penugasan Driver">
    <x-slot name="header">
        <x-page-header title="Penugasan Driver" subtitle="Daftar penugasan driver pada peminjaman kendaraan" :crumbs="['Operasional', 'Penugasan Driver']">
        </x-page-header>
    </x-slot>

    <x-card :padded="false">
        <form method="GET" action="{{ route('bookings.driver-assignments.index') }}">
            <x-filter-bar searchPlaceholder="Cari tanggal, tujuan, driver, kendaraan, no pengajuan, pemohon, status..." name="search" :value="request('search')" />
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Tanggal &amp; Tujuan</th>
                        <th class="px-5 py-3">Driver / Kendaraan</th>
                        <th class="px-5 py-3">No. Pengajuan</th>
                        <th class="px-5 py-3">Pemohon</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($assignments as $b)
                        <tr class="hover:bg-usc-50/50">
                            <td class="px-5 py-3.5 w-48">
                                <p class="text-gray-900">{{ $b->booking_date->format('d-m-Y') }}</p>
                                <p class="max-w-[160px] truncate text-xs text-gray-500" title="{{ $b->destination }} — {{ $b->purpose }}">
                                    {{ $b->destination }} &middot; {{ $b->purpose }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                @if ($b->vehicle)
                                    <p class="font-medium text-gray-900">{{ $b->driver?->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $b->vehicle->plate_number }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <a href="{{ route('bookings.show', $b) }}" class="font-medium text-gray-900 hover:text-usc-700">
                                    {{ $b->booking_number }}
                                </a>
                                @if ($b->is_urgent)
                                    <span class="ml-1.5 inline-flex items-center rounded-full bg-red-50 px-1.5 py-0.5 text-[10px] font-semibold text-red-600 ring-1 ring-inset ring-red-600/20">URGENT</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $b->requester?->name }}</p>
                                <p class="text-xs text-gray-500">{{ $b->department?->name }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <x-status-badge :status="$b->status" />
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                <a href="{{ route('bookings.show', $b) }}"
                                   class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                   @if(in_array($b->status, [\App\Enums\BookingStatus::Disetujui, \App\Enums\BookingStatus::SedangDigunakan]))
                                        Proses
                                   @else
                                        Detail
                                   @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="6" message="Belum ada penugasan driver." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$assignments" />
    </x-card>
</x-app-layout>
