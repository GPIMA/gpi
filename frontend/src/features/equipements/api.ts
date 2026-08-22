import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '@/lib/api/client'
import type { Equipement, Paginated } from '@/lib/api/types'

 export interface EquipementFilters {
  q?: string
  type?: string
  etat?: string
  localisation?: string
  statutAffectation?: string
  /** Équipements actuellement affectés à cet utilisateur (id). */
  assigneA?: number
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
  forcerAffectation?: boolean
}

export function useEquipements(filters: EquipementFilters, options?: { enabled?: boolean }) {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['equipements', filters, i18n.language],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Equipement>>('/equipements', {
        params: {
          q: filters.q || undefined,
          type: filters.type || undefined,
          etat: filters.etat || undefined,
          localisation: filters.localisation || undefined,
          statut_affectation: filters.statutAffectation || undefined,
          assigne_a: filters.assigneA || undefined,
          page: filters.page || 1,
        },
      })
      return data
    },
    placeholderData: (prev) => prev,
    enabled: options?.enabled ?? true,
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

export interface EmployeOption {
  id: number
  nomComplet: string
  role: string
  roleLabel: string
}

// Utilisateurs éligibles au champ "Affecté à" : tous les rôles (employé,
// technicien, admin, super admin). Le scope par site (un Admin ne voit que
// les utilisateurs de son site) est déjà appliqué côté backend dans
// UtilisateurController::index — le Super Admin, lui, voit tout le monde.
export function useEmployes(options?: { enabled?: boolean }) {
  return useQuery({
    queryKey: ['utilisateurs', 'affectables'],
    queryFn: async () => {
      const { data } = await api.get<Paginated<EmployeOption>>('/utilisateurs', {
        params: { per_page: 500 },
      })
      return [...data.data].sort((a, b) => a.nomComplet.localeCompare(b.nomComplet, 'fr'))
    },
    enabled: options?.enabled ?? true,
  })
}
export function useLocalisations() {
  return useQuery({
    queryKey: ['equipements-localisations'],
    queryFn: async () => {
      const { data } = await api.get<{ data: string[] }>('/equipements/localisations')
      return data.data
    },
  })
}
export interface HistoriqueUtilisateurDetail {
  id: number
  nomComplet: string
  email: string
  telephone: string | null
  role: string | null
  roleLabel: string | null
  departement: string | null
  localisation: string | null
}

export interface HistoriqueEquipementEntry {
  id: number
  action: string
  description: string
  createdAt: string
  utilisateur: HistoriqueUtilisateurDetail | null
  technicienAssigne: string | null
  auteur: string | null
}

export function useHistoriqueEquipement(equipementId: number | null) {
  return useQuery({
    queryKey: ['historique-equipement', equipementId],
    queryFn: async () => {
      const { data } = await api.get<HistoriqueEquipementEntry[]>(`/equipements/${equipementId}/historique`)
      return data
    },
    enabled: !!equipementId,
  })
}

export function useAjouterCommentaireEquipement(equipementId: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (commentaire: string) =>
      (await api.post<HistoriqueEquipementEntry>(`/equipements/${equipementId}/commentaires`, { commentaire })).data,
    onSuccess: () => qc.invalidateQueries({ queryKey: ['historique-equipement', equipementId] }),
  })
}