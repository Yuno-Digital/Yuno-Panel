import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                    950: '#1e1b4b',
                },
            },
            boxShadow: {
                glow: '0 10px 30px -10px rgba(99,102,241,.55)',
                'glow-lg': '0 20px 60px -15px rgba(139,92,246,.5)',
            },
            keyframes: {
                'fade-in': { '0%': { opacity: 0 }, '100%': { opacity: 1 } },
                'fade-in-up': {
                    '0%': { opacity: 0, transform: 'translateY(14px)' },
                    '100%': { opacity: 1, transform: 'translateY(0)' },
                },
                'scale-in': {
                    '0%': { opacity: 0, transform: 'scale(.96)' },
                    '100%': { opacity: 1, transform: 'scale(1)' },
                },
                float: {
                    '0%,100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-16px)' },
                },
                'gradient-x': {
                    '0%,100%': { 'background-position': '0% 50%' },
                    '50%': { 'background-position': '100% 50%' },
                },
                shimmer: { '100%': { transform: 'translateX(100%)' } },
            },
            animation: {
                'fade-in': 'fade-in .5s ease-out both',
                'fade-in-up': 'fade-in-up .6s cubic-bezier(.21,1.02,.73,1) both',
                'scale-in': 'scale-in .4s ease-out both',
                float: 'float 9s ease-in-out infinite',
                'gradient-x': 'gradient-x 6s ease infinite',
                shimmer: 'shimmer 2.5s infinite',
            },
        },
    },

    plugins: [forms],
};
