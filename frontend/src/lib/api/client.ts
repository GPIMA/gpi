import axios from 'axios'
import i18n from '@/lib/i18n'

const OVERRIDE_KEY = 'gpi_api_url'

function normalizeApiBaseUrl(): string {
  if (import.meta.env.PROD) {
    const origin = (import.meta.env.VITE_API_URL?.trim() || '').replace(/\/$/, '')
    return origin ? `${origin}/api` : '/api'
  }

  // Échappatoire manuelle, au besoin : localStorage.setItem('gpi_api_url', 'http://127.0.0.1:8001')
  // — mais en temps normal inutile, la détection automatique ci-dessous s'en charge.
  const override = window.localStorage.getItem(OVERRIDE_KEY)?.trim()
  if (override) {
    return `${override.replace(/\/$/, '')}/api`
  }

  // Dev : déduit le backend de l'endroit où le frontend lui-même a été
  // ouvert, au lieu de se fier uniquement à VITE_API_URL figé au démarrage
  // de Vite. Évite un backend inaccessible selon qu'on ouvre l'app via
  // localhost ou l'URL forwardée d'un Codespace (*-5173.app.github.dev).
  const { protocol, hostname } = window.location
  if (hostname.endsWith('.app.github.dev')) {
    const backendHost = hostname.replace(/-5173(?=\.app\.github\.dev$)/, '-8000')
    return `${protocol}//${backendHost}/api`
  }

  const raw = import.meta.env.VITE_API_URL?.trim()
  const origin = (raw || 'http://localhost:8000').replace(/\/$/, '')
  return `${origin}/api`
}

const baseURL = normalizeApiBaseUrl()
const TOKEN_KEY = 'gpi_token'

export const tokenStore = {
  get: () => localStorage.getItem(TOKEN_KEY),
  set: (token: string) => localStorage.setItem(TOKEN_KEY, token),
  clear: () => localStorage.removeItem(TOKEN_KEY),
}

export const api = axios.create({
  baseURL,
  headers: { Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = tokenStore.get()
  if (token) config.headers.Authorization = `Bearer ${token}`
  config.headers['X-Locale'] = i18n.language
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      tokenStore.clear()
      if (!window.location.pathname.startsWith('/login')) {
        // window.location.assign('/login')
      }
    }
    return Promise.reject(error)
  },
)

// --- Détection automatique du port du backend (dev, localhost uniquement) ---
// VS Code Desktop peut rediriger un port forwardé vers un port local différent
// en cas de conflit (ex. 8000 -> 8001), rendant l'URL par défaut injoignable
// sans que rien dans le code ne le sache. Plutôt que de demander une manip
// manuelle, on sonde silencieusement quelques ports voisins au chargement et
// on bascule dessus automatiquement dès qu'un backend GPI y répond.
if (!import.meta.env.PROD) {
  const { protocol, hostname } = window.location
  const isLocalHost = hostname === 'localhost' || hostname === '127.0.0.1'
  const hasOverride = !!window.localStorage.getItem(OVERRIDE_KEY)?.trim()

  if (isLocalHost && !hasOverride) {
    void autoDetectApiPort(protocol, hostname)
  }
}

async function pingHealth(origin: string, timeoutMs = 1500): Promise<boolean> {
  try {
    const controller = new AbortController()
    const timer = setTimeout(() => controller.abort(), timeoutMs)
    const res = await fetch(`${origin}/api/health`, { signal: controller.signal })
    clearTimeout(timer)
    return res.ok
  } catch {
    return false
  }
}

async function autoDetectApiPort(protocol: string, hostname: string): Promise<void> {
  const currentOrigin = (api.defaults.baseURL ?? '').replace(/\/api$/, '')
  if (await pingHealth(currentOrigin)) return // le backend attendu répond déjà, rien à faire

  const candidates = [8000, 8001, 8002, 8003, 8080]
    .map((port) => `${protocol}//${hostname}:${port}`)
    .filter((origin) => origin !== currentOrigin)

  try {
    const found = await Promise.any(
      candidates.map(async (origin) => {
        if (!(await pingHealth(origin))) throw new Error('unreachable')
        return origin
      }),
    )
    window.localStorage.setItem(OVERRIDE_KEY, found)
    api.defaults.baseURL = `${found}/api`
    console.info(`[GPI] Backend détecté automatiquement sur ${found} (${currentOrigin} ne répondait pas).`)
  } catch {
    // Aucun port voisin ne répond : le backend est probablement vraiment
    // injoignable, l'erreur réseau normale de l'app s'affichera.
  }
}
