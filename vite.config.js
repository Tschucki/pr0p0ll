import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        tailwindcss(),
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/filament/pr0p0ll/theme.css'],
            ssr: 'resources/js/ssr.js',
            refresh: [
                ...refreshPaths,
                'app/Livewire/**',
            ],
        }),
    ],
});
