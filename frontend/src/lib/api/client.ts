import axios from 'axios'
import i18n from '@/lib/i18n'

// The backend is a separate deployment — its URL is injected at build time.
const baseURL = `${import.meta.env.VITE_API_URL ?? 'http://localhost:8000'}/api`

const TOKEN_KEY = 'hk_token'

export const tokenStore = {
  get: () => localStorage.getItem(TOKEN_KEY),
  set: (token: string) => localStorage.setItem(TOKEN_KEY, token),
  clear: () => localStorage.removeItem(TOKEN_KEY),
}

export const api = axios.create({
  baseURL,
  headers: { Accept: 'application/json' },
})

// Attach the bearer token and the active locale to every request.
api.interceptors.request.use((config) => {
  const token = tokenStore.get()
  if (token) config.headers.Authorization = `Bearer ${token}`
  config.headers['X-Locale'] = i18n.language
  return config
})

// A 401 means the token is gone or stale — drop it and bounce to login.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      tokenStore.clear()
      if (!window.location.pathname.startsWith('/login')) {
        window.location.assign('/login')
      }
    }
    return Promise.reject(error)
  },
)
