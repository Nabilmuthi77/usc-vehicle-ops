@props(['id' => 'password', 'name' => 'password', 'required' => false, 'autocomplete' => 'current-password', 'placeholder' => '••••••••'])

<div class="relative w-full">
    <input id="{{ $id }}" class="block w-full rounded-md border-gray-300 pr-20 shadow-sm focus:border-usc-500 focus:ring-usc-500 sm:text-sm"
           type="password"
           name="{{ $name }}"
           {{ $required ? 'required' : '' }}
           autocomplete="{{ $autocomplete }}"
           placeholder="{{ $placeholder }}" />

    <button type="button" data-password-toggle="{{ $id }}"
        class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs font-semibold text-gray-500 hover:text-usc-600 focus:outline-none">
        Lihat
    </button>
</div>
