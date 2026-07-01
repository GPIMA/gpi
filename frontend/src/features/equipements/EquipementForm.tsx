import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { isAxiosError } from 'axios'
import { Modal } from '@/components/Modal'
import { useEnums } from '@/lib/api/enums'
import type { Equipement } from '@/lib/api/types'
import { useCreateEquipement, useUpdateEquipement, useEmployes, type EquipementInput } from './api'

type Errors = Record<string, string[]>

function emptyForm(): EquipementInput {
  return {
    nom: '',
    type: '',
    marque: '',
    modele: '',
    numeroSerie: '',
    adresseIP: '',
    adresseMAC: '',
    etat: '',
    localisation: '',
    dateAcquisition: '',
    employeId: null,
  }
}

function fromEquipement(e: Equipement): EquipementInput {
  return {
    nom: e.nom,
    type: e.type,
    marque: e.marque ?? '',
    modele: e.modele ?? '',
    numeroSerie: e.numeroSerie ?? '',
    adresseIP: e.adresseIP ?? '',
    adresseMAC: e.adresseMAC ?? '',
    etat: e.etat,
    localisation: e.localisation ?? '',
    dateAcquisition: e.dateAcquisition ?? '',
    employeId: e.affectation?.employeId ?? null,
  }
}

export function EquipementForm({
  open,
  onClose,
  equipement,
}: {
  open: boolean
  onClose: () => void
  equipement: Equipement | null
}) {
  const { t } = useTranslation()
  const { data: enums } = useEnums()
  const { data: employes } = useEmployes()
  const isEdit = !!equipement

  const [form, setForm] = useState<EquipementInput>(
    equipement ? fromEquipement(equipement) : emptyForm(),
  )
  const [errors, setErrors] = useState<Errors>({})

  const create = useCreateEquipement()
  const update = useUpdateEquipement(equipement?.id ?? 0)
  const busy = create.isPending || update.isPending

  function set<K extends keyof EquipementInput>(key: K, value: EquipementInput[K]) {
    setForm((f) => ({ ...f, [key]: value }))
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})
    // Strip empty strings so optional fields are sent as null/omitted.
    // employeId is kept even when null, so the backend can clear an
    // existing affectation when the dropdown is reset to "—".
    const payload = Object.fromEntries(
      Object.entries(form).filter(([key, v]) => key === 'employeId' || v !== ''),
    ) as EquipementInput
    try {
      if (isEdit) await update.mutateAsync(payload)
      else await create.mutateAsync(payload)
      onClose()
    } catch (err) {
      if (isAxiosError(err) && err.response?.status === 422) {
        setErrors(err.response.data.errors ?? {})
      }
    }
  }

  const err = (field: string) => errors[field]?.[0]

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={isEdit ? t('equipements.form.editTitle') : t('equipements.form.createTitle')}
      width={560}
    >
      <form onSubmit={onSubmit} className="grid grid-cols-2 gap-4">
        <Field label={t('equipements.form.nom')} error={err('nom')} className="col-span-2">
          <input className="input" value={form.nom} onChange={(e) => set('nom', e.target.value)} required />
        </Field>

        <Field label={t('equipements.form.type')} error={err('type')}>
          <select className="input" value={form.type} onChange={(e) => set('type', e.target.value)} required>
            <option value="" disabled>—</option>
            {enums?.typeEquipement.map((o) => (
              <option key={o.value} value={o.value}>{o.label}</option>
            ))}
          </select>
        </Field>

        <Field label={t('equipements.form.etat')} error={err('etat')}>
          <select className="input" value={form.etat} onChange={(e) => set('etat', e.target.value)} required>
            <option value="" disabled>—</option>
            {enums?.etatEquipement.map((o) => (
              <option key={o.value} value={o.value}>{o.label}</option>
            ))}
          </select>
        </Field>

        <Field label={t('equipements.form.marque')} error={err('marque')}>
          <input className="input" value={form.marque ?? ''} onChange={(e) => set('marque', e.target.value)} />
        </Field>

        <Field label={t('equipements.form.modele')} error={err('modele')}>
          <input className="input" value={form.modele ?? ''} onChange={(e) => set('modele', e.target.value)} />
        </Field>

        <Field label={t('equipements.form.numeroSerie')} error={err('numeroSerie')}>
          <input className="input mono" value={form.numeroSerie ?? ''} onChange={(e) => set('numeroSerie', e.target.value)} />
        </Field>

        <Field label={t('equipements.form.affecteA')} error={err('employeId')}>
          <select
            className="input"
            value={form.employeId ?? ''}
            onChange={(e) => set('employeId', e.target.value ? Number(e.target.value) : null)}
          >
            <option value="">—</option>
            {employes?.map((emp) => (
              <option key={emp.id} value={emp.id}>{emp.nomComplet}</option>
            ))}
          </select>
        </Field>

        <Field label={t('equipements.form.ip')} error={err('adresseIP')}>
          <input className="input mono" placeholder="192.168.1.10" value={form.adresseIP ?? ''} onChange={(e) => set('adresseIP', e.target.value)} />
        </Field>

        <Field label={t('equipements.form.mac')} error={err('adresseMAC')}>
          <input className="input mono" placeholder="AA:BB:CC:DD:EE:FF" value={form.adresseMAC ?? ''} onChange={(e) => set('adresseMAC', e.target.value)} />
        </Field>

        <Field label={t('equipements.form.localisation')} error={err('localisation')}>
          <input className="input" value={form.localisation ?? ''} onChange={(e) => set('localisation', e.target.value)} />
        </Field>

        <Field label={t('equipements.form.dateAcquisition')} error={err('dateAcquisition')}>
          <input type="date" className="input" value={form.dateAcquisition ?? ''} onChange={(e) => set('dateAcquisition', e.target.value)} />
        </Field>

        <div className="col-span-2 mt-2 flex justify-end gap-2">
          <button type="button" className="btn" onClick={onClose}>
            {t('equipements.form.cancel')}
          </button>
          <button type="submit" className="btn btn-primary" disabled={busy}>
            {busy ? t('equipements.form.saving') : t('equipements.form.save')}
          </button>
        </div>
      </form>
    </Modal>
  )
}

function Field({
  label,
  error,
  className,
  children,
}: {
  label: string
  error?: string
  className?: string
  children: React.ReactNode
}) {
  return (
    <div className={className}>
      <label className="field-label">{label}</label>
      {children}
      {error && <p className="mt-1 text-xs" style={{ color: '#ff8983' }}>{error}</p>}
    </div>
  )
}