import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { isAxiosError } from 'axios'
import { Modal } from '@/components/Modal'
import { useEnums } from '@/lib/api/enums'
import type { Utilisateur } from '@/lib/api/types'
import { useCreateUtilisateur, useUpdateUtilisateur, type UtilisateurInput } from './api'

// Fixed list of sites — kept in sync with the equipment form's site list.
const SITES = ['Rabat', 'Casablanca', 'Tanger']

// Fixed list of departments an employee can belong to.
const DEPARTEMENTS = ['RH', 'PROD', 'Direction']

function emptyForm(): UtilisateurInput {
  return {
    nom: '', prenom: '', email: '', password: '', role: 'EMPLOYE',
    telephone: '', departement: '', localisation: '',
  }
}

function fromUser(u: Utilisateur): UtilisateurInput {
  return {
    nom: u.nom, prenom: u.prenom, email: u.email, password: '', role: u.role,
    telephone: u.telephone ?? '', departement: u.departement ?? '',
    localisation: u.localisation ?? '',
  }
}

const PASSWORD_RULES = [
  { key: 'minLength', label: 'Au moins 8 caractères', test: (v: string) => v.length >= 8 },
  { key: 'upper', label: 'Une majuscule', test: (v: string) => /[A-Z]/.test(v) },
  { key: 'lower', label: 'Une minuscule', test: (v: string) => /[a-z]/.test(v) },
  { key: 'digit', label: 'Un chiffre', test: (v: string) => /\d/.test(v) },
  { key: 'special', label: 'Un caractère spécial', test: (v: string) => /[^A-Za-z0-9]/.test(v) },
]

function isPasswordValid(v: string) {
  return PASSWORD_RULES.every((r) => r.test(v))
}

export function UtilisateurForm({ open, onClose, utilisateur }: { open: boolean; onClose: () => void; utilisateur: Utilisateur | null }) {
  const { t } = useTranslation()
  const { data: enums } = useEnums()
  const isEdit = !!utilisateur

  const [form, setForm] = useState<UtilisateurInput>(utilisateur ? fromUser(utilisateur) : emptyForm())
  const [passwordConfirm, setPasswordConfirm] = useState('')
  const [passwordFocused, setPasswordFocused] = useState(false)
  const [errors, setErrors] = useState<Record<string, string[]>>({})
  const [localError, setLocalError] = useState<string | null>(null)

  const create = useCreateUtilisateur()
  const update = useUpdateUtilisateur(utilisateur?.id ?? 0)
  const busy = create.isPending || update.isPending

  function set<K extends keyof UtilisateurInput>(k: K, v: UtilisateurInput[K]) {
    setForm((f) => ({ ...f, [k]: v }))
  }

  const passwordValue = form.password ?? ''
  const passwordTouched = passwordValue.length > 0
  const passwordMismatch = passwordConfirm.length > 0 && passwordConfirm !== passwordValue

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})
    setLocalError(null)
if (/\d/.test(form.nom) || /\d/.test(form.prenom)) {
      setLocalError("Le nom et le prénom ne doivent pas contenir de chiffres.")
      return
    }
    if ((!isEdit || passwordTouched) && !isPasswordValid(passwordValue)) {
      setLocalError("Le mot de passe ne respecte pas tous les critères requis.")
      return
    }
    if ((!isEdit || passwordTouched) && passwordValue !== passwordConfirm) {
      setLocalError("Les deux mots de passe ne correspondent pas.")
      return
    }
    if (!form.telephone || !/^\d{10}$/.test(form.telephone)) {
      setLocalError("Le numéro de téléphone doit contenir exactement 10 chiffres.")
      return
    }

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
          <input
            className="input"
            value={form.prenom}
            onChange={(e) => set('prenom', e.target.value)}
            pattern="^[^0-9]+$"
            title="Le prénom ne doit pas contenir de chiffres."
            required
          />
        </Field>
        <Field label={t('administration.form.nom')} error={err('nom')}>
          <input
            className="input"
            value={form.nom}
            onChange={(e) => set('nom', e.target.value)}
            pattern="^[^0-9]+$"
            title="Le nom ne doit pas contenir de chiffres."
            required
          />
        </Field>

        <Field label={t('administration.form.email')} error={err('email')} className="col-span-2">
          <input type="email" className="input" value={form.email} onChange={(e) => set('email', e.target.value)} required />
        </Field>

        <Field label={isEdit ? t('administration.form.passwordEdit') : t('administration.form.password')} error={err('password')} className="col-span-2">
          <input
            type="password"
            className="input"
            value={passwordValue}
            onChange={(e) => set('password', e.target.value)}
            onFocus={() => setPasswordFocused(true)}
            required={!isEdit}
            autoComplete="new-password"
          />
          {(passwordFocused || passwordTouched) && (
            <ul className="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
              {PASSWORD_RULES.map((rule) => {
                const ok = rule.test(passwordValue)
                return (
                  <li key={rule.key} className="flex items-center gap-1.5" style={{ color: ok ? '#22c55e' : '#8a94a3' }}>
                    <span>{ok ? '✓' : '○'}</span>
                    <span>{rule.label}</span>
                  </li>
                )
              })}
            </ul>
          )}
        </Field>

        <Field
          label="Confirmer le mot de passe"
          error={passwordMismatch ? "Les mots de passe ne correspondent pas." : undefined}
          className="col-span-2"
        >
          <input
            type="password"
            className="input"
            value={passwordConfirm}
            onChange={(e) => setPasswordConfirm(e.target.value)}
            required={!isEdit || passwordTouched}
            autoComplete="new-password"
          />
        </Field>

        <Field label={t('administration.form.role')} error={err('role')}>
          <select className="input" value={form.role} onChange={(e) => set('role', e.target.value)} required>
            {enums?.roleUtilisateur.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
        </Field>
        <Field label={t('administration.form.telephone')} error={err('telephone')}>
          <input
            type="tel"
            className="input"
            value={form.telephone ?? ''}
            onChange={(e) => set('telephone', e.target.value.replace(/\D/g, '').slice(0, 10))}
            pattern="^\d{10}$"
            title="Le numéro doit contenir exactement 10 chiffres."
            maxLength={10}
            required
          />
        </Field>

        <Field label="Localisation" error={err('localisation')} className="col-span-2">
          <select
            className="input"
            value={form.localisation ?? ''}
            onChange={(e) => set('localisation', e.target.value)}
            required
          >
            <option value="" disabled>—</option>
            {SITES.map((site) => (
              <option key={site} value={site}>{site}</option>
            ))}
          </select>
        </Field>

        {form.role === 'EMPLOYE' && (
          <Field label={t('administration.form.departement')} error={err('departement')} className="col-span-2">
            <select
              className="input"
              value={form.departement ?? ''}
              onChange={(e) => set('departement', e.target.value)}
              required
            >
              <option value="" disabled>—</option>
              {DEPARTEMENTS.map((dep) => (
                <option key={dep} value={dep}>{dep}</option>
              ))}
            </select>
          </Field>
        )}

        {localError && (
          <p className="col-span-2 text-sm" style={{ color: '#ff8983' }}>{localError}</p>
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