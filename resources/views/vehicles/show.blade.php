<x-app-layout title="Detail Kendaraan">
    <x-slot name="header">
        <x-page-header :title="$vehicle->plate_number" :subtitle="$vehicle->full_name.' · '.$vehicle->year"
                       :crumbs="['Master Data', 'Kendaraan', $vehicle->plate_number]">
            <x-slot name="actions">
                <a href="{{ route('service.history', $vehicle) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    <x-nav-icon name="wrench" class="h-4 w-4" />
                    Riwayat Servis
                </a>
                @can('update', $vehicle)
                    <a href="{{ route('vehicles.edit', $vehicle) }}">
                        <x-btn-primary icon="wrench">Ubah Data</x-btn-primary>
                    </a>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>


    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-card title="Data Kendaraan" class="xl:col-span-2">
            <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                <div><dt class="text-gray-500">Status</dt><dd class="mt-1"><x-status-badge :status="$vehicle->status" /></dd></div>
                <div><dt class="text-gray-500">Kepemilikan</dt><dd class="mt-1 text-gray-900">{{ $vehicle->ownership->label() }}</dd></div>
                <div><dt class="text-gray-500">Departemen</dt><dd class="mt-1 text-gray-900">{{ $vehicle->department?->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Jenis BBM</dt><dd class="mt-1 text-gray-900">{{ $vehicle->fuel_type->label() }}</dd></div>
                <div><dt class="text-gray-500">Kapasitas Tangki</dt><dd class="mt-1 text-gray-900">{{ $vehicle->tank_capacity ? number_format((float) $vehicle->tank_capacity, 0, ',', '.').' L' : '—' }}</dd></div>
                <div><dt class="text-gray-500">Paritas Pelat</dt><dd class="mt-1 text-gray-900">{{ $vehicle->plate_parity ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Odometer</dt><dd class="mt-1 tabular-nums font-medium text-gray-900">{{ number_format((int) $vehicle->current_odometer, 0, ',', '.') }} km</dd></div>
                <div><dt class="text-gray-500">Rata-rata Pemakaian</dt><dd class="mt-1 tabular-nums text-gray-900">{{ number_format($averageDailyUsage, 1, ',', '.') }} km/hari</dd></div>
                <div><dt class="text-gray-500">Status Servis</dt><dd class="mt-1"><x-status-badge :status="$vehicle->service_status" /></dd></div>
                @if ($vehicle->rentalContract)
                    <div class="sm:col-span-3">
                        <dt class="text-gray-500">Kontrak Sewa</dt>
                        <dd class="mt-1 text-gray-900">
                            {{ $vehicle->rentalContract->contract_number }} — {{ $vehicle->rentalContract->vendor?->name }}
                            (berakhir {{ $vehicle->rentalContract->end_date->format('d-m-Y') }})
                        </dd>
                    </div>
                @endif
            </dl>
        </x-card>

        <div class="space-y-4">
            @if ($vehicle->photo_path)
                <x-card title="Foto Kendaraan" class="overflow-hidden">
                    <x-image-viewer 
                        url="{{ Storage::disk(config('usc_vehicle_ops.uploads.disk', 'public'))->url($vehicle->photo_path) }}" 
                        alt="Foto kendaraan {{ $vehicle->plate_number }}" 
                    />
                </x-card>
            @endif

            <x-card>
                <x-slot name="title">
                    <div class="flex items-center justify-between">
                        <span>Dokumen Kendaraan</span>
                        @can('update', $vehicle)
                            <a href="{{ route('vehicles.documents.create', $vehicle) }}" class="text-sm font-medium text-usc-600 hover:text-usc-700">
                                + Tambah
                            </a>
                        @endcan
                    </div>
                </x-slot>
                
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($vehicle->documents as $doc)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">
                                        @if ($doc->file_path)
                                            <a href="{{ Storage::disk(config('usc_vehicle_ops.uploads.disk', 'public'))->url($doc->file_path) }}" target="_blank" class="hover:underline hover:text-usc-600">
                                                {{ $doc->document_type->label() }}
                                            </a>
                                        @else
                                            {{ $doc->document_type->label() }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $doc->document_number ?? '—' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <p class="{{ $doc->isExpired() ? 'font-semibold text-red-600' : ($doc->isExpiringSoon() ? 'font-medium text-amber-600' : 'text-gray-600') }}">
                                        {{ $doc->expiry_date->format('d-m-Y') }}
                                    </p>
                                    <p class="text-[11px] text-gray-400">
                                        {{ $doc->isExpired() ? 'Kedaluwarsa' : 'Sisa '.$doc->days_remaining.' hari' }}
                                    </p>
                                </td>
                                @can('update', $vehicle)
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-xs">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('vehicles.documents.edit', [$vehicle, $doc]) }}" class="font-medium text-usc-600 hover:text-usc-700">Ubah</a>
                                            <form method="POST" action="{{ route('vehicles.documents.destroy', [$vehicle, $doc]) }}" onsubmit="return confirm('Hapus dokumen ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 hover:text-red-700">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <x-empty-row :colspan="auth()->user()->can('update', $vehicle) ? 3 : 2" message="Belum ada dokumen." />
                        @endforelse
                    </tbody>
                </table>
            </x-card>
        </div>

        <x-card title="Riwayat Peminjaman Terakhir" :padded="false" class="xl:col-span-2">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">No. Pengajuan</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Pemohon</th>
                        <th class="px-5 py-3 text-right">Jarak</th>
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
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $b->requester?->name }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-600">
                                {{ $b->distance_traveled !== null ? number_format((int) $b->distance_traveled, 0, ',', '.').' km' : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3"><x-status-badge :status="$b->status" /></td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="5" message="Belum ada riwayat peminjaman." />
                    @endforelse
                </tbody>
            </table>
        </x-card>

        {{-- BR-03 — log odometer & koreksi oleh Admin. --}}
        <x-card title="Log Odometer" :padded="false">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentOdometerLogs as $log)
                        <tr>
                            <td class="px-4 py-2.5">
                                <p class="tabular-nums font-medium text-gray-900">{{ number_format((int) $log->odometer, 0, ',', '.') }} km</p>
                                <p class="text-xs text-gray-500">{{ $log->source->label() }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-right text-xs text-gray-500">
                                {{ $log->recorded_at->format('d-m-Y') }}
                                @if ($log->is_correction)
                                    <p class="font-medium text-amber-600">Koreksi</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-empty-row :colspan="2" message="Belum ada log odometer." />
                    @endforelse
                </tbody>
            </table>

            @can('correctOdometer', $vehicle)
                <form method="POST" action="{{ route('vehicles.correct-odometer', $vehicle) }}"
                      class="space-y-2 border-t border-gray-100 p-4">
                    @csrf
                    <x-input-label for="odometer" value="Koreksi Odometer" class="text-xs" />
                    <x-text-input id="odometer" name="odometer" type="number" min="0" class="block w-full text-sm"
                                  :value="$vehicle->current_odometer" required />
                    <textarea name="correction_reason" rows="2" required placeholder="Alasan koreksi (wajib)"
                              class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-usc-400 focus:ring-usc-400"></textarea>
                    <x-input-error :messages="$errors->get('odometer')" />
                    <button type="submit" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Simpan Koreksi
                    </button>
                </form>
            @endcan
        </x-card>
    </div>
</x-app-layout>
