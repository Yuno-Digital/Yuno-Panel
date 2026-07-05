@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-200 dark:border-white/10 bg-white/80 dark:bg-white/5 dark:text-gray-100 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-2 focus:ring-brand-400/40 rounded-lg shadow-sm transition duration-200']) }}>
