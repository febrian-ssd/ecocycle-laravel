import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path'; // <-- Import path

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss', // <-- Ubah dari .css ke .scss
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    // === TAMBAHKAN BAGIAN INI ===
    resolve: {
        alias: {
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
        }
    },
    // ============================
});
