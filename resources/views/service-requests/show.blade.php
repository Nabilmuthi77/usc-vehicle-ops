<x-app-layout title="Detail Permintaan Servis">
    <x-slot name="header">
        <x-page-header :title="$request->request_number"
                       :subtitle="$request->vehicle?->plate_number.' — '.$request->vendor?->name"
                       :crumbs="['Operasional', 'Permintaan Servis', $request->request_number]">
            <x-slot name="actions">
                <a href="{{ route('service-requests.print', $request) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    <x-nav-icon name="download" class="h-4 w-4" />
                    Cetak PDF
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>


    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-card title="Rincian Permintaan" class="xl:col-span-2">
            <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                <div><dt class="text-gray-500">Status</dt><dd class="mt-1"><x-status-badge :status="$request->status" /></dd></div>
                <div><dt class="text-gray-500">Kendaraan</dt><dd class="mt-1 font-medium text-gray-900">{{ $request->vehicle?->plate_number }}</dd></div>
                <div><dt class="text-gray-500">Kepemilikan</dt><dd class="mt-1 text-gray-900">{{ $request->vehicle?->ownership->label() }}</dd></div>
                <div><dt class="text-gray-500">Vendor</dt><dd class="mt-1 text-gray-900">{{ $request->vendor?->name }}</dd></div>
                <div><dt class="text-gray-500">Jenis Servis</dt><dd class="mt-1 text-gray-900">{{ $request->serviceType?->name ?? 'Pemeriksaan umum' }}</dd></div>
                <div><dt class="text-gray-500">Odometer</dt><dd class="mt-1 tabular-nums text-gray-900">{{ number_format((int) $request->current_odometer, 0, ',', '.') }} km</dd></div>
                <div><dt class="text-gray-500">Tgl. Dikirim ke Vendor</dt><dd class="mt-1 text-gray-900">{{ $request->sent_at?->format('d-m-Y') ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Tgl. Dijadwalkan Vendor</dt><dd class="mt-1 text-gray-900">{{ $request->scheduled_date?->format('d-m-Y') ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Tgl. Selesai</dt><dd class="mt-1 text-gray-900">{{ $request->completed_date?->format('d-m-Y') ?? '—' }}</dd></div>
            </dl>

            @if (filled($request->complaint_note))
                <div class="mt-4">
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Keluhan</h4>
                    <p class="mt-1 text-sm text-gray-700">{{ $request->complaint_note }}</p>
                </div>
            @endif

            {{-- FR-M4-18 — pelacakan SLA vendor. --}}
            @if ($daysSinceSent !== null && ! $request->status->isClosed())
                <p class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">
                    Menunggu respons vendor selama {{ $daysSinceSent }} hari sejak permintaan dikirim.
                </p>
            @endif

            @if ($request->response_days !== null)
                <p class="mt-4 text-xs text-gray-500">
                    Waktu respons vendor: {{ $request->response_days }} hari.
                    Downtime kendaraan: {{ $request->downtime_days ?? 0 }} hari.
                </p>
            @endif
        </x-card>

        <div class="space-y-4">
            {{-- FR-M4-17 — kirim permintaan ke PIC vendor. --}}
            @can('send', $request)
                <x-card title="Kirim ke Vendor">
                    <form method="POST" action="{{ route('service-requests.send', $request) }}" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="sent_to_email" value="Email PIC Vendor" />
                            <x-text-input id="sent_to_email" name="sent_to_email" type="email" class="mt-1 block w-full !h-[34px] !shadow-none"
                                          :value="old('sent_to_email', $request->vendor?->pic_email)" required />
                            <x-input-error :messages="$errors->get('sent_to_email')" class="mt-1" />
                        </div>
                        <x-btn-primary icon="check-circle" type="submit">Kirim Permintaan</x-btn-primary>
                    </form>
                </x-card>
            @endcan

            {{-- FR-M4-16 — transisi status yang diizinkan. --}}
            @can('update', $request)
                @if (! empty($allowedTransitions))
                    <x-card title="Perbarui Status">
                        <form method="POST" action="{{ route('service-requests.update-status', $request) }}" class="space-y-3" x-data="{ status: '{{ old('status', '') }}' }">
                            @csrf
                            <div>
                                <x-input-label for="status" value="Status Baru" />
                                @php
                                    $statusOptions = [];
                                    foreach($allowedTransitions as $target) {
                                        $statusOptions[$target->value] = $target->label();
                                    }
                                @endphp
                                <x-filter-select id="status" name="status" containerClass="mt-1 w-full block"
                                                 @change="status = $event.target.value"
                                                 :selected="old('status')"
                                                 :options="$statusOptions" />
                                <x-input-error :messages="$errors->get('status')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label x-text="status ? 'Tanggal ' + status.charAt(0).toUpperCase() + status.slice(1) + ' Vendor' : 'Tanggal Vendor'" />
                                <x-date-picker id="status_date" name="status_date" :value="old('status_date')"
                                    x-effect="
                                        let ph = status ? 'Tanggal ' + status.charAt(0).toUpperCase() + status.slice(1) + ' Vendor' : 'Tanggal Vendor';
                                        let btn = $el.querySelector('[data-date-button]');
                                        let lbl = $el.querySelector('[data-date-label]');
                                        if (btn) btn.dataset.placeholder = ph;
                                        if (lbl && ! $el.querySelector('[data-date-value]').value) lbl.textContent = ph;
                                    "
                                />
                                <x-input-error :messages="$errors->get('status_date')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="vendor_response_note" value="Catatan Vendor" />
                                <textarea id="vendor_response_note" name="vendor_response_note" rows="2"
                                          class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10"></textarea>
                            </div>
                            <x-btn-primary icon="check-circle" type="submit">Perbarui Status</x-btn-primary>
                        </form>
                    </x-card>
                @endif
            @endcan

            {{-- FR-M4-19 — penyelesaian permintaan; biaya opsional. --}}
            @can('complete', $request)
                <x-card title="Selesaikan Permintaan">
                    <form method="POST" action="{{ route('service-requests.complete', $request) }}" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="completed_date" value="Tanggal Selesai" />
                            <x-date-picker id="completed_date" name="completed_date" :value="old('completed_date', now()->toDateString())" placeholder="Tanggal Selesai" />
                            <x-input-error :messages="$errors->get('completed_date')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="odometer" value="Odometer saat Servis (km)" />
                            <x-text-input id="odometer" name="odometer" type="number" min="0" class="mt-1 block w-full !h-[34px]"
                                          :value="$request->vehicle?->current_odometer" required />
                        </div>
                        <div>
                            <x-input-label for="total_cost" value="Biaya (opsional — informasi vendor)" />
                            <x-text-input id="total_cost" name="total_cost" type="number" step="0.01" min="0" class="mt-1 block w-full !h-[34px]" />
                            <p class="mt-1 text-xs text-gray-500">
                                Biaya kendaraan sewa ditanggung vendor dan tidak dihitung sebagai biaya operasional perusahaan.
                            </p>
                        </div>
                        <div>
                            <x-input-label for="description" value="Ringkasan Pekerjaan" />
                            <textarea id="description" name="description" rows="2"
                                      class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10 shadow-sm"></textarea>
                        </div>
                        <x-btn-primary icon="check-circle" type="submit">Tandai Selesai</x-btn-primary>
                    </form>
                </x-card>
            @endcan
        </div>
    </div>
</x-app-layout>
