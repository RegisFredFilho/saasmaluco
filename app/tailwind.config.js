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
                sans: ['"DM Sans"', ...defaultTheme.fontFamily.sans],
                display: ['"Syne"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ink: {
                    DEFAULT: '#EEF1F8',
                    soft: '#B8C0D4',
                    muted: '#7E879C',
                },
                canvas: {
                    DEFAULT: '#070A12',
                    elev: '#111627',
                    line: '#1F2640',
                    mist: '#171D33',
                },
                accent: {
                    DEFAULT: '#4F7CFF',
                    hover: '#3D68F0',
                    soft: '#1A2550',
                    fg: '#A8C0FF',
                },
                vibe: {
                    DEFAULT: '#9B6BFF',
                    soft: '#2A1F4D',
                    fg: '#D2B8FF',
                },
            },
            boxShadow: {
                panel: '0 0 0 1px rgba(79, 124, 255, 0.04), 0 12px 40px rgba(0, 0, 0, 0.45)',
                glow: '0 0 40px rgba(79, 124, 255, 0.18)',
            },
            backgroundImage: {
                'stage-radial': 'radial-gradient(ellipse 80% 60% at 20% 0%, rgba(79,124,255,0.22), transparent 55%), radial-gradient(ellipse 60% 50% at 90% 20%, rgba(155,107,255,0.14), transparent 50%)',
                'stage-fade': 'linear-gradient(180deg, transparent 0%, #070A12 100%)',
            },
            maxWidth: {
                content: '76rem',
            },
        },
    },

    plugins: [forms],
};
