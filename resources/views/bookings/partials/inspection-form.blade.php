{{-- FR-M2-20 & FR-M2-21 — form pemeriksaan serah terima / pengembalian. --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="odometer-{{ $isCheckout ? 'out' : 'in' }}"
                           :value="$isCheckout ? 'Odometer Awal (km)' : 'Odometer Akhir (km)'" />
            <x-text-input id="odometer-{{ $isCheckout ? 'out' : 'in' }}" name="odometer" type="number" min="0"
                          class="mt-1 block w-full"
                          :value="old('odometer', $isCheckout ? $booking->vehicle?->current_odometer : $booking->odometer_start)"
                          required />
            <x-input-error :messages="$errors->get('odometer')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="fuel_level-{{ $isCheckout ? 'out' : 'in' }}" value="Level BBM (fraksi tangki)" />
            <x-filter-select id="fuel_level-{{ $isCheckout ? 'out' : 'in' }}" name="fuel_level"
                             containerClass="mt-1 w-full block"
                             :options="['1' => 'Penuh (1/1)', '0.75' => '3/4', '0.5' => '1/2', '0.25' => '1/4', '0' => 'Kosong']"
                             :selected="old('fuel_level', '1')"
                             size="md" />
        </div>
    </div>

    <div>
        <x-input-label value="Kelengkapan Kendaraan" />
        <div class="mt-2 flex flex-wrap gap-4">
            @foreach (\App\Models\BookingInspection::CHECKLIST_ITEMS as $item)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="checklist[{{ $item }}]" value="0">
                    <input type="checkbox" name="checklist[{{ $item }}]" value="1" checked
                           class="rounded border-gray-300 text-usc-600 focus:ring-usc-400">
                    {{ ucfirst(str_replace('_', ' ', $item)) }}
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <x-input-label for="condition_notes-{{ $isCheckout ? 'out' : 'in' }}" value="Catatan Kondisi" />
        <textarea id="condition_notes-{{ $isCheckout ? 'out' : 'in' }}" name="condition_notes" rows="2"
                  class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10 shadow-sm"></textarea>
    </div>

    @unless ($isCheckout)
        <div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="damage_found" value="0">
                <input type="checkbox" name="damage_found" value="1"
                       class="rounded border-gray-300 text-usc-600 focus:ring-usc-400">
                Ditemukan kerusakan / insiden
            </label>
            <textarea name="damage_notes" rows="2" placeholder="Jelaskan kerusakan yang ditemukan"
                      class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-gray-900 outline-none transition placeholder:text-slate-400 focus:border-usc-500 focus:ring-4 focus:ring-usc-500/10 shadow-sm"></textarea>
            <x-input-error :messages="$errors->get('damage_notes')" class="mt-1" />
        </div>
    @endunless

    <div>
        <x-input-label :value="$isCheckout ? 'Foto Kondisi (4 sisi wajib)' : 'Foto Kondisi (opsional)'" />
        <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (\App\Models\BookingInspection::PHOTO_POSITIONS as $position)
                @php $required = $isCheckout && in_array($position, ['depan', 'belakang', 'kanan', 'kiri'], true); @endphp
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-600">
                        {{ ucfirst($position) }} @if ($required)<span class="text-red-500">*</span>@endif
                    </label>
                    <input type="file" name="photos[{{ $position }}]" accept="image/png,image/jpeg" @required($required)
                           class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-usc-50 file:text-usc-700 hover:file:bg-usc-100 border border-gray-200 rounded-lg bg-gray-50 overflow-hidden">
                    <x-input-error :messages="$errors->get('photos.'.$position)" class="mt-1" />
                </div>
            @endforeach
        </div>
    </div>

    @if ($isCheckout)
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="confirmed" value="1" required
                   class="rounded border-gray-300 text-usc-600 focus:ring-usc-400">
            Saya mengonfirmasi serah terima kendaraan sesuai kondisi di atas.
        </label>
        <x-input-error :messages="$errors->get('confirmed')" />
    @endif

    <div class="flex justify-end border-t border-gray-100 pt-4">
        <x-btn-primary icon="check-circle" type="submit" class="w-full justify-center sm:w-auto">
            {{ $isCheckout ? 'Catat Serah Terima' : 'Catat Pengembalian' }}
        </x-btn-primary>
    </div>
</form>
