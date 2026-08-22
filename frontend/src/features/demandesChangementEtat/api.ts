import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { api } from '@/lib/api/client'
import type { DemandeChangementEtat, DemandeChangementEtatCommentaire, Paginated } from '@/lib/api/types'

export interface DemandeChangementEtatFilters {
  statut?: string
  page?: number
}

export function useDemandesChangementEtat(filters: DemandeChangementEtatFilters) {
  return useQuery({
    queryKey: ['demandes-changement-etat', filters],
    queryFn: async () => {
      const { data } = await api.get<Paginated<DemandeChangementEtat>>('/demandes-changement-etat', {
        params: {
          statut: filters.statut || undefined,
          per_page: 20,
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
    qc.invalidateQueries({ queryKey: ['demandes-changement-etat'] })
    qc.invalidateQueries({ queryKey: ['equipements'] })
    qc.invalidateQueries({ queryKey: ['notifications'] })
  }
}

export function useApprouverDemandeChangementEtat() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async (id: number) => (await api.post(`/demandes-changement-etat/${id}/approuver`)).data,
    onSuccess: invalidate,
  })
}

export function useRejeterDemandeChangementEtat() {
  const invalidate = useInvalidate()
  return useMutation({
    mutationFn: async ({ id, commentaire }: { id: number; commentaire?: string }) =>
      (await api.post(`/demandes-changement-etat/${id}/rejeter`, { commentaire })).data,
    onSuccess: invalidate,
  })
}

export function useCommentairesDemandeChangementEtat(demandeId: number | null) {
  return useQuery({
    queryKey: ['demandes-changement-etat', demandeId, 'commentaires'],
    queryFn: async () => {
      const { data } = await api.get<{ data: DemandeChangementEtatCommentaire[] }>(
        `/demandes-changement-etat/${demandeId}/commentaires`,
      )
      return data.data
    },
    enabled: demandeId !== null,
  })
}

export function useAjouterCommentaireDemandeChangementEtat() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ id, contenu }: { id: number; contenu: string }) =>
      (await api.post<DemandeChangementEtatCommentaire>(`/demandes-changement-etat/${id}/commentaires`, { contenu })).data,
    onSuccess: (_data, variables) => {
      qc.invalidateQueries({ queryKey: ['demandes-changement-etat', variables.id, 'commentaires'] })
      qc.invalidateQueries({ queryKey: ['notifications'] })
    },
  })
}
