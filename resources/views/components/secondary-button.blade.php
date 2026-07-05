<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/70 dark:bg-white/5 border border-gray-200 dark:border-white/10 backdrop-blur text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-white dark:hover:bg-white/10 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-40 transition-all duration-200']) }}>
    {{ $slot }}
</button>
