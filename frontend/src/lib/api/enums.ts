import { useQuery } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from './client'
import type { EnumDictionary } from './types'

/**
 * Loads the localized data-dictionary from the API. Re-fetches when the UI
 * language changes so labels stay in sync. This is the single source for every
 * option list in the app — none are hardcoded in components.
 */
export function useEnums() {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['enums', i18n.language],
    queryFn: async () => (await api.get<EnumDictionary>('/enums')).data,
    staleTime: 1000 * 60 * 30,
  })
}
