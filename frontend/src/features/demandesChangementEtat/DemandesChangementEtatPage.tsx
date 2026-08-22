import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '@/features/auth/auth-context'
import { PageHeader } from '@/components/PageHeader'
import { StatusPill } from '@/components/StatusPill'
import { Modal } from '@/components/Modal'
import type { DemandeChangementEtat } from '@/lib/api/types'
import {
  useAjouterCommentaireDemandeChangementEtat,
  useApprouverDemandeChangementEtat,
  useCommentairesDemandeChangementEtat,
  useDemandesChangementEtat,
  useRejeterDemandeChangementEtat,
  type DemandeChangementEtatFilters,
} from './api'

export function DemandesChangementEtatPage() {
  const navigate = useNavigate()
  const { user } = useAuth()
  const isAdmin = user?.role === 'SUPER_ADMIN' || user?.role === 'ADMIN'

  const [filters, setFilters] = useState<DemandeChangementEtatFilters>({ page: 1 })
  const { data, isLoading } = useDemandesChangementEtat(filters)
  const approuver = useApprouverDemandeChangementEtat()
  const rejeter = useRejeterDemandeChangementEtat()
  const [rejeting, setRejeting] = useState<DemandeChangementEtat | null>(null)
  const [commentaire, setCommentaire] = useState('')
  const [viewing, setViewing] = useState<DemandeChangementEtat | null>(null)

  const rows = data?.data ?? []
  const meta = data?.meta

  async function confirmRejeter() {
    if (!rejeting) return
    await rejeter.mutateAsync({ id: rejeting.id, commentaire: commentaire.trim() || undefined })
    setRejeting(null)
    setCommentaire('')
  }

  return (
    <>
      <PageHeader
        eyebrow="Administration"
        title="Demandes de changement de statut"
        subtitle="Un technicien qui change le statut d'un équipement (hors résolution d'incident) doit obtenir l'accord d'un Admin ou d'un Super Admin."
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
            value={filters.statut ?? ''}
            onChange={(e) => setFilters((f) => ({ ...f, statut: e.target.value, page: 1 }))}
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
                <th>Équipement</th>
                <th>Site</th>
                <th>Demandeur</th>
                <th>Changement demandé</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Traité par</th>
                <th></th>
                {isAdmin && <th className="text-right">Actions</th>}
              </tr>
            </thead>
            <tbody>
              {rows.map((d) => (
                <tr key={d.id}>
                  <td className="font-medium">{d.equipement?.nom ?? '—'}</td>
                  <td className="text-[var(--color-muted)]">{d.equipement?.localisation ?? '—'}</td>
                  <td className="text-[var(--color-muted)]">{d.demandeur?.nomComplet ?? '—'}</td>
                  <td>
                    <span className="mono text-xs text-[var(--color-muted)]">
                      {d.etatActuelLabel} → <strong className="text-[var(--color-ink)]">{d.etatDemandeLabel}</strong>
                    </span>
                  </td>
                  <td className="mono text-[11px] text-[var(--color-faint)]">
                    {new Date(d.createdAt).toLocaleDateString('fr-FR', { dateStyle: 'short' })}
                  </td>
                  <td>
                    <StatusPill value={d.statut} label={d.statutLabel} />
                  </td>
                  <td className="text-[var(--color-muted)]">{d.traitePar ?? '—'}</td>
                  <td>
                    <button className="btn-ghost px-2 py-1 text-xs" onClick={() => setViewing(d)}>
                      Consulter
                    </button>
                  </td>
                  {isAdmin && (
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
                            onClick={() => setRejeting(d)}
                            disabled={rejeter.isPending}
                            style={{ color: '#ff8983' }}
                          >
                            Rejeter
                          </button>
                        </div>
                      )}
                    </td>
                  )}
                </tr>
              ))}
              {!isLoading && rows.length === 0 && (
                <tr>
                  <td colSpan={isAdmin ? 9 : 8} className="py-12 text-center text-[var(--color-muted)]">
                    Aucune demande trouvée.
                  </td>
                </tr>
              )}
              {isLoading && (
                <tr>
                  <td colSpan={isAdmin ? 9 : 8} className="py-12 text-center text-[var(--color-faint)]">
                    Chargement...
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {meta && meta.total > 0 && (
          <div className="flex items-center justify-between border-t border-[var(--color-line)] px-4 py-3">
            <span className="mono text-[11px] text-[var(--color-faint)]">{meta.from}–{meta.to} / {meta.total}</span>
            <div className="flex items-center gap-1">
              <button
                className="btn-ghost px-2 py-1 text-xs disabled:opacity-40"
                disabled={meta.current_page <= 1}
                onClick={() => setFilters((f) => ({ ...f, page: (f.page ?? 1) - 1 }))}
              >
                Précédent
              </button>
              <span className="mono px-2 text-xs text-[var(--color-muted)]">{meta.current_page} / {meta.last_page}</span>
              <button
                className="btn-ghost px-2 py-1 text-xs disabled:opacity-40"
                disabled={meta.current_page >= meta.last_page}
                onClick={() => setFilters((f) => ({ ...f, page: (f.page ?? 1) + 1 }))}
              >
                Suivant
              </button>
            </div>
          </div>
        )}
      </section>

      <Modal open={!!rejeting} onClose={() => setRejeting(null)} title="Rejeter la demande" width={440}>
        <p className="text-sm text-[var(--color-muted)]">
          {rejeting && (
            <>
              Rejeter le passage de « {rejeting.equipement?.nom} » à « {rejeting.etatDemandeLabel} » ?
            </>
          )}
        </p>
        <label className="field-label mt-4 block">Motif (optionnel)</label>
        <textarea
          className="input mt-1 w-full"
          rows={3}
          placeholder="Expliquer pourquoi cette demande est rejetée..."
          value={commentaire}
          onChange={(e) => setCommentaire(e.target.value)}
        />
        <div className="mt-4 flex justify-end gap-2">
          <button className="btn" onClick={() => setRejeting(null)}>Annuler</button>
          <button
            className="btn"
            onClick={confirmRejeter}
            disabled={rejeter.isPending}
            style={{ background: 'var(--color-down)', borderColor: 'var(--color-down)', color: '#fff', fontWeight: 600 }}
          >
            Rejeter
          </button>
        </div>
      </Modal>

      {viewing && <DemandeDetailModal demande={viewing} onClose={() => setViewing(null)} />}
    </>
  )
}

