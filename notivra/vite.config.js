import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import inertia from '@inertiajs/vite';
import vue from '@vitejs/plugin-vue';


export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/blog.js'],
            hotFile: '../public_html/hot',
            refresh: true,
            refresh: [
                'resources/views/**/*.blade.php',
                'routes/**/*.php',
            ],
        }),
        tailwindcss(),
        inertia({
            ssr: false,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
        }
    },
    server: {
        hmr: {
            host: 'localhost',
        },
        watch: {
            usePolling: true,
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
