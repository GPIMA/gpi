import { createContext, useContext } from 'react'
import type { Utilisateur } from '@/lib/api/types'

export interface AuthState {
  user: Utilisateur | null
  ready: boolean
  login: (email: string, password: string) => Promise<Utilisateur>
  logout: () => Promise<void>
}

export const AuthContext = createContext<AuthState | null>(null)

export function useAuth(): AuthState {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within <AuthProvider>')
  return ctx
}
