<x-app-layout title="Peminjaman">
    <x-slot name="header">
        <x-page-header title="Peminjaman Kendaraan" subtitle="Pengajuan, approval, dan penugasan kendaraan" :crumbs="['Operasional', 'Peminjaman']">
            <x-slot name="actions">
                @can('peminjaman.setujui')
                    <a href="{{ route('bookings.approval-queue') }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-sm font-medium text-gray-600 hover:text-usc-700 hover:bg-usc-50">
                        <x-nav-icon name="check-circle" class="h-4 w-4" />
                        Antrean Approval
                    </a>
                @endcan
                @can('peminjaman.ajukan')
                    <a href="{{ route('bookings.create') }}">
                        <x-btn-primary icon="plus">Ajukan Peminjaman</x-btn-primary>
                    </a>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <x-card :padded="false">
        <form method="GET" action="{{ route('bookings.index') }}">
            <x-filter-bar searchPlaceholder="Cari nomor pengajuan, tujuan, atau keperluan..."
                          :value="$filters['search'] ?? ''">
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
                        <th class="px-5 py-3">No. Pengajuan</th>
                        <th class="px-5 py-3">Pemohon</th>
                        <th class="px-5 py-3">Tanggal &amp; Tujuan</th>
                        <th class="px-5 py-3">Durasi</th>
                        <th class="px-5 py-3">Kendaraan / Driver</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($bookings as $b)
                        <tr class="hover:bg-usc-50/50">
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <a href="{{ route('bookings.show', $b) }}" class="font-medium text-gray-900 hover:text-usc-700">
                                    {{ $b->booking_number }}
                                </a>
                                @if ($b->is_urgent)
                                    <span class="ml-1.5 inline-flex items-center rounded-full bg-red-50 px-1.5 py-0.5 text-[10px] font-semibold text-red-600 ring-1 ring-inset ring-red-600/20">URGENT</span>
                                @endif
                                @if ($b->odd_even_zone)
                                    <div class="mt-0.5 text-[11px] text-gray-400">Ganjil-genap</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $b->requester?->name }}</p>
                                <p class="text-xs text-gray-500">{{ $b->department?->name }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-gray-900">{{ $b->booking_date->format('d-m-Y') }}</p>
                                <p class="max-w-[220px] truncate text-xs text-gray-500" title="{{ $b->destination }} — {{ $b->purpose }}">
                                    {{ $b->destination }} &middot; {{ $b->purpose }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $b->duration_type->label() }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                @if ($b->vehicle)
                                    <p class="font-medium text-gray-900">{{ $b->vehicle->plate_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $b->driver?->name ?? 'Self-drive' }}</p>
                                @else
                                    <span class="text-xs italic text-gray-400">Belum ditugaskan</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <x-status-badge :status="$b->status" />
                                @if ($b->status === \App\Enums\BookingStatus::Ditolak && filled($b->rejection_reason))
                                    <p class="mt-1 max-w-[180px] truncate text-[11px] text-gray-400" title="{{ $b->rejection_reason }}">
                                        {{ $b->rejection_reason }}
                                    </p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                <a href="{{ route('bookings.show', $b) }}"
                                   class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                    {{ $b->status === \App\Enums\BookingStatus::MenungguApproval && auth()->user()->can('peminjaman.setujui') ? 'Proses' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="7" message="Belum ada pengajuan peminjaman yang cocok dengan filter." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$bookings" />
    </x-card>
</x-app-layout>

