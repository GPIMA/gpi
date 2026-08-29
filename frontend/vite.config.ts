import { defineConfig } from 'vite'
import { fileURLToPath, URL } from 'node:url'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    // Autorise l'accès via l'URL forwardée d'un GitHub Codespace
    // (proxy avec un Host différent de localhost, bloqué par défaut par Vite).
    allowedHosts: ['.app.github.dev'],
  },
})