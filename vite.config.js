import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/admin.css',
                'resources/css/site-forms.css',
                'resources/css/admin-dark.css',
                'resources/css/admin-login.css',
                'resources/js/panel.js',
                'resources/css/frontend/default/app.css',
                'resources/js/frontend/default/app.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
                bunny('Manrope', {
                    weights: [400, 500, 600, 700, 800],
                    subsets: ['latin', 'latin-ext'],
                    preload: [{ weight: 400 }, { weight: 700 }],
                    optimizedFallbacks: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
