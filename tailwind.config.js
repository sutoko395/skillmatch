import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'bg-light': '#F8FAFC',
                'bg-brand': '#1E1B4B',
                'brand-primary': '#4F46E5',
                'txt-light-primary': '#0F172A',
                'txt-light-secondary': '#64748B',
                'txt-dark-primary': '#F8FAFC',
                'txt-dark-secondary': '#94A3B8',
            }
        },
    },

    plugins: [forms],
};