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
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    watch: {
      usePolling: true,
      interval: 1500,
      ignored: [
        '**/node_modules/**',
        '**/vendor/**',
        '**/storage/**',
        '**/public/build/**',
      ],
    },
    hmr: {
      host: 'localhost',
      port: 5173,
    },
  },
});
