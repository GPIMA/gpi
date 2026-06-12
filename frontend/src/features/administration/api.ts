import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '@/lib/api/client'
import type { Paginated, Utilisateur } from '@/lib/api/types'

export interface UtilisateurFilters {
  q?: string
  role?: string
  page?: number
}

export interface UtilisateurInput {
  nom: string
  prenom: string
  email: string
  password?: string
  role: string
  telephone?: string | null
  specialite?: string | null
  departement?: string | null
}

export function useUtilisateurs(filters: UtilisateurFilters) {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['utilisateurs', filters, i18n.language],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Utilisateur>>('/utilisateurs', {
        params: { q: filters.q || undefined, role: filters.role || undefined, page: filters.page || 1 },
      })
      return data
    },
    placeholderData: (prev) => prev,
  })
}

function useInvalidate() {
  const qc = useQueryClient()
  return () => qc.invalidateQueries({ queryKey: ['utilisateurs'] })
}

export function useCreateUtilisateur() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (input: UtilisateurInput) => (await api.post('/utilisateurs', input)).data,
    onSuccess: invalidate,
  })
}

export function useUpdateUtilisateur(id: number) {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (input: Partial<UtilisateurInput>) => (await api.put(`/utilisateurs/${id}`, input)).data,
    onSuccess: invalidate,
  })
}

export function useDeleteUtilisateur() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (id: number) => (await api.delete(`/utilisateurs/${id}`)).data,
    onSuccess: invalidate,
  })
}
