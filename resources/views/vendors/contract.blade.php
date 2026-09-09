<x-app-layout title="Detail Kontrak Sewa">
    <x-slot name="header">
        <x-page-header :title="$contract->contract_number" :subtitle="$contract->vendor?->name"
                       :crumbs="['Master Data', 'Kontrak Sewa', $contract->contract_number]" />
    </x-slot>


    <x-card class="max-w-3xl">
        <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
            <div><dt class="text-gray-500">Vendor</dt><dd class="mt-1 font-medium text-gray-900">{{ $contract->vendor?->name }}</dd></div>
            <div><dt class="text-gray-500">Mulai</dt><dd class="mt-1 text-gray-900">{{ $contract->start_date->format('d-m-Y') }}</dd></div>
            <div><dt class="text-gray-500">Berakhir</dt><dd class="mt-1 text-gray-900">{{ $contract->end_date->format('d-m-Y') }}</dd></div>
            <div><dt class="text-gray-500">Biaya Bulanan</dt><dd class="mt-1 tabular-nums text-gray-900">Rp {{ number_format((float) $contract->monthly_cost, 0, ',', '.') }}</dd></div>
            <div><dt class="text-gray-500">Pengingat</dt><dd class="mt-1 text-gray-900">H-{{ $contract->reminder_days }}</dd></div>
            <div>
                <dt class="text-gray-500">Sisa Masa</dt>
                <dd class="mt-1 {{ $contract->days_remaining < 0 ? 'font-semibold text-red-600' : 'text-gray-900' }}">
                    {{ $contract->days_remaining < 0 ? 'Berakhir '.abs($contract->days_remaining).' hari lalu' : $contract->days_remaining.' hari lagi' }}
                </dd>
            </div>
            <div><dt class="text-gray-500">PIC</dt><dd class="mt-1 text-gray-900">{{ $contract->pic_name ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Telepon PIC</dt><dd class="mt-1 text-gray-900">{{ $contract->pic_phone ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Email PIC</dt><dd class="mt-1 text-gray-900">{{ $contract->pic_email ?? '—' }}</dd></div>
        </dl>

        <div class="mt-5 border-t border-gray-100 pt-4">
            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Cakupan Layanan</h4>
            <div class="mt-2 flex flex-wrap gap-2">
                @forelse ($contract->coverage ?? [] as $item => $included)
                    @if ($included)
                        <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700">
                            {{ ucfirst(str_replace('_', ' ', $item)) }}
                        </span>
                    @endif
                @empty
                    <span class="text-sm text-gray-500">Tidak ada cakupan tercatat.</span>
                @endforelse
            </div>
        </div>

        <div class="mt-5 border-t border-gray-100 pt-4">
            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kendaraan dalam Kontrak</h4>
            <p class="mt-2 text-sm text-gray-700">{{ $contract->vehicles->pluck('plate_number')->implode(', ') ?: '—' }}</p>
        </div>
    </x-card>
</x-app-layout>
