<x-app-layout title="Detail Servis">
    <x-slot name="header">
        <x-page-header :title="'Servis '.$record->vehicle?->plate_number"
                       :subtitle="$record->service_date->format('d-m-Y').' · '.($record->serviceType?->name ?? 'Insidental')"
                       :crumbs="['Operasional', 'Servis', 'Detail']" />
    </x-slot>


    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-card title="Rincian Servis" class="xl:col-span-2">
            <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                <div><dt class="text-gray-500">Kendaraan</dt><dd class="mt-1 font-medium text-gray-900">{{ $record->vehicle?->plate_number }}</dd></div>
                <div><dt class="text-gray-500">Kategori</dt><dd class="mt-1 text-gray-900">{{ $record->category->label() }}</dd></div>
                <div><dt class="text-gray-500">Jenis Servis</dt><dd class="mt-1 text-gray-900">{{ $record->serviceType?->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Tanggal</dt><dd class="mt-1 text-gray-900">{{ $record->service_date->format('d-m-Y') }}</dd></div>
                <div><dt class="text-gray-500">Odometer</dt><dd class="mt-1 tabular-nums text-gray-900">{{ number_format((int) $record->odometer, 0, ',', '.') }} km</dd></div>
                <div><dt class="text-gray-500">Vendor</dt><dd class="mt-1 text-gray-900">{{ $record->vendor?->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">No. Invoice</dt><dd class="mt-1 text-gray-900">{{ $record->invoice_number ?? '—' }}</dd></div>
                <div>
                    <dt class="text-gray-500">Total Biaya</dt>
                    <dd class="mt-1 tabular-nums font-medium text-gray-900">
                        {{ $record->total_cost !== null ? 'Rp '.number_format((float) $record->total_cost, 0, ',', '.') : '—' }}
                    </dd>
                </div>
                <div><dt class="text-gray-500">Ditanggung</dt><dd class="mt-1 text-gray-900">{{ $record->cost_borne_by->label() }}</dd></div>
            </dl>

            @if (filled($record->cost_borne_by_reason))
                <p class="mt-4 rounded-lg bg-gray-50 p-3 text-xs text-gray-600">
                    Alasan penanggung biaya: {{ $record->cost_borne_by_reason }}
                </p>
            @endif

            @if (filled($record->description))
                <div class="mt-4">
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Deskripsi Pekerjaan</h4>
                    <p class="mt-1 text-sm text-gray-700">{{ $record->description }}</p>
                </div>
            @endif

        </x-card>

        <div class="space-y-4">
            @if ($record->items->isNotEmpty())
                <x-card title="Rincian Item" :padded="false">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-white/80 backdrop-blur">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="px-4 py-2.5">Item</th>
                                <th class="px-4 py-2.5 text-right">Qty</th>
                                <th class="px-4 py-2.5 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($record->items as $item)
                                <tr>
                                    <td class="px-4 py-2.5 text-gray-900">
                                        {{ $item->item_name }}
                                        <p class="text-xs text-gray-500">{{ ucfirst($item->type) }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-2.5 text-right tabular-nums text-gray-600">{{ (float) $item->quantity }}</td>
                                    <td class="whitespace-nowrap px-4 py-2.5 text-right tabular-nums text-gray-900">
                                        Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-card>
            @endif

            @if ($record->attachment_path)
                <x-card title="Foto Nota / Invoice" class="overflow-hidden">
                    <x-image-viewer 
                        url="{{ Storage::url($record->attachment_path) }}" 
                        alt="Foto nota servis {{ $record->vehicle?->plate_number }}" 
                    />
                </x-card>
            @endif
        </div>
    </div>
</x-app-layout>
