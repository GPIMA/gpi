import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '@/lib/api/client'
import type { NotificationItem } from '@/lib/api/types'

export function useNotifications() {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['notifications', i18n.language],
    queryFn: async () =>
      (await api.get<{ data: NotificationItem[]; nonLues: number }>('/notifications')).data,
    refetchInterval: 30_000,
  })
}

export function useMarquerLues() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async () => (await api.post('/notifications/lues')).data,
    onSuccess: () => qc.invalidateQueries({ queryKey: ['notifications'] }),
  })
}
