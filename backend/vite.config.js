import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/scss/app.scss',
        'resources/js/app.js',
        'resources/js/main.ts',
      ],
      refresh: true,
    }),
    vue(),
  ],
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
  },
});
