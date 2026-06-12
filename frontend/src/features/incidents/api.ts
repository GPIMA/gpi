import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '@/lib/api/client'
import type { Incident, Paginated } from '@/lib/api/types'

export interface IncidentFilters {
  statut?: string
  priorite?: string
  page?: number
}

export interface IncidentInput {
  titre: string
  description: string
  equipementId: number
  priorite: string
}

export function useIncidents(filters: IncidentFilters) {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['incidents', filters, i18n.language],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Incident>>('/incidents', {
        params: {
          statut: filters.statut || undefined,
          priorite: filters.priorite || undefined,
          page: filters.page || 1,
        },
      })
      return data
    },
    placeholderData: (prev) => prev,
  })
}

function useInvalidate() {
  const qc = useQueryClient()
  return () => qc.invalidateQueries({ queryKey: ['incidents'] })
}

export function useCreateIncident() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (input: IncidentInput) => (await api.post('/incidents', input)).data,
    onSuccess: invalidate,
  })
}

export function usePrendreIncident() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (id: number) => (await api.post(`/incidents/${id}/prendre`)).data,
    onSuccess: invalidate,
  })
}

export function useResoudreIncident() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, solution }: { id: number; solution: string }) =>
      (await api.post(`/incidents/${id}/resoudre`, { solution })).data,
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['incidents'] })
      qc.invalidateQueries({ queryKey: ['notifications'] })
    },
  })
}
