<x-app-layout title="Vendor & Bengkel">
    <x-slot name="header">
        <x-page-header title="Vendor & Kontrak Sewa" subtitle="Bengkel, SPBU, dan penyedia kendaraan sewa" :crumbs="['Master Data', 'Vendor']">
            <x-slot name="actions">
                @can('master-data.kelola')
                    <x-btn-primary href="{{ route('vendors.create') }}" icon="plus" class="w-full justify-center sm:w-auto">Tambah Vendor</x-btn-primary>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <x-card :padded="false" class="mb-4">
        <form method="GET" action="{{ route('vendors.index') }}">
            <x-filter-bar searchPlaceholder="Cari nama vendor..." :value="$filters['search'] ?? ''">
                <x-filter-select name="type" :options="['' => 'Semua Jenis'] + $types"
                                 :selected="$filters['type'] ?? ''" />
            </x-filter-bar>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Nama Vendor</th>
                        <th class="px-5 py-3">Jenis</th>
                        <th class="px-5 py-3">PIC</th>
                        <th class="px-5 py-3">Alamat</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($vendors as $v)
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <a href="{{ route('vendors.show', $v) }}" class="font-medium text-gray-900 hover:text-usc-700">
                                    {{ $v->name }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                @php
                                    $typeClass = match ($v->type->value) {
                                        'bengkel' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                        'spbu' => 'bg-green-50 text-green-700 ring-green-600/20',
                                        default => 'bg-violet-50 text-violet-700 ring-violet-600/20',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $typeClass }}">
                                    {{ $v->type->label() }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">
                                {{ $v->pic_name ?? '-' }}
                                @if ($v->pic_phone)
                                    <p class="text-xs text-gray-400">{{ $v->pic_phone }}</p>
                                @endif
                            </td>
                            <td class="max-w-[260px] truncate px-5 py-3.5 text-gray-600" title="{{ $v->address }}">
                                {{ $v->address ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <x-status-badge :status="$v->is_active ? 'aktif' : 'nonaktif'" />
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                @can('master-data.kelola')
                                    <a href="{{ route('vendors.edit', $v) }}"
                                       class="rounded-lg bg-usc-50 px-2.5 py-1.5 text-xs font-medium text-usc-700 hover:bg-usc-100">
                                        Ubah
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="6" message="Belum ada vendor." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$vendors" />
    </x-card>

    {{-- FR-M4-22 — kontrak sewa beserta pengingat berakhirnya kontrak. --}}
    <x-card :padded="false">
        <div class="border-b border-gray-100 px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Kontrak Sewa Kendaraan</h3>
                <p class="mt-0.5 text-xs text-gray-500">Pengingat otomatis dikirim H-{{ $contracts->first()?->reminder_days ?? 60 }} sebelum kontrak berakhir.</p>
            </div>
            @can('master-data.kelola')
                <a href="{{ route('rental-contracts.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center gap-1.5 rounded-lg border border-usc-200 bg-white px-3.5 py-2 text-xs font-medium text-usc-700 shadow-sm hover:bg-usc-50 hover:text-usc-800 hover:shadow-md transition-all duration-300 shrink-0">
                    <x-nav-icon name="document-text" class="h-4 w-4" />
                    Tulis Kontrak Sewa
                </a>
            @endcan
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">No. Kontrak</th>
                        <th class="px-5 py-3">Vendor</th>
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">Masa Berlaku</th>
                        <th class="px-5 py-3 text-right">Biaya / Bulan</th>
                        <th class="px-5 py-3">Sisa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($contracts as $c)
                        @php $days = $c->days_remaining; @endphp
                        <tr class="hover:bg-gray-50/60">
                            <td class="whitespace-nowrap px-5 py-3.5 font-medium text-gray-900">{{ $c->contract_number }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">{{ $c->vendor?->name }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $c->vehicles->pluck('plate_number')->implode(', ') ?: '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-gray-600">
                                {{ $c->start_date->format('d-m-Y') }} &ndash; {{ $c->end_date->format('d-m-Y') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right tabular-nums text-gray-900">
                                Rp {{ number_format((float) $c->monthly_cost, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <span class="{{ $days < 0 ? 'font-semibold text-red-600' : ($c->isExpiringSoon() ? 'font-medium text-amber-600' : 'text-gray-600') }}">
                                    {{ $days < 0 ? 'Berakhir '.abs($days).' hari lalu' : $days.' hari lagi' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="6" message="Belum ada kontrak sewa." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>

