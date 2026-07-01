import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { useNavigate } from 'react-router-dom'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { api } from '@/lib/api/client'
import type { DemandeInscription, Paginated } from '@/lib/api/types'
import { PageHeader } from '@/components/PageHeader'
import { StatusPill } from '@/components/StatusPill'

function useDemandesInscription(statut: string) {
  return useQuery({
    queryKey: ['demandes-inscription', statut],
    queryFn: async () => {
      const { data } = await api.get<Paginated<DemandeInscription>>('/demandes-inscription', {
        params: { statut: statut || undefined, per_page: 20 },
      })
      return data
    },
  })
}

function useApprouver() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) =>
      (await api.post(`/demandes-inscription/${id}/approuver`)).data,
    onSuccess: () => qc.invalidateQueries({ queryKey: ['demandes-inscription'] }),
  })
}

function useRejeter() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async (id: number) =>
      (await api.post(`/demandes-inscription/${id}/rejeter`)).data,
    onSuccess: () => qc.invalidateQueries({ queryKey: ['demandes-inscription'] }),
  })
}

const STATUT_COLORS: Record<string, string> = {
  EN_ATTENTE: 'warning',
  APPROUVEE: 'success',
  REJETEE: 'danger',
}

const STATUT_LABELS: Record<string, string> = {
  EN_ATTENTE: 'En attente',
  APPROUVEE: 'Approuvée',
  REJETEE: 'Rejetée',
}

const ROLE_LABELS: Record<string, string> = {
  ADMIN: 'Administrateur',
  TECHNICIEN: 'Technicien',
  EMPLOYE: 'Employé',
}

export function DemandesInscriptionPage() {
  const { i18n } = useTranslation()
  const navigate = useNavigate()
  const [statut, setStatut] = useState('')
  const { data, isLoading } = useDemandesInscription(statut)
  const approuver = useApprouver()
  const rejeter = useRejeter()

  const rows = data?.data ?? []

  return (
    <>
      <PageHeader
  eyebrow="Administration"
  title="Demandes d'inscription"
  subtitle="Validez ou rejetez les demandes d'accès soumises depuis la vitrine."
  actions={
    <button className="btn" onClick={() => navigate(-1)}>
      ← Retour
    </button>
  }
/>

      <section className="panel">
        <div className="flex flex-wrap items-center gap-3 border-b border-[var(--color-line)] p-3">
          <select
            className="input w-auto"
            value={statut}
            onChange={(e) => setStatut(e.target.value)}
          >
            <option value="">Tous les statuts</option>
            <option value="EN_ATTENTE">En attente</option>
            <option value="APPROUVEE">Approuvées</option>
            <option value="REJETEE">Rejetées</option>
          </select>
        </div>

        <div className="overflow-x-auto">
          <table className="data-table">
            <thead>
              <tr>
                <th>Nom complet</th>
                <th>Email</th>
                <th>Rôle souhaité</th>
                <th>Téléphone</th>
                <th>Département</th>
                <th>Message</th>
                <th>Date</th>
                <th>Statut</th>
                <th className="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((d) => (
                <tr key={d.id}>
                  <td className="font-medium">{d.prenom} {d.nom}</td>
                  <td className="mono text-[var(--color-muted)]">{d.email}</td>
                  <td>{ROLE_LABELS[d.role] ?? d.role}</td>
                  <td className="text-[var(--color-muted)]">{d.telephone ?? '—'}</td>
                  <td className="text-[var(--color-muted)]">{d.departement ?? '—'}</td>
                  <td>
                    <div className="max-w-xs truncate text-[11px] text-[var(--color-faint)]">
                      {d.message ?? '—'}
                    </div>
                  </td>
                  <td className="mono text-[11px] text-[var(--color-faint)]">
                    {new Date(d.created_at).toLocaleDateString(i18n.language, { dateStyle: 'short' })}
                  </td>
                  <td>
                    <StatusPill value={STATUT_COLORS[d.statut]} label={STATUT_LABELS[d.statut]} />
                  </td>
                  <td>
                    {d.statut === 'EN_ATTENTE' && (
                      <div className="flex items-center justify-end gap-2">
                        <button
                          className="btn btn-primary px-2.5 py-1 text-xs"
                          onClick={() => approuver.mutate(d.id)}
                          disabled={approuver.isPending}
                        >
                          Approuver
                        </button>
                        <button
                          className="btn px-2.5 py-1 text-xs"
                          onClick={() => rejeter.mutate(d.id)}
                          disabled={rejeter.isPending}
                          style={{ color: '#ff8983' }}
                        >
                          Rejeter
                        </button>
                      </div>
                    )}
                  </td>
                </tr>
              ))}
              {!isLoading && rows.length === 0 && (
                <tr>
                  <td colSpan={9} className="py-12 text-center text-[var(--color-muted)]">
                    Aucune demande trouvée.
                  </td>
                </tr>
              )}
              {isLoading && (
                <tr>
                  <td colSpan={9} className="py-12 text-center text-[var(--color-faint)]">
                    Chargement...
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </section>
    </>
  )
}