<x-app-layout title="Tambah Dokumen">
    <x-slot name="header">
        <x-page-header title="Tambah Dokumen Kendaraan" :subtitle="$vehicle->plate_number.' · '.$vehicle->full_name" :crumbs="['Master Data', 'Kendaraan', 'Dokumen', 'Tambah']" />
    </x-slot>

    <div class="mx-auto max-w-2xl">
        <x-card>
            <form method="POST" action="{{ route('vehicles.documents.store', $vehicle) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="space-y-1">
                        <x-input-label for="document_type" value="Jenis Dokumen" />
                        <select id="document_type" name="document_type" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-usc-400 focus:ring-usc-400" required>
                            <option value="">Pilih Jenis Dokumen...</option>
                            @foreach ($documentTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('document_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('document_type')" />
                    </div>

                    <div class="space-y-1">
                        <x-input-label for="document_number" value="Nomor Dokumen" />
                        <x-text-input id="document_number" name="document_number" type="text" class="block w-full" :value="old('document_number')" />
                        <x-input-error :messages="$errors->get('document_number')" />
                    </div>

                    <div class="space-y-1">
                        <x-input-label for="issued_date" value="Tanggal Terbit" />
                        <x-text-input id="issued_date" name="issued_date" type="date" class="block w-full" :value="old('issued_date')" />
                        <x-input-error :messages="$errors->get('issued_date')" />
                    </div>

                    <div class="space-y-1">
                        <x-input-label for="expiry_date" value="Tanggal Berakhir (Kedaluwarsa)" />
                        <x-text-input id="expiry_date" name="expiry_date" type="date" class="block w-full" :value="old('expiry_date')" required />
                        <x-input-error :messages="$errors->get('expiry_date')" />
                    </div>

                    <div class="space-y-1">
                        <x-input-label for="reminder_days" value="Pengingat (Hari sebelum kedaluwarsa)" />
                        <x-text-input id="reminder_days" name="reminder_days" type="number" min="1" max="90" class="block w-full" :value="old('reminder_days', 30)" required />
                        <x-input-error :messages="$errors->get('reminder_days')" />
                    </div>
                </div>

                <div class="space-y-1">
                    <x-input-label for="file" value="File Dokumen (Opsional)" />
                    <input id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-usc-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-usc-700 hover:file:bg-usc-100" />
                    <p class="mt-1 text-xs text-gray-500">Maks. 5MB. Format: PDF, JPG, PNG.</p>
                    <x-input-error :messages="$errors->get('file')" />
                </div>

                <div class="space-y-1">
                    <x-input-label for="notes" value="Catatan (Opsional)" />
                    <textarea id="notes" name="notes" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-usc-400 focus:ring-usc-400 sm:text-sm">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" />
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                    <a href="{{ route('vehicles.show', $vehicle) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                    <x-btn-primary type="submit">Simpan Dokumen</x-btn-primary>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
