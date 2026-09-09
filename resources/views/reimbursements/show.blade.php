<x-app-layout title="Detail Batch Reimbursement">
    <x-slot name="header">
        <x-page-header :title="$batch->batch_number"
                       :subtitle="$batch->claimant?->name.' — '.$batch->period_start->format('d-m-Y').' s.d. '.$batch->period_end->format('d-m-Y')"
                       :crumbs="['Operasional', 'Reimbursement', $batch->batch_number]">
            <x-slot name="actions">
                <a href="{{ route('reimbursements.print', $batch) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    <x-nav-icon name="download" class="h-4 w-4" />
                    Cetak Rekap PDF
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>


    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-card title="Rincian Klaim" :padded="false" class="xl:col-span-2">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-white/80 backdrop-blur">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Kendaraan</th>
                        <th class="px-5 py-3">Nota</th>
                        <th class="px-5 py-3 text-right">Nominal</th>
                        @can('update', $batch)
                            <th class="px-5 py-3"></th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($batch->items as $item)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $item->transaction_datetime->format('d-m-Y') }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-900">{{ $item->vehicle?->plate_number }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $item->station_name }} &middot; {{ $item->receipt_number }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right tabular-nums text-gray-900">
                                Rp {{ number_format($item->claimable_amount, 0, ',', '.') }}
                            </td>
                            @can('update', $batch)
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('reimbursements.remove-claim', [$batch, $item]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:underline">Lepas</button>
                                    </form>
                                </td>
                            @endcan
                        </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-semibold">
                        <td colspan="3" class="px-5 py-3 text-right text-gray-700">Total ({{ $batch->item_count }} nota)</td>
                        <td class="px-5 py-3 text-right tabular-nums text-gray-900">
                            Rp {{ number_format((float) $batch->total_amount, 0, ',', '.') }}
                        </td>
                        @can('update', $batch)<td></td>@endcan
                    </tr>
                </tbody>
            </table>
        </x-card>

        <div class="space-y-4">
            <x-card title="Status Batch">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd><x-status-badge :status="$batch->status" /></dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Diserahkan</dt><dd class="text-gray-900">{{ $batch->submitted_at?->format('d-m-Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Dibayar</dt><dd class="text-gray-900">{{ $batch->paid_at?->format('d-m-Y') ?? '—' }}</dd></div>
                    @if ($batch->paidBy)
                        <div class="flex justify-between"><dt class="text-gray-500">Diproses oleh</dt><dd class="text-gray-900">{{ $batch->paidBy->name }}</dd></div>
                    @endif
                </dl>
            </x-card>

            @if ($batch->status === \App\Enums\ReimbursementBatchStatus::Dibayar)
                <x-card title="Bukti Pembayaran">
                    @if ($batch->payment_proof_path)
                        <div class="mb-3">
                            <x-image-viewer 
                                url="{{ Storage::url($batch->payment_proof_path) }}" 
                                alt="Bukti Pembayaran Batch {{ $batch->batch_number }}" 
                            />
                        </div>
                    @endif
                    @if ($batch->payment_note)
                        <div>
                            <span class="block text-xs font-medium text-gray-500">Catatan:</span>
                            <p class="mt-1 text-sm text-gray-900">{{ $batch->payment_note }}</p>
                        </div>
                    @endif
                </x-card>
            @endif

            @can('update', $batch)
                @if ($batch->isOpen())
                    <x-card title="Serahkan ke Finance">
                        <form method="POST" action="{{ route('reimbursements.submit', $batch) }}">
                            @csrf
                            <x-btn-primary icon="check-circle" type="submit">Serahkan ke Finance</x-btn-primary>
                        </form>
                    </x-card>
                @endif
            @endcan

            {{-- FR-M3-12 — penandaan pembayaran oleh Finance. --}}
            @can('markAsPaid', $batch)
                <x-card title="Tandai Dibayar">
                    <form method="POST" action="{{ route('reimbursements.pay', $batch) }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="paid_at" value="Tanggal Pembayaran" />
                            <x-date-picker id="paid_at" name="paid_at" :value="now()->toDateString()" placeholder="Tanggal Pembayaran" />
                            <x-input-error :messages="$errors->get('paid_at')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="payment_proof" value="Bukti Transfer" />
                            <input id="payment_proof" type="file" name="payment_proof" accept="image/png,image/jpeg,application/pdf"
                                   class="mt-1 block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-usc-50 file:text-usc-700 hover:file:bg-usc-100 border border-gray-200 rounded-lg bg-gray-50 overflow-hidden">
                        </div>
                        <div>
                            <x-input-label for="payment_note" value="Catatan" />
                            <textarea id="payment_note" name="payment_note" rows="2"
                                      class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10 shadow-sm"></textarea>
                        </div>
                        <x-btn-primary icon="check-circle" type="submit">Tandai Dibayar</x-btn-primary>
                    </form>
                </x-card>
            @endcan
        </div>
    </div>
</x-app-layout>
