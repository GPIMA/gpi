import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { isAxiosError } from 'axios'
import { Modal } from '@/components/Modal'
import { useEnums } from '@/lib/api/enums'
import type { Utilisateur } from '@/lib/api/types'
import { useCreateUtilisateur, useUpdateUtilisateur, type UtilisateurInput } from './api'

function emptyForm(): UtilisateurInput {
  return { nom: '', prenom: '', email: '', password: '', role: 'EMPLOYE', telephone: '', specialite: '', departement: '' }
}

function fromUser(u: Utilisateur): UtilisateurInput {
  return {
    nom: u.nom, prenom: u.prenom, email: u.email, password: '', role: u.role,
    telephone: u.telephone ?? '', specialite: u.specialite ?? '', departement: u.departement ?? '',
  }
}

export function UtilisateurForm({ open, onClose, utilisateur }: { open: boolean; onClose: () => void; utilisateur: Utilisateur | null }) {
  const { t } = useTranslation()
  const { data: enums } = useEnums()
  const isEdit = !!utilisateur

  const [form, setForm] = useState<UtilisateurInput>(utilisateur ? fromUser(utilisateur) : emptyForm())
  const [errors, setErrors] = useState<Record<string, string[]>>({})

  const create = useCreateUtilisateur()
  const update = useUpdateUtilisateur(utilisateur?.id ?? 0)
  const busy = create.isPending || update.isPending

  function set<K extends keyof UtilisateurInput>(k: K, v: UtilisateurInput[K]) {
    setForm((f) => ({ ...f, [k]: v }))
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})
    const payload = { ...form }
    if (isEdit && !payload.password) delete payload.password
    try {
      if (isEdit) await update.mutateAsync(payload)
      else await create.mutateAsync(payload)
      onClose()
    } catch (err) {
      if (isAxiosError(err) && err.response?.status === 422) setErrors(err.response.data.errors ?? {})
    }
  }

  const err = (f: string) => errors[f]?.[0]

  return (
    <Modal open={open} onClose={onClose} title={isEdit ? t('administration.form.editTitle') : t('administration.form.createTitle')} width={560}>
      <form onSubmit={onSubmit} className="grid grid-cols-2 gap-4">
        <Field label={t('administration.form.prenom')} error={err('prenom')}>
          <input className="input" value={form.prenom} onChange={(e) => set('prenom', e.target.value)} required />
        </Field>
        <Field label={t('administration.form.nom')} error={err('nom')}>
          <input className="input" value={form.nom} onChange={(e) => set('nom', e.target.value)} required />
        </Field>

        <Field label={t('administration.form.email')} error={err('email')} className="col-span-2">
          <input type="email" className="input" value={form.email} onChange={(e) => set('email', e.target.value)} required />
        </Field>

        <Field label={isEdit ? t('administration.form.passwordEdit') : t('administration.form.password')} error={err('password')} className="col-span-2">
          <input type="password" className="input" value={form.password ?? ''} onChange={(e) => set('password', e.target.value)} required={!isEdit} autoComplete="new-password" />
        </Field>

        <Field label={t('administration.form.role')} error={err('role')}>
          <select className="input" value={form.role} onChange={(e) => set('role', e.target.value)} required>
            {enums?.roleUtilisateur.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
        </Field>
        <Field label={t('administration.form.telephone')} error={err('telephone')}>
          <input className="input" value={form.telephone ?? ''} onChange={(e) => set('telephone', e.target.value)} />
        </Field>

        {form.role === 'TECHNICIEN' && (
          <Field label={t('administration.form.specialite')} error={err('specialite')} className="col-span-2">
            <input className="input" value={form.specialite ?? ''} onChange={(e) => set('specialite', e.target.value)} />
          </Field>
        )}
        {form.role === 'EMPLOYE' && (
          <Field label={t('administration.form.departement')} error={err('departement')} className="col-span-2">
            <input className="input" value={form.departement ?? ''} onChange={(e) => set('departement', e.target.value)} />
          </Field>
        )}

        <div className="col-span-2 mt-2 flex justify-end gap-2">
          <button type="button" className="btn" onClick={onClose}>{t('administration.form.cancel')}</button>
          <button type="submit" className="btn btn-primary" disabled={busy}>
            {busy ? t('administration.form.saving') : t('administration.form.save')}
          </button>
        </div>
      </form>
    </Modal>
  )
}

function Field({ label, error, className, children }: { label: string; error?: string; className?: string; children: React.ReactNode }) {
  return (
    <div className={className}>
      <label className="field-label">{label}</label>
      {children}
      {error && <p className="mt-1 text-xs" style={{ color: '#ff8983' }}>{error}</p>}
    </div>
  )
}
