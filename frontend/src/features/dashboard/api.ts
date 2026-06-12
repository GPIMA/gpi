import { useQuery } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '@/lib/api/client'
import type { DashboardData } from '@/lib/api/types'

export function useDashboard() {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['dashboard', i18n.language],
    queryFn: async () => (await api.get<DashboardData>('/dashboard')).data,
    refetchInterval: 30_000,
  })
}
