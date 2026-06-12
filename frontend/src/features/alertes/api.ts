import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '@/lib/api/client'
import type { Alerte, Paginated } from '@/lib/api/types'

export interface AlerteFilters {
  etat?: string
  severite?: string
  page?: number
}

export function useAlertes(filters: AlerteFilters) {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['alertes', filters, i18n.language],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Alerte>>('/alertes', {
        params: {
          etat: filters.etat || undefined,
          severite: filters.severite || undefined,
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
  return () => {
    qc.invalidateQueries({ queryKey: ['alertes'] })
    qc.invalidateQueries({ queryKey: ['dashboard'] })
  }
}

export function usePrendreAlerte() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (id: number) => (await api.post(`/alertes/${id}/prendre`)).data,
    onSuccess: invalidate,
  })
}

export function useResoudreAlerte() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (id: number) => (await api.post(`/alertes/${id}/resoudre`)).data,
    onSuccess: invalidate,
  })
}
