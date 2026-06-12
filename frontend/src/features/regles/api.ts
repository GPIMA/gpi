import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '@/lib/api/client'
import type { RegleAlerte } from '@/lib/api/types'

export interface RegleInput {
  nom: string
  metriqueCible: string
  operateur: string
  seuil: number
  severite: string
  typeAlerte: string
  actif: boolean
}

export function useRegles() {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['regles', i18n.language],
    queryFn: async () => (await api.get<{ data: RegleAlerte[] }>('/regles-alerte')).data.data,
  })
}

function useInvalidate() {
  const qc = useQueryClient()
  return () => qc.invalidateQueries({ queryKey: ['regles'] })
}

export function useCreateRegle() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (input: RegleInput) => (await api.post('/regles-alerte', input)).data,
    onSuccess: invalidate,
  })
}

export function useUpdateRegle(id: number) {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (input: Partial<RegleInput>) => (await api.put(`/regles-alerte/${id}`, input)).data,
    onSuccess: invalidate,
  })
}

export function useDeleteRegle() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (id: number) => (await api.delete(`/regles-alerte/${id}`)).data,
    onSuccess: invalidate,
  })
}
