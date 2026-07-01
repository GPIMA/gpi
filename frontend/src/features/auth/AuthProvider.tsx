import { useCallback, useEffect, useState } from 'react'
import { api, tokenStore } from '@/lib/api/client'
import type { LoginResponse, Utilisateur } from '@/lib/api/types'
import { AuthContext } from './auth-context'

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<Utilisateur | null>(null)
  const [ready, setReady] = useState(false)

  // Restore the session from a stored token on first load.
  useEffect(() => {
    let active = true
    const token = tokenStore.get()
    if (!token) {
      setReady(true)
      return
    }
    api
      .get<{ data: Utilisateur }>('/me')
      .then((res) => active && setUser(res.data.data))
      .catch(() => tokenStore.clear())
      .finally(() => active && setReady(true))
    return () => {
      active = false
    }
  }, [])

  const login = useCallback(async (email: string, password: string): Promise<Utilisateur> => {
  const res = await api.post<LoginResponse>('/login', { email, password })
  tokenStore.set(res.data.token)
  setUser(res.data.utilisateur)
  return res.data.utilisateur
}, [])

  const logout = useCallback(async () => {
    try {
      await api.post('/logout')
    } finally {
      tokenStore.clear()
      setUser(null)
    }
  }, [])

  return (
    <AuthContext.Provider value={{ user, ready, login, logout }}>
      {children}
    </AuthContext.Provider>
  )
}
