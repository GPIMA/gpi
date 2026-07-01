import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '@/lib/api/client'
import type { Equipement, Paginated } from '@/lib/api/types'

export interface EquipementFilters {
  q?: string
  type?: string
  etat?: string
  page?: number
}

export interface EquipementInput {
  nom: string
  type: string
  marque?: string | null
  modele?: string | null
  numeroSerie?: string | null
  adresseIP?: string | null
  adresseMAC?: string | null
  etat: string
  localisation?: string | null
  dateAcquisition?: string | null
  employeId?: number | null
}

export function useEquipements(filters: EquipementFilters) {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['equipements', filters, i18n.language],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Equipement>>('/equipements', {
        params: {
          q: filters.q || undefined,
          type: filters.type || undefined,
          etat: filters.etat || undefined,
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
  return () => qc.invalidateQueries({ queryKey: ['equipements'] })
}

export function useCreateEquipement() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (input: EquipementInput) =>
      (await api.post('/equipements', input)).data,
    onSuccess: invalidate,
  })
}

export function useUpdateEquipement(id: number) {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (input: Partial<EquipementInput>) =>
      (await api.put(`/equipements/${id}`, input)).data,
    onSuccess: invalidate,
  })
}

export function useDeleteEquipement() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (id: number) => (await api.delete(`/equipements/${id}`)).data,
    onSuccess: invalidate,
  })
}

export function useScanReseau() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async () =>
      (await api.post<{ message: string; scan: { nbDetectes: number } }>('/scan-reseau', {})).data,
    onSuccess: invalidate,
  })
}
export interface EmployeOption {
  id: number
  nomComplet: string
}

export function useEmployes() {
  return useQuery({
    queryKey: ['utilisateurs', 'EMPLOYE'],
    queryFn: async () => {
      const { data } = await api.get<Paginated<EmployeOption>>('/utilisateurs', {
        params: { role: 'EMPLOYE', per_page: 200 },
      })
      return data.data
    },
  })
}