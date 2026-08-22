import { Fragment, useState } from 'react'
import { useTranslation } from 'react-i18next'
import type { Utilisateur } from '@/lib/api/types'
import { Icons } from '@/components/icons'
import { useAjouterCommentaireUtilisateur, useHistoriqueUtilisateur } from './api'

interface HistoriqueModalProps {
  utilisateur: Utilisateur
  onClose: () => void
}

export function HistoriqueModal({ utilisateur, onClose }: HistoriqueModalProps) {
  const { t } = useTranslation()
  const { data: historiques, isLoading } = useHistoriqueUtilisateur(utilisateur.id)
  const ajouterCommentaire = useAjouterCommentaireUtilisateur(utilisateur.id)
  const [commentaire, setCommentaire] = useState('')
  const [detailId, setDetailId] = useState<number | null>(null)

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (!commentaire.trim()) return
    await ajouterCommentaire.mutateAsync(commentaire.trim())
    setCommentaire('')
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div className="panel flex max-h-[80vh] w-full max-w-3xl flex-col overflow-hidden">
        <div className="flex items-center justify-between border-b border-[var(--color-line)] p-4">
          <div>
            <h2 className="text-sm font-semibold">
              {t('administration.historique.title', { defaultValue: 'Historique' })} — {utilisateur.nomComplet}
            </h2>
            <p className="text-xs text-[var(--color-muted)]">{utilisateur.email}</p>
          </div>
          <button className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px]" onClick={onClose} aria-label="Fermer">
            <Icons.close size={16} />
          </button>
        </div>

        <div className="flex-1 overflow-y-auto p-4">
          {isLoading && (
            <p className="py-8 text-center text-sm text-[var(--color-faint)]">{t('common.loading')}</p>
          )}

          {!isLoading && (!historiques || historiques.length === 0) && (
            <p className="py-8 text-center text-sm text-[var(--color-muted)]">
              {t('administration.historique.empty', { defaultValue: 'Aucune action enregistrée pour cet utilisateur.' })}
            </p>
          )}

          {!isLoading && historiques && historiques.length > 0 && (
            <table className="data-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Action</th>
                  <th>Description</th>
                  <th>Équipement</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {historiques.map((h) => {
                  const open = detailId === h.id
                  return (
                    <Fragment key={h.id}>
                      <tr>
                        <td className="mono text-[11px] text-[var(--color-faint)] whitespace-nowrap">
                          {new Date(h.createdAt).toLocaleString('fr-FR')}
                        </td>
                        <td>
                          <span className="inline-block rounded bg-[var(--color-line)] px-2 py-0.5 text-[10px] uppercase text-[var(--color-muted)]">
                            {h.action === 'commentaire' ? 'Commentaire' : h.action}
                          </span>
                        </td>
                        <td className="text-[var(--color-muted)]">{h.description}</td>
                        <td className="text-[var(--color-muted)]">
                          {h.equipement?.nom ?? (h.action === 'commentaire' ? h.auteur : null) ?? '—'}
                        </td>
                        <td className="text-right">
                          {h.equipement && (
                            <button
                              type="button"
                              className="btn-ghost px-2 py-1 text-xs"
                              onClick={() => setDetailId(open ? null : h.id)}
                            >
                              {open ? 'Masquer' : 'Détail'}
                            </button>
                          )}
                        </td>
                      </tr>
                      {open && h.equipement && (
                        <tr>
                          <td colSpan={5} className="bg-[var(--color-brand-wash)]">
                            <div className="grid grid-cols-2 gap-x-6 gap-y-1.5 py-2 text-xs sm:grid-cols-3">
                              <DetailField label="Type" value={h.equipement.typeLabel} />
                              <DetailField label="Marque" value={h.equipement.marque} />
                              <DetailField label="Modèle" value={h.equipement.modele} />
                              <DetailField label="N° de série" value={h.equipement.numeroSerie} />
                              <DetailField label="État" value={h.equipement.etatLabel} />
                              <DetailField label="Localisation" value={h.equipement.localisation} />
                              <DetailField label="Adresse IP" value={h.equipement.adresseIP} />
                              <DetailField label="Adresse MAC" value={h.equipement.adresseMAC} />
                              <DetailField
                                label="Date d'acquisition"
                                value={h.equipement.dateAcquisition ? new Date(h.equipement.dateAcquisition).toLocaleDateString('fr-FR') : null}
                              />
                              <DetailField label="Technicien assigné" value={h.technicienAssigne} />
                              <DetailField label="Affecté par" value={h.auteur} />
                            </div>
                          </td>
                        </tr>
                      )}
                    </Fragment>
                  )
                })}
              </tbody>
            </table>
          )}
        </div>

        <form onSubmit={onSubmit} className="border-t border-[var(--color-line)] p-4">
          <label className="field-label">Ajouter un commentaire</label>
          <div className="mt-1 flex gap-2">
            <textarea
              className="input flex-1"
              rows={2}
              placeholder="Écrire un commentaire sur cet utilisateur..."
              value={commentaire}
              onChange={(e) => setCommentaire(e.target.value)}
            />
            <button type="submit" className="btn btn-primary self-end" disabled={ajouterCommentaire.isPending || !commentaire.trim()}>
              {ajouterCommentaire.isPending ? '...' : 'Envoyer'}
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}

function DetailField({ label, value }: { label: string; value: string | null | undefined }) {
  return (
    <div>
      <p className="text-[10px] uppercase text-[var(--color-faint)]">{label}</p>
      <p className="text-[var(--color-muted)]">{value ?? '—'}</p>
    </div>
  )
}