function DetailRow({ label, value }: { label: string; value: string | null | undefined }) {
  return (
    <div>
      <p className="text-[10px] uppercase text-[var(--color-faint)]">{label}</p>
      <p className="text-[var(--color-ink)]">{value || '—'}</p>
    </div>
  )
}

/**
 * Détail d'une demande + petite discussion libre entre le demandeur et
 * l'Admin/Super Admin qui la traite (même mécanique que la discussion d'un
 * incident) — accessible aux deux côtés, pas seulement au technicien.
 */
function DemandeDetailModal({ demande, onClose }: { demande: DemandeChangementEtat; onClose: () => void }) {
  const { user } = useAuth()
  const { data: commentaires } = useCommentairesDemandeChangementEtat(demande.id)
  const ajouterCommentaire = useAjouterCommentaireDemandeChangementEtat()
  const [message, setMessage] = useState('')

  async function submitCommentaire(e: React.FormEvent) {
    e.preventDefault()
    if (!message.trim()) return
    await ajouterCommentaire.mutateAsync({ id: demande.id, contenu: message.trim() })
    setMessage('')
  }

  return (
    <Modal open onClose={onClose} title="Détail de la demande" width={480}>
      <div className="space-y-3 text-sm">
        <DetailRow label="Équipement" value={demande.equipement?.nom} />
        <DetailRow label="Site" value={demande.equipement?.localisation} />
        <DetailRow label="Demandeur" value={demande.demandeur?.nomComplet} />
        <DetailRow
          label="Changement demandé"
          value={`${demande.etatActuelLabel} → ${demande.etatDemandeLabel}`}
        />
        <DetailRow
          label="Date de la demande"
          value={new Date(demande.createdAt).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' })}
        />
        <div>
          <p className="text-[10px] uppercase text-[var(--color-faint)]">Statut</p>
          <StatusPill value={demande.statut} label={demande.statutLabel} />
        </div>
        {demande.statut !== 'EN_ATTENTE' && (
          <>
            <DetailRow label="Traité par" value={demande.traitePar ?? '—'} />
            <DetailRow
              label="Traité le"
              value={
                demande.traiteLe
                  ? new Date(demande.traiteLe).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' })
                  : '—'
              }
            />
            <div>
              <p className="text-[10px] uppercase text-[var(--color-faint)]">
                {demande.statut === 'REJETEE' ? "Message de l'admin (motif du rejet)" : "Message de l'admin"}
              </p>
              <p className="mt-0.5 whitespace-pre-wrap text-[var(--color-ink)]">
                {demande.commentaireTraitement || 'Aucun message laissé.'}
              </p>
            </div>
          </>
        )}
        {demande.statut === 'EN_ATTENTE' && (
          <p className="text-xs text-[var(--color-faint)]">
            En attente d'approbation — aucun message pour le moment.
          </p>
        )}

        <div className="border-t border-[var(--color-line)] pt-3">
          <p className="field-label mb-2">Commentaires</p>
          <div className="max-h-56 space-y-2 overflow-y-auto">
            {commentaires?.length ? (
              commentaires.map((c) => (
                <div
                  key={c.id}
                  className={`rounded-md p-2 text-[13px] ${c.auteurId === user?.id ? 'ml-8 bg-[var(--color-brand)]/10' : 'mr-8 bg-[var(--color-line)]/40'}`}
                >
                  <p className="mb-0.5 text-[11px] font-medium text-[var(--color-muted)]">{c.auteur ?? '—'}</p>
                  <p>{c.contenu}</p>
                  <p className="mono mt-1 text-[10px] text-[var(--color-faint)]">
                    {new Date(c.createdAt).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' })}
                  </p>
                </div>
              ))
            ) : (
              <p className="text-[13px] text-[var(--color-faint)]">Aucun commentaire pour le moment.</p>
            )}
          </div>
          <form onSubmit={submitCommentaire} className="mt-2 flex gap-2">
            <input
              className="input flex-1"
              placeholder="Écrire un commentaire..."
              value={message}
              onChange={(e) => setMessage(e.target.value)}
            />
            <button type="submit" className="btn btn-primary px-3" disabled={ajouterCommentaire.isPending || !message.trim()}>
              {ajouterCommentaire.isPending ? 'Envoi...' : 'Envoyer'}
            </button>
          </form>
        </div>

        <div className="flex justify-end pt-2">
          <button type="button" className="btn" onClick={onClose}>Fermer</button>
        </div>
      </div>
    </Modal>
  )
}
