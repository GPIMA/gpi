import axios from 'axios'
import i18n from '@/lib/i18n'

function normalizeApiBaseUrl(): string {
  const raw = import.meta.env.VITE_API_URL?.trim()
  const fallback = import.meta.env.PROD ? '' : 'http://localhost:8000'
  const origin = (raw || fallback).replace(/\/$/, '')

  return origin ? `${origin}/api` : '/api'
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
        window.location.assign('/login')
      }
    }
    return Promise.reject(error)
  },
)
