import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '@/lib/api/client'
import type { ModeleIA, Paginated, Prediction } from '@/lib/api/types'

export function usePredictions(page = 1) {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['predictions', page, i18n.language],
    queryFn: async () =>
      (await api.get<Paginated<Prediction>>('/predictions', { params: { page, per_page: 30 } })).data,
    placeholderData: (prev) => prev,
  })
}

export function useModeleIA() {
  return useQuery({
    queryKey: ['modele-ia'],
    queryFn: async () => (await api.get<{ data: ModeleIA | null }>('/predictions/modele')).data.data,
  })
}

export function useGenererPredictions() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async () => (await api.post<{ message: string }>('/predictions/generer')).data,
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['predictions'] })
      qc.invalidateQueries({ queryKey: ['alertes'] })
      qc.invalidateQueries({ queryKey: ['dashboard'] })
    },
  })
}
