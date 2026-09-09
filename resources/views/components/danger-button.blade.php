<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-600 to-rose-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest shadow-lg shadow-red-500/20 transition-all duration-300 hover:-translate-y-1 hover:from-red-700 hover:to-rose-600 hover:shadow-xl hover:shadow-red-500/25 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
