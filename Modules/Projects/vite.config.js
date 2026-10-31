import { defineConfig } from 'vite';
import { fileURLToPath } from 'node:url';

export default defineConfig({
    publicDir: false,
    build: {
        outDir: fileURLToPath(new URL('./resources/dist', import.meta.url)),
        emptyOutDir: true,
        lib: {
            entry: fileURLToPath(new URL('./resources/assets/js/panel.js', import.meta.url)),
            formats: ['es'],
            fileName: () => 'panel.js',
        },
    },
});
