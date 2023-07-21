import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [svelte()],
  build: {
    emptyOutDir: true,
    minify: true,
    target: 'es2015',
    sourcemap: true,
    rollupOptions: {
      input: {
        main: './src/main.ts',
      },
      output: {
        entryFileNames: 'bulk_delete.js',
        chunkFileNames: '[name].js',
        assetFileNames: '[name].[ext]',
      },
      
    },      
  }
})
