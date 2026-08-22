import { useRef, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { isAxiosError } from 'axios'
import { Modal } from '@/components/Modal'
import { SearchableSelect } from '@/components/SearchableSelect'
import { useEnums } from '@/lib/api/enums'
import { useAuth } from '@/features/auth/auth-context'
import { useEmployes, useEquipements } from '@/features/equipements/api'
import { useCreateIncident, type IncidentInput } from './api'

const MAX_FICHIERS = 5
const MAX_TAILLE_OCTETS = 5 * 1024 * 1024 // 5 Mo

export function IncidentForm({ open, onClose }: { open: boolean; onClose: () => void }) {
  const { t } = useTranslation()
  const { user } = useAuth()
  const isEmploye = user?.role === 'EMPLOYE'
  const { data: enums } = useEnums()
  const create = useCreateIncident()
  const fileInputRef = useRef<HTMLInputElement>(null)
  const [fileError, setFileError] = useState<string | null>(null)

  // Un Employé déclare toujours pour lui-même (l'API scope automatiquement
  // son parc). Un Admin/Technicien/Super Admin déclare pour le compte d'un
  // utilisateur choisi ci-dessous — la liste des équipements ne se peuple
  // qu'une fois cet utilisateur sélectionné.
  const [utilisateurId, setUtilisateurId] = useState<string>('')
  const { data: utilisateurs } = useEmployes({ enabled: !isEmploye })

  const { data: equipementsResp } = useEquipements(
    { assigneA: !isEmploye && utilisateurId ? Number(utilisateurId) : undefined, page: 1 },
    { enabled: open && (isEmploye || !!utilisateurId) },
  )
  const equipements = equipementsResp?.data ?? []

  const [form, setForm] = useState<IncidentInput>({ titre: '', description: '', equipementId: 0, priorite: 'MOYENNE', pieceJointes: [] })
  const [errors, setErrors] = useState<Record<string, string[]>>({})

  function set<K extends keyof IncidentInput>(k: K, v: IncidentInput[K]) {
    setForm((f) => ({ ...f, [k]: v }))
  }

  function onUtilisateurChange(value: string) {
    setUtilisateurId(value)
    // L'équipement choisi ne concerne plus forcément le nouvel utilisateur.
    set('equipementId', 0)
  }

  function onFileChange(e: React.ChangeEvent<HTMLInputElement>) {
    const nouveaux = Array.from(e.target.files ?? [])
    e.target.value = ''
    if (nouveaux.length === 0) return

    const existants = form.pieceJointes ?? []

    const tropGros = nouveaux.filter((f) => f.size > MAX_TAILLE_OCTETS)
    if (tropGros.length > 0) {
      setFileError(`Chaque fichier ne doit pas dépasser 5 Mo (${tropGros.map((f) => f.name).join(', ')}).`)
      return
    }

    if (existants.length + nouveaux.length > MAX_FICHIERS) {
      setFileError(`Vous pouvez joindre ${MAX_FICHIERS} fichiers maximum.`)
      return
    }

    setFileError(null)
    setForm((f) => ({ ...f, pieceJointes: [...existants, ...nouveaux] }))
  }

  function removeFile(index: number) {
    setFileError(null)
    setForm((f) => ({ ...f, pieceJointes: (f.pieceJointes ?? []).filter((_, i) => i !== index) }))
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})
    try {
      await create.mutateAsync({
        ...form,
        utilisateurId: !isEmploye && utilisateurId ? Number(utilisateurId) : undefined,
      })
      onClose()
    } catch (err) {
      if (isAxiosError(err) && err.response?.status === 422) setErrors(err.response.data.errors ?? {})
    }
  }

  const err = (f: string) => errors[f]?.[0]

  return (
    <Modal open={open} onClose={onClose} title={t('incidents.form.title')} width={540}>
      <form onSubmit={onSubmit} className="space-y-4">
        <div>
          <label className="field-label">{t('incidents.form.titre')}</label>
          <input className="input" value={form.titre} onChange={(e) => set('titre', e.target.value)} required />
          {err('titre') && <p className="mt-1 text-xs" style={{ color: '#ff8983' }}>{err('titre')}</p>}
        </div>

        <div>
          <label className="field-label">{t('incidents.form.description')}</label>
          <textarea className="input" rows={4} value={form.description} onChange={(e) => set('description', e.target.value)} required />
          {err('description') && <p className="mt-1 text-xs" style={{ color: '#ff8983' }}>{err('description')}</p>}
        </div>

        {!isEmploye && (
          <div>
            <label className="field-label">{t('incidents.form.utilisateurConcerne')}</label>
            <SearchableSelect
              value={utilisateurId}
              onChange={onUtilisateurChange}
              placeholder={t('incidents.form.utilisateurSearchPlaceholder')}
              options={(utilisateurs ?? []).map((u) => ({ value: String(u.id), label: `${u.nomComplet} — ${u.roleLabel}` }))}
            />
            {err('utilisateurId') && <p className="mt-1 text-xs" style={{ color: '#ff8983' }}>{err('utilisateurId')}</p>}
          </div>
        )}

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="field-label">{t('incidents.form.equipement')}</label>
            <select
              className="input"
              value={form.equipementId || ''}
              onChange={(e) => set('equipementId', Number(e.target.value))}
              disabled={!isEmploye && !utilisateurId}
              required
            >
              <option value="" disabled>—</option>
              {equipements.map((e) => <option key={e.id} value={e.id}>{e.nom}</option>)}
            </select>
            {!isEmploye && !utilisateurId ? (
              <p className="mt-1 text-xs text-[var(--color-faint)]">{t('incidents.form.equipementChoisirUtilisateurDabord')}</p>
            ) : (
              err('equipementId') && <p className="mt-1 text-xs" style={{ color: '#ff8983' }}>{err('equipementId')}</p>
            )}
          </div>
          <div>
            <label className="field-label">{t('incidents.form.priorite')}</label>
            <select className="input" value={form.priorite} onChange={(e) => set('priorite', e.target.value)} required>
              {enums?.severite.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
            </select>
          </div>
        </div>

        <div>
          <label className="field-label">Pièces jointes</label>
          <input
            ref={fileInputRef}
            type="file"
            multiple
            className="hidden"
            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx"
            onChange={onFileChange}
          />
          <div className="flex items-center gap-3">
            <button
              type="button"
              className="btn flex items-center gap-2"
              onClick={() => fileInputRef.current?.click()}
              disabled={(form.pieceJointes?.length ?? 0) >= 5}
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M21.44 11.05l-9.19 9.19a5 5 0 0 1-7.07-7.07l9.19-9.19a3.5 3.5 0 0 1 4.95 4.95l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
              </svg>
              Joindre des fichiers
            </button>
          </div>
          {(form.pieceJointes?.length ?? 0) > 0 && (
            <ul className="mt-2 space-y-1">
              {form.pieceJointes!.map((f, i) => (
                <li key={i} className="flex items-center gap-2 text-sm text-[var(--color-muted)]">
                  {f.name}
                  <button
                    type="button"
                    className="text-[var(--color-faint)] hover:text-[var(--color-ink)]"
                    onClick={() => removeFile(i)}
                  >
                    ✕
                  </button>
                </li>
              ))}
            </ul>
          )}
          <p className="mt-1 text-xs text-[var(--color-faint)]">JPG, PNG, PDF, DOC, DOCX ou XLSX — 5 Mo max chacun, 5 fichiers max.</p>
          {fileError && <p className="mt-1 text-xs" style={{ color: '#ff8983' }}>{fileError}</p>}
          {err('pieceJointes') && <p className="mt-1 text-xs" style={{ color: '#ff8983' }}>{err('pieceJointes')}</p>}
        </div>

        <div className="flex justify-end gap-2 pt-2">
          <button type="button" className="btn" onClick={onClose}>{t('incidents.form.cancel')}</button>
          <button type="submit" className="btn btn-primary" disabled={create.isPending}>
            {create.isPending ? t('incidents.form.submitting') : t('incidents.form.submit')}
          </button>
        </div>
      </form>
    </Modal>
  )
}