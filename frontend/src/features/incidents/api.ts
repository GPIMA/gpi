import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '@/lib/api/client'
import type { Incident, IncidentCommentaire, Paginated } from '@/lib/api/types'

export interface IncidentFilters {
  q?: string
  statut?: string
  priorite?: string
  /** employe = signalés par un employé, personnel = par un membre du staff. */
  origine?: 'employe' | 'personnel'
  /** Vue personnelle : uniquement les incidents où je suis la personne concernée. */
  mesIncidents?: boolean
  page?: number
}

export interface IncidentInput {
  titre: string
  description: string
  equipementId: number
  priorite: string
  /** Utilisateur concerné (requis quand le déclarant n'est pas un Employé). */
  utilisateurId?: number
  pieceJointes?: File[]
}

export function useIncidents(filters: IncidentFilters) {
  const { i18n } = useTranslation()
  return useQuery({
    queryKey: ['incidents', filters, i18n.language],
    queryFn: async () => {
      const { data } = await api.get<Paginated<Incident>>('/incidents', {
        params: {
          q: filters.q || undefined,
          statut: filters.statut || undefined,
          priorite: filters.priorite || undefined,
          origine: filters.origine || undefined,
          mes_incidents: filters.mesIncidents || undefined,
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
    mutationFn: async (input: IncidentInput) => {
      const formData = new FormData()
      formData.append('titre', input.titre)
      formData.append('description', input.description)
      formData.append('equipementId', String(input.equipementId))
      formData.append('priorite', input.priorite)
      if (input.utilisateurId) formData.append('utilisateurId', String(input.utilisateurId))
      input.pieceJointes?.forEach((fichier) => formData.append('pieceJointes[]', fichier))
      return (await api.post('/incidents', formData, { headers: { 'Content-Type': 'multipart/form-data' } })).data
    },
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
export function useDemanderRestitution() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, dateRestitution }: { id: number; dateRestitution: string }) =>
      (await api.post(`/incidents/${id}/demander-restitution`, { dateRestitution })).data,
    onSuccess: (_data, variables) => {
      qc.invalidateQueries({ queryKey: ['incidents'] })
      qc.invalidateQueries({ queryKey: ['incidents', variables.id, 'commentaires'] })
      qc.invalidateQueries({ queryKey: ['notifications'] })
    },
  })
}
export interface TraiterRetourInput {
  id: number
  motif: 'MAINTENANCE_SUR_PLACE' | 'NOUVELLE_DATE' | 'POSTE_REMPLACE'
  commentaire?: string
  dateRestitution?: string
  nouvelEquipementId?: number
  /** Motif "Nouvelle date" uniquement : poste remplaçant temporaire (optionnel). */
  nouvelEquipementRemplacementId?: number
}

export function useTraiterRetour() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, ...body }: TraiterRetourInput) =>
      (await api.post(`/incidents/${id}/traiter-retour`, body)).data,
    onSuccess: (_data, variables) => {
      qc.invalidateQueries({ queryKey: ['incidents'] })
      qc.invalidateQueries({ queryKey: ['incidents', variables.id, 'commentaires'] })
      qc.invalidateQueries({ queryKey: ['notifications'] })
      qc.invalidateQueries({ queryKey: ['equipements'] })
    },
  })
}

    export function useTechniciens() {
  return useQuery({
    queryKey: ['utilisateurs', 'TECHNICIEN'],
    queryFn: async () => {
      const { data } = await api.get<Paginated<{ id: number; nomComplet: string; localisation: string | null }>>('/utilisateurs', {
        params: { role: 'TECHNICIEN', per_page: 200 },
      })
      return data.data
    },
  })
}

// Ouvrir la consultation d'un incident "Ouvert" (admin/super admin) le fait
// automatiquement passer à "En cours" côté serveur.
export function useConsulterIncident() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) => {
      const { data } = await api.post<{ data: Incident }>(`/incidents/${id}/consulter`)
      return data.data
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ['incidents'] }),
  })
}

export function useAssignerIncident() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async ({ id, technicienId }: { id: number; technicienId: number }) =>
      (await api.post(`/incidents/${id}/assigner`, { technicien_id: technicienId })).data,
    onSuccess: invalidate,
  })
}
export function useIncidentCommentaires(incidentId: number | null) {
  return useQuery({
    queryKey: ['incidents', incidentId, 'commentaires'],
    queryFn: async () => {
      const { data } = await api.get<{ data: IncidentCommentaire[] }>(`/incidents/${incidentId}/commentaires`)
      return data.data
    },
    enabled: incidentId !== null,
  })
}

export function useAjouterCommentaire() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, contenu }: { id: number; contenu: string }) =>
      (await api.post<IncidentCommentaire>(`/incidents/${id}/commentaires`, { contenu })).data,
    onSuccess: (_data, variables) => {
      qc.invalidateQueries({ queryKey: ['incidents', variables.id, 'commentaires'] })
      qc.invalidateQueries({ queryKey: ['notifications'] })
    },
  })
}
export function useReouvrirIncident() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, message }: { id: number; message: string }) =>
      (await api.post(`/incidents/${id}/reouvrir`, { message })).data,
    onSuccess: (_data, variables) => {
      qc.invalidateQueries({ queryKey: ['incidents'] })
      qc.invalidateQueries({ queryKey: ['incidents', variables.id, 'commentaires'] })
      qc.invalidateQueries({ queryKey: ['notifications'] })
    },
  })
}

// Suppression définitive par l'employé concerné — un commentaire justifiant
// la suppression est obligatoire.
export function useSupprimerIncident() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, commentaire }: { id: number; commentaire: string }) =>
      (await api.post(`/incidents/${id}/supprimer`, { commentaire })).data,
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['incidents'] })
      qc.invalidateQueries({ queryKey: ['notifications'] })
    },
  })
}