<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white/80 border border-usc-200 rounded-lg font-semibold text-xs text-usc-700 uppercase tracking-widest shadow-sm backdrop-blur hover:bg-usc-50 focus:outline-none focus:ring-2 focus:ring-usc-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-300']) }}>
    {{ $slot }}
</button>
