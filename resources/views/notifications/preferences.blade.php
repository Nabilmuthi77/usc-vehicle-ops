<x-app-layout title="Preferensi Notifikasi">
    <x-slot name="header">
        <x-page-header title="Preferensi Notifikasi" subtitle="Pilih kanal untuk setiap jenis kejadian — in-app, email, atau keduanya" :crumbs="['Notifikasi', 'Preferensi']">
            <x-slot name="actions">
                <x-btn-primary form="preferences-form" icon="check-circle" type="submit" class="w-full sm:w-auto justify-center">Simpan Preferensi</x-btn-primary>
            </x-slot>
        </x-page-header>
    </x-slot>


    {{-- FR-M7-04 & K-04 — kanal terbatas pada in-app dan email. --}}
    <x-card :padded="false" class="max-w-3xl overflow-hidden">
        <form id="preferences-form" method="POST" action="{{ route('notifications.preferences.update') }}">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-white/80 backdrop-blur">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <th class="px-5 py-3 whitespace-nowrap">Jenis Kejadian</th>
                            <th class="px-5 py-3 text-center whitespace-nowrap">In-App</th>
                            <th class="px-5 py-3 text-center whitespace-nowrap">Email</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($events as $key => $label)
                            @php $pref = $preferences[$key] ?? null; @endphp
                            <tr>
                                <td class="px-5 py-3 text-gray-900">{{ $label }}</td>
                                <td class="px-5 py-3 text-center">
                                    <input type="hidden" name="preferences[{{ $key }}][via_database]" value="0">
                                    <input type="checkbox" name="preferences[{{ $key }}][via_database]" value="1"
                                           @checked($pref?->via_database ?? true)
                                           class="rounded border-gray-300 text-usc-600 focus:ring-usc-400">
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <input type="hidden" name="preferences[{{ $key }}][via_mail]" value="0">
                                    <input type="checkbox" name="preferences[{{ $key }}][via_mail]" value="1"
                                           @checked($pref?->via_mail ?? true)
                                           class="rounded border-gray-300 text-usc-600 focus:ring-usc-400">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </form>
    </x-card>
</x-app-layout>
