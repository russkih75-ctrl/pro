import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    // Keep root at theme directory so we can import theme files (e.g. style.css) from assets/src.
    root: resolve(__dirname),
    base: './',
    build: {
        outDir: resolve(__dirname, 'assets/dist'),
        emptyOutDir: true,
        rollupOptions: {
            input: {
                main: resolve(__dirname, 'assets/src/main.js'),
            },
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name].[ext]',
            },
        },
        cssCodeSplit: false,
        minify: 'terser',
    },
});
