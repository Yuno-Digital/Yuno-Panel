<button {{ $attributes->merge(['type' => 'submit', 'class' => 'group relative inline-flex items-center justify-center gap-2 overflow-hidden px-4 py-2 rounded-lg bg-gradient-to-r from-brand-600 to-fuchsia-600 text-sm font-semibold text-white shadow-glow hover:shadow-glow-lg hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-200 disabled:opacity-50 disabled:pointer-events-none']) }}>
    <span class="pointer-events-none absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 bg-gradient-to-r from-transparent via-white/25 to-transparent"></span>
    <span class="relative inline-flex items-center gap-2">{{ $slot }}</span>
</button>
