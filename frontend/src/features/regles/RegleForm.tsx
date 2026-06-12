import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { isAxiosError } from 'axios'
import { Modal } from '@/components/Modal'
import { useEnums } from '@/lib/api/enums'
import type { RegleAlerte } from '@/lib/api/types'
import { useCreateRegle, useUpdateRegle, type RegleInput } from './api'

const OPERATEURS = ['>', '>=', '<', '<=']
const CIBLES = ['cpu', 'ram', 'disque']

function emptyForm(): RegleInput {
  return { nom: '', metriqueCible: 'cpu', operateur: '>=', seuil: 85, severite: '', typeAlerte: '', actif: true }
}

function fromRegle(r: RegleAlerte): RegleInput {
  return {
    nom: r.nom, metriqueCible: r.metriqueCible, operateur: r.operateur,
    seuil: r.seuil, severite: r.severite, typeAlerte: r.typeAlerte, actif: r.actif,
  }
}

export function RegleForm({ open, onClose, regle }: { open: boolean; onClose: () => void; regle: RegleAlerte | null }) {
  const { t } = useTranslation()
  const { data: enums } = useEnums()
  const isEdit = !!regle

  const [form, setForm] = useState<RegleInput>(regle ? fromRegle(regle) : emptyForm())
  const [errors, setErrors] = useState<Record<string, string[]>>({})

  const create = useCreateRegle()
  const update = useUpdateRegle(regle?.id ?? 0)
  const busy = create.isPending || update.isPending

  function set<K extends keyof RegleInput>(k: K, v: RegleInput[K]) {
    setForm((f) => ({ ...f, [k]: v }))
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})
    try {
      if (isEdit) await update.mutateAsync(form)
      else await create.mutateAsync(form)
      onClose()
    } catch (err) {
      if (isAxiosError(err) && err.response?.status === 422) setErrors(err.response.data.errors ?? {})
    }
  }

  const err = (f: string) => errors[f]?.[0]

  return (
    <Modal open={open} onClose={onClose} title={isEdit ? t('regles.form.editTitle') : t('regles.form.createTitle')} width={520}>
      <form onSubmit={onSubmit} className="grid grid-cols-2 gap-4">
        <Field label={t('regles.form.nom')} error={err('nom')} className="col-span-2">
          <input className="input" value={form.nom} onChange={(e) => set('nom', e.target.value)} required />
        </Field>

        <Field label={t('regles.form.cible')} error={err('metriqueCible')}>
          <select className="input" value={form.metriqueCible} onChange={(e) => set('metriqueCible', e.target.value)}>
            {CIBLES.map((c) => <option key={c} value={c}>{t(`regles.targets.${c}`)}</option>)}
          </select>
        </Field>

        <div className="grid grid-cols-2 gap-2">
          <Field label={t('regles.form.operateur')} error={err('operateur')}>
            <select className="input" value={form.operateur} onChange={(e) => set('operateur', e.target.value)}>
              {OPERATEURS.map((o) => <option key={o} value={o}>{o}</option>)}
            </select>
          </Field>
          <Field label={t('regles.form.seuil')} error={err('seuil')}>
            <input type="number" min={0} max={100} className="input mono" value={form.seuil} onChange={(e) => set('seuil', Number(e.target.value))} required />
          </Field>
        </div>

        <Field label={t('regles.form.severite')} error={err('severite')}>
          <select className="input" value={form.severite} onChange={(e) => set('severite', e.target.value)} required>
            <option value="" disabled>—</option>
            {enums?.severite.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
        </Field>

        <Field label={t('regles.form.typeAlerte')} error={err('typeAlerte')}>
          <select className="input" value={form.typeAlerte} onChange={(e) => set('typeAlerte', e.target.value)} required>
            <option value="" disabled>—</option>
            {enums?.typeAlerte.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
        </Field>

        <label className="col-span-2 flex items-center gap-2 text-sm text-[var(--color-muted)]">
          <input type="checkbox" checked={form.actif} onChange={(e) => set('actif', e.target.checked)} />
          {t('regles.form.actif')}
        </label>

        <div className="col-span-2 mt-2 flex justify-end gap-2">
          <button type="button" className="btn" onClick={onClose}>{t('regles.form.cancel')}</button>
          <button type="submit" className="btn btn-primary" disabled={busy}>
            {busy ? t('regles.form.saving') : t('regles.form.save')}
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
