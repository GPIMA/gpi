import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '@/lib/api/client'
import type { Paginated, Utilisateur } from '@/lib/api/types'

export interface UtilisateurFilters {
  q?: string
  role?: string
  localisation?: string
  page?: number
}

export interface UtilisateurInput {
  nom: string
  prenom: string
  email: string
  password?: string
  role: string
  telephone?: string | null
  departement?: string | null
  localisation?: string | null
}

export function useUtilisateurs(filters: UtilisateurFilters) {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['utilisateurs', filters, i18n.language],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Utilisateur>>('/utilisateurs', {
        params: { q: filters.q || undefined, role: filters.role || undefined, localisation: filters.localisation || undefined, page: filters.page || 1 }, })
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
export interface HistoriqueEquipementDetail {
  id: number
  nom: string
  type: string | null
  typeLabel: string | null
  marque: string | null
  modele: string | null
  numeroSerie: string | null
  adresseIP: string | null
  adresseMAC: string | null
  etat: string | null
  etatLabel: string | null
  localisation: string | null
  dateAcquisition: string | null
}

export interface HistoriqueEntry {
  id: number
  action: string
  description: string
  createdAt: string
  equipement: HistoriqueEquipementDetail | null
  technicienAssigne: string | null
  auteur: string | null
}

export function useHistoriqueUtilisateur(userId: number | null) {
  return useQuery({
    queryKey: ['historique-utilisateur', userId],
    queryFn: async () => {
      const { data } = await api.get<HistoriqueEntry[]>(`/utilisateurs/${userId}/historique`)
      return data
    },
    enabled: !!userId,
  })
}

export function useAjouterCommentaireUtilisateur(userId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (commentaire: string) =>
      (await api.post<HistoriqueEntry>(`/utilisateurs/${userId}/commentaires`, { commentaire })).data,
    onSuccess: () => qc.invalidateQueries({ queryKey: ['historique-utilisateur', userId] }),
  })
}