<x-app-layout title="Detail Peminjaman">
    <x-slot name="header">
        <x-page-header :title="$booking->booking_number"
                       :subtitle="$booking->destination.' — '.$booking->booking_date->format('d-m-Y')"
                       :crumbs="['Operasional', 'Peminjaman', $booking->booking_number]">
            <x-slot name="actions">
                <a href="{{ route('bookings.print', $booking) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-sm font-medium text-gray-600 hover:text-usc-700 hover:bg-usc-50">
                    <x-nav-icon name="printer" class="h-4 w-4" />
                    Cetak Surat Jalan
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>


    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        {{-- Rincian pengajuan --}}
        <x-card title="Rincian Pengajuan" class="xl:col-span-1">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Status</dt>
                    <dd><x-status-badge :status="$booking->status" /></dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Pemohon</dt>
                    <dd class="text-right font-medium text-gray-900">{{ $booking->requester?->name }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Departemen</dt>
                    <dd class="text-right text-gray-900">{{ $booking->department?->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Tanggal Pemakaian</dt>
                    <dd class="text-right text-gray-900">{{ $booking->booking_date->format('d-m-Y') }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Tujuan</dt>
                    <dd class="text-right text-gray-900">{{ $booking->destination }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Keperluan</dt>
                    <dd class="text-right text-gray-900">{{ $booking->purpose }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Durasi</dt>
                    <dd class="text-right text-gray-900">{{ $booking->duration_type->label() }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">Area Ganjil–Genap</dt>
                    <dd class="text-right text-gray-900">{{ $booking->odd_even_zone ? 'Ya' : 'Tidak' }}</dd>
                </div>
                @if ($booking->is_urgent)
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500">Penanda</dt>
                        <dd><span class="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-medium text-red-600 ring-1 ring-inset ring-red-600/20">Urgent</span></dd>
                    </div>
                @endif
                @if (filled($booking->additional_note))
                    <div>
                        <dt class="text-gray-500">Catatan Pemohon</dt>
                        <dd class="mt-1 text-gray-900">{{ $booking->additional_note }}</dd>
                    </div>
                @endif
            </dl>

            {{-- Penugasan unit & driver --}}
            @if ($booking->vehicle)
                <div class="mt-5 border-t border-gray-100 pt-4">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Penugasan</h4>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Kendaraan</dt>
                            <dd class="text-right font-medium text-gray-900">
                                {{ $booking->vehicle->plate_number }}
                                <p class="text-xs font-normal text-gray-500">{{ $booking->vehicle->full_name }}</p>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Pengemudi</dt>
                            <dd class="text-right text-gray-900">
                                {{ $booking->self_drive ? 'Self-drive (pemohon)' : $booking->driver?->name }}
                                @if (! $booking->self_drive && $booking->driver?->phone)
                                    <p class="text-xs text-gray-500">{{ $booking->driver->phone }}</p>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Disetujui oleh</dt>
                            <dd class="text-right text-gray-900">
                                {{ $booking->approver?->name }}
                                <p class="text-xs text-gray-500">{{ $booking->approved_at?->format('d-m-Y H:i') }}</p>
                            </dd>
                        </div>
                    </dl>
                </div>
            @endif

            @if (filled($booking->rejection_reason))
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                    <p class="font-medium">Alasan penolakan</p>
                    <p>{{ $booking->rejection_reason }}</p>
                </div>
            @endif
        </x-card>

        <div class="space-y-4 xl:col-span-2">
            {{-- FR-M2-09 s.d. FR-M2-14 — layar approval & penugasan Admin GA. --}}
            @can('approve', $booking)
                @if ($booking->status === \App\Enums\BookingStatus::MenungguApproval)
                    <x-card title="Persetujuan & Penugasan Unit"
                            subtitle="Pilih kendaraan berdasarkan nomor polisi; driver bersifat opsional">
                        <form method="POST" action="{{ route('bookings.approve', $booking) }}" class="space-y-4">
                            @csrf

                            <div class="max-h-96 overflow-y-auto rounded-xl border border-usc-100 bg-gradient-to-br from-usc-50/40 via-white to-white shadow-sm">
                                <table class="min-w-full divide-y divide-gray-100 text-sm">
                                    <thead class="sticky top-0 bg-white/80 backdrop-blur border-b border-usc-100">
                                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            <th class="px-3 py-2.5"></th>
                                            <th class="px-3 py-2.5">Plat Nomor</th>
                                            <th class="px-3 py-2.5">Paritas</th>
                                            <th class="px-3 py-2.5">Servis</th>
                                            <th class="px-3 py-2.5">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($candidates as $c)
                                            <tr class="{{ $c->selectable ? 'hover:bg-usc-50/50' : 'bg-gray-50/60 opacity-70' }}">
                                                <td class="px-3 py-2.5">
                                                    <input type="radio" name="vehicle_id" value="{{ $c->vehicle->id }}"
                                                           @disabled(! $c->selectable)
                                                           @checked(old('vehicle_id') == $c->vehicle->id)
                                                           class="border-slate-300 text-usc-600 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 disabled:opacity-40">
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-2.5">
                                                    <span class="font-medium text-[#02081C]">{{ $c->vehicle->plate_number }}</span>
                                                    <p class="text-xs text-gray-500">{{ $c->vehicle->full_name }}</p>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-2.5">
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $c->vehicle->plate_parity === 'Ganjil' ? 'bg-orange-50 text-orange-700' : 'bg-sky-50 text-sky-700' }}">
                                                        {{ $c->vehicle->plate_parity ?? '-' }}
                                                    </span>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-2.5">
                                                    <x-status-badge :status="$c->serviceStatus" />
                                                </td>
                                                <td class="px-3 py-2.5 text-xs">
                                                    @foreach ($c->blockingReasons as $reason)
                                                        <p class="text-red-600">{{ $reason }}</p>
                                                    @endforeach
                                                    @foreach ($c->warnings as $warning)
                                                        <p class="text-amber-600">{{ $warning }}</p>
                                                    @endforeach
                                                    @if (empty($c->blockingReasons) && empty($c->warnings))
                                                        <span class="text-gray-400">Siap digunakan</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <x-input-error :messages="$errors->get('vehicle_id')" />

                            <div>
                                <x-input-label for="driver_id" value="Tugaskan Driver (opsional)" />
                                <div class="mt-1">
                                    @php
                                        $driverOptions = ['' => 'Tanpa driver — pemohon mengemudi sendiri (self drive)'] + $drivers->mapWithKeys(function($d) {
                                            return [$d->id => $d->name . ' — ' . $d->license_type];
                                        })->all();
                                    @endphp
                                    <x-filter-select name="driver_id" :options="$driverOptions" :selected="old('driver_id')" containerClass="w-full" class="border-slate-200" />
                                </div>
                                <x-input-error :messages="$errors->get('driver_id')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="assignment_note" value="Catatan Penugasan (opsional)" />
                                <textarea id="assignment_note" name="assignment_note" rows="2"
                                          class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                                          placeholder="Tuliskan catatan tambahan jika ada">{{ old('assignment_note') }}</textarea>
                            </div>

                            <div class="flex justify-end">
                                <x-btn-primary icon="check-circle" type="submit">Setujui &amp; Tugaskan</x-btn-primary>
                            </div>
                        </form>

                        {{-- FR-M2-15 — penolakan dengan alasan wajib. --}}
                        <form method="POST" action="{{ route('bookings.reject', $booking) }}"
                              class="mt-5 space-y-3 border-t border-gray-100 pt-4">
                            @csrf
                            <x-input-label for="rejection_reason" value="Tolak Pengajuan (alasan wajib)" />
                            <textarea id="rejection_reason" name="rejection_reason" rows="2"
                                      class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"
                                      placeholder="Alasan pengajuan ini ditolak">{{ old('rejection_reason') }}</textarea>
                            <x-input-error :messages="$errors->get('rejection_reason')" />
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-red-500 to-rose-500 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-red-500/20 hover:from-red-600 hover:to-rose-600 hover:shadow-xl hover:shadow-red-500/25 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300">
                                    <x-nav-icon name="x-circle" class="h-4 w-4" />
                                    Tolak Pengajuan
                                </button>
                            </div>
                        </form>
                    </x-card>
                @endif
            @endcan

            {{-- FR-M2-20 — serah terima. --}}
            @can('checkOut', $booking)
                <x-card title="Serah Terima (Check-in)" subtitle="Odometer awal, level BBM, kelengkapan, dan foto kondisi 4 sisi">
                    @include('bookings.partials.inspection-form', [
                        'action' => route('bookings.check-out', $booking),
                        'isCheckout' => true,
                        'booking' => $booking,
                    ])
                </x-card>
            @endcan

            {{-- FR-M2-21 — pengembalian. --}}
            @can('checkIn', $booking)
                <x-card title="Pengembalian (Check-out)" subtitle="Odometer akhir, level BBM, dan catatan kerusakan">
                    @include('bookings.partials.inspection-form', [
                        'action' => route('bookings.check-in', $booking),
                        'isCheckout' => false,
                        'booking' => $booking,
                    ])
                </x-card>
            @endcan

            {{-- Pemeriksaan Serah Terima & Pengembalian --}}
            @if ($booking->checkoutInspection || $booking->checkinInspection)
                <x-card title="Pemeriksaan Kendaraan" subtitle="Ringkasan serah terima (check-in) dan pengembalian (check-out)" :padded="false">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-white/80 backdrop-blur">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold text-gray-900">Kriteria</th>
                                    <th class="px-5 py-3 text-left font-semibold text-gray-900">Serah Terima (Check-in)</th>
                                    <th class="px-5 py-3 text-left font-semibold text-gray-900">Pengembalian (Check-out)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900 whitespace-nowrap">Waktu</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $booking->checkoutInspection?->inspected_at?->format('d-m-Y H:i') ?? '-' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $booking->checkinInspection?->inspected_at?->format('d-m-Y H:i') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900 whitespace-nowrap">Odometer</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $booking->checkoutInspection ? number_format($booking->checkoutInspection->odometer, 0, ',', '.') . ' km' : '-' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $booking->checkinInspection ? number_format($booking->checkinInspection->odometer, 0, ',', '.') . ' km' : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900 whitespace-nowrap">Level BBM</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $booking->checkoutInspection ? ((float) $booking->checkoutInspection->fuel_level * 100) . '%' : '-' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $booking->checkinInspection ? ((float) $booking->checkinInspection->fuel_level * 100) . '%' : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900 whitespace-nowrap">Kelengkapan</td>
                                    <td class="px-5 py-3 text-gray-600">
                                        @php
                                            $formatChecklist = function($checklist) {
                                                if (!is_array($checklist)) return '-';
                                                $items = collect($checklist)
                                                    ->filter(fn($val) => $val)
                                                    ->keys()
                                                    ->map(fn($k) => ucwords(str_replace('_', ' ', $k)))
                                                    ->toArray();
                                                return empty($items) ? '-' : implode(', ', $items);
                                            };
                                        @endphp
                                        {{ $formatChecklist($booking->checkoutInspection?->checklist) }}
                                    </td>
                                    <td class="px-5 py-3 text-gray-600">
                                        {{ $formatChecklist($booking->checkinInspection?->checklist) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900 whitespace-nowrap">Catatan kondisi</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $booking->checkoutInspection?->condition_notes ?: '-' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $booking->checkinInspection?->condition_notes ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900 whitespace-nowrap align-top">Foto Kondisi</td>
                                    <td class="px-5 py-3 align-top">
                                        @if($booking->checkoutInspection && $booking->checkoutInspection->photos->isNotEmpty())
                                            <div class="flex flex-wrap gap-3">
                                                @foreach($booking->checkoutInspection->photos as $photo)
                                                    <div class="flex flex-col gap-1 w-24 sm:w-32">
                                                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">{{ $photo->position }}</span>
                                                        <x-image-viewer 
                                                            url="{{ Storage::disk(config('usc_vehicle_ops.uploads.disk', 'public'))->url($photo->photo_path) }}" 
                                                            alt="Foto {{ $photo->position }}" 
                                                            :compact="true"
                                                        />
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-600">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 align-top">
                                        @if($booking->checkinInspection && $booking->checkinInspection->photos->isNotEmpty())
                                            <div class="flex flex-wrap gap-3">
                                                @foreach($booking->checkinInspection->photos as $photo)
                                                    <div class="flex flex-col gap-1 w-24 sm:w-32">
                                                        <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">{{ $photo->position }}</span>
                                                        <x-image-viewer 
                                                            url="{{ Storage::disk(config('usc_vehicle_ops.uploads.disk', 'public'))->url($photo->photo_path) }}" 
                                                            alt="Foto {{ $photo->position }}" 
                                                            :compact="true"
                                                        />
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-600">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </x-card>
            @endif

            {{-- Hasil peminjaman --}}
            @if ($booking->odometer_start !== null)
                <x-card title="Data Pemakaian">
                    <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                        <div>
                            <dt class="text-gray-500">Odometer Awal</dt>
                            <dd class="font-medium tabular-nums text-gray-900">{{ number_format((int) $booking->odometer_start, 0, ',', '.') }} km</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Odometer Akhir</dt>
                            <dd class="font-medium tabular-nums text-gray-900">
                                {{ $booking->odometer_end !== null ? number_format((int) $booking->odometer_end, 0, ',', '.').' km' : '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Jarak Tempuh</dt>
                            <dd class="font-medium tabular-nums text-gray-900">
                                {{ $booking->distance_traveled !== null ? number_format((int) $booking->distance_traveled, 0, ',', '.').' km' : '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Status Kembali</dt>
                            <dd class="font-medium text-gray-900">{{ $booking->is_overdue ? 'Terlambat' : 'Tepat waktu' }}</dd>
                        </div>
                    </dl>
                </x-card>
            @endif

            {{-- FR-M2-07 — pembatalan. --}}
            @can('cancel', $booking)
                <x-card title="Batalkan Pengajuan">
                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="space-y-3">
                        @csrf
                        <textarea name="cancellation_reason" rows="2" placeholder="Alasan pembatalan (opsional)"
                                  class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#02081C] font-medium outline-none transition hover:border-slate-300 focus:border-usc-400 focus:ring-4 focus:ring-usc-50 placeholder:text-gray-400 placeholder:text-sm placeholder:font-normal"></textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-lg border px-4 py-2
                            rounded-lg border border-usc-200 bg-white/80 px-3.5 py-2 text-sm font-medium text-usc-700 shadow-sm backdrop-blur hover:bg-usc-50 transition duration-300
                            ">
                                Batalkan Pengajuan
                            </button>
                        </div>
                    </form>
                </x-card>
            @endcan
        </div>
    </div>
</x-app-layout>
