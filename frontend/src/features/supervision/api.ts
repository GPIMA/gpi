import { useQuery } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '@/lib/api/client'
import type { Equipement, Metrique, SupervisionRow } from '@/lib/api/types'

export function useSupervisionApercu() {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['supervision-apercu', i18n.language],
    queryFn: async () => (await api.get<{ data: SupervisionRow[] }>('/supervision/apercu')).data.data,
    refetchInterval: 30_000,
  })
}

export function useMetriqueHistorique(equipementId: number | null) {
  return useQuery({
    queryKey: ['metriques', equipementId],
    enabled: equipementId != null,
    queryFn: async () => {
      const { data } = await api.get<{ equipement: Equipement; data: Metrique[] }>(
        `/equipements/${equipementId}/metriques`,
        { params: { limite: 96 } },
      )
      return data
    },
  })
}
