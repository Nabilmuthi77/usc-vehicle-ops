<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Ubah Foto Profil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Pilih foto profil baru untuk akun Anda.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.photo') }}" enctype="multipart/form-data" class="mt-6 space-y-6" novalidate>
        @csrf

        <div class="flex flex-col sm:flex-row items-start gap-6">
            <!-- Photo Preview -->
            <div class="shrink-0 relative group">
                @if ($user->photo_profile)
                    <img class="h-27 w-33 object-cover rounded-2xl shadow-sm ring-6 ring-usc-50" src="{{ \Illuminate\Support\Facades\Storage::url($user->photo_profile) }}" alt="Current profile photo" />
                @else
                    <div class="flex h-32 w-32 shrink-0 items-center justify-center rounded-2xl bg-usc-50 text-4xl font-bold text-usc-600 shadow-sm ring-4 ring-white">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <!-- Input and Button -->
            <div class="flex-1 w-full space-y-4">
                <label class="block">
                    <span class="sr-only">Pilih foto profil</span>
                    <div class="relative flex items-center rounded-xl overflow-hidden border border-usc-100">
                        <input type="file" name="photo" id="photo" class="block w-full text-sm text-slate-500
                            file:mr-4 file:py-1.5 file:px-5
                            file:border-0 file:border-r file:border-usc-100
                            file:text-sm file:font-semibold
                            file:bg-usc-50 file:text-usc-700
                            hover:file:bg-usc-100 cursor-pointer focus:outline-none" />
                    </div>
                    <p class="mt-2 text-xs text-slate-400 ml-2">Format: JPG, PNG, GIF (Maks. 2MB)</p>
                </label>

                <div class="flex items-center justify-end">
                    <x-primary-button>{{ __('Simpan') }}</x-primary-button>
                </div>
            </div>
        </div>
    </form>
</section>
