import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Two independent bundles so public pages never download admin code:
//   web   → public website + member area
//   admin → admin panel
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/web.css',
                'resources/js/web.js',
                'resources/css/admin.css',
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        cssMinify: true,
        // Small bundles; inline nothing so CSP stays strict.
        assetsInlineLimit: 0,
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
