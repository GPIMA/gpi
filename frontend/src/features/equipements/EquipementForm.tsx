import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { isAxiosError } from 'axios'
import { Modal } from '@/components/Modal'
import { SearchableSelect } from '@/components/SearchableSelect'
import { useAuth } from '@/features/auth/auth-context'
import { useEnums } from '@/lib/api/enums'
import type { Equipement } from '@/lib/api/types'
import { useCreateEquipement, useUpdateEquipement, useEmployes, type EquipementInput } from './api'

type Errors = Record<string, string[]>

// Fixed list of sites — equipment is always attached to one of these locations.
const SITES = ['Rabat', 'Casablanca', 'Tanger']

// A newly created asset always starts offline; the field is locked so this
// can't be changed from the creation form.
const ETAT_INITIAL = 'HORS_LIGNE'

// Peripherals with no network identity of their own — IP/MAC stay optional
// for these (mirrors StoreEquipementRequest::TYPES_SANS_RESEAU on the backend).
const TYPES_SANS_RESEAU = ['SOURIS', 'CLAVIER', 'ECRAN', 'SOCLE']

function emptyForm(): EquipementInput {
  return {
    nom: '',
    type: '',
    marque: '',
    modele: '',
    numeroSerie: '',
    adresseIP: '',
    adresseMAC: '',
    etat: ETAT_INITIAL,
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
  affecterOnly = false,
}: {
  open: boolean
  onClose: () => void
  equipement: Equipement | null
  affecterOnly?: boolean
}) {
  const { t } = useTranslation()
  const { user } = useAuth()
  const { data: enums } = useEnums()
  const { data: employes } = useEmployes()
  const isEdit = !!equipement
  const isTechnicien = user?.role === 'TECHNICIEN'

  const [form, setForm] = useState<EquipementInput>(
    equipement ? fromEquipement(equipement) : emptyForm(),
  )
  const [errors, setErrors] = useState<Errors>({})
  const [forcerAffectation, setForcerAffectation] = useState(false)
const [warningAffectation, setWarningAffectation] = useState<string | null>(null)

  const fieldDisabled = affecterOnly
  const reseauRequis = !TYPES_SANS_RESEAU.includes(form.type)

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
    const payload = {
  ...Object.fromEntries(
    Object.entries(form).filter(([key, v]) => key === 'employeId' || v !== ''),
  ),
  forcerAffectation,
} as EquipementInput
    try {
      if (isEdit) await update.mutateAsync(payload)
      else await create.mutateAsync(payload)
      onClose()
    } catch (err) {
  if (isAxiosError(err) && err.response?.status === 422) {
    const data = err.response.data
    setErrors(data.errors ?? {})
    // Si le message employeId contient un avertissement d'affectation existante
    if (data.errors?.employeId?.[0]?.includes('possède déjà')) {
      setWarningAffectation(data.errors.employeId[0])
    }
  }
}
  }

  const err = (field: string) => errors[field]?.[0]

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={
        affecterOnly
          ? t('equipements.form.affecterTitle')
          : isEdit
            ? t('equipements.form.editTitle')
            : t('equipements.form.createTitle')
      }
      width={560}
    >
      <form onSubmit={onSubmit} className="grid grid-cols-2 gap-4">
        <Field label={t('equipements.form.nom')} error={err('nom')} className="col-span-2">
          <input className="input" value={form.nom} onChange={(e) => set('nom', e.target.value)} disabled={fieldDisabled} required />
        </Field>

        <Field label={t('equipements.form.type')} error={err('type')}>
          <select className="input" value={form.type} onChange={(e) => set('type', e.target.value)} disabled={fieldDisabled} required>
            <option value="" disabled>—</option>
            {enums?.typeEquipement.map((o) => (
              <option key={o.value} value={o.value}>{o.label}</option>
            ))}
          </select>
        </Field>

        <Field label={t('equipements.form.etat')} error={err('etat')}>
          {isEdit ? (
            <select className="input" value={form.etat} onChange={(e) => set('etat', e.target.value)} disabled={fieldDisabled} required>
              <option value="" disabled>—</option>
              {enums?.etatEquipement.map((o) => (
                <option key={o.value} value={o.value}>{o.label}</option>
              ))}
            </select>
          ) : (
            <input
              className="input"
              value={enums?.etatEquipement.find((o) => o.value === ETAT_INITIAL)?.label ?? 'Hors ligne'}
              disabled
              readOnly
            />
          )}
          {equipement?.demandeChangementEtatEnAttente && (
            <p className="mt-1 text-xs" style={{ color: 'var(--color-warn)' }}>
              Changement vers « {equipement.demandeChangementEtatEnAttente.etatDemandeLabel} » déjà en attente d'approbation.
            </p>
          )}
          {isTechnicien && isEdit && !equipement?.demandeChangementEtatEnAttente && (
            <p className="mt-1 text-xs text-[var(--color-faint)]">
              Un changement de statut sera soumis à l'approbation d'un Admin ou Super Admin.
            </p>
          )}
        </Field>

        <Field label={t('equipements.form.marque')} error={err('marque')}>
          <input className="input" value={form.marque ?? ''} onChange={(e) => set('marque', e.target.value)} disabled={fieldDisabled} required />
        </Field>

        <Field label={t('equipements.form.modele')} error={err('modele')}>
          <input className="input" value={form.modele ?? ''} onChange={(e) => set('modele', e.target.value)} disabled={fieldDisabled} required />
        </Field>

        <Field label={t('equipements.form.numeroSerie')} error={err('numeroSerie')}>
          <input
            className="input mono"
            value={form.numeroSerie ?? ''}
            onChange={(e) => set('numeroSerie', e.target.value)}
            pattern="^[A-Za-z0-9-]{3,}$"
            title={t('equipements.form.numeroSerieHint')}
            disabled={fieldDisabled}
            required
          />
        </Field>

        <Field label={t('equipements.form.affecteA')} error={warningAffectation ? undefined : err('employeId')}>
          <SearchableSelect
            value={form.employeId != null ? String(form.employeId) : ''}
            onChange={(v) => set('employeId', v ? Number(v) : null)}
            placeholder={t('equipements.form.affecterSearchPlaceholder')}
            options={(employes ?? []).map((emp) => ({ value: String(emp.id), label: `${emp.nomComplet} — ${emp.roleLabel}` }))}
          />
        </Field>

        <Field label={t('equipements.form.ip') + (reseauRequis ? '' : ' (optionnel)')} error={err('adresseIP')}>
          <input
            className="input mono"
            placeholder="192.168.1.10"
            value={form.adresseIP ?? ''}
            onChange={(e) => set('adresseIP', e.target.value)}
            pattern="^(25[0-5]|2[0-4][0-9]|1?[0-9]{1,2})(\.(25[0-5]|2[0-4][0-9]|1?[0-9]{1,2})){3}$"
            title={t('equipements.form.ipHint')}
            disabled={fieldDisabled}
            required={reseauRequis}
          />
        </Field>

        <Field label={t('equipements.form.mac') + (reseauRequis ? '' : ' (optionnel)')} error={err('adresseMAC')}>
          <input
            className="input mono"
            placeholder="AA:BB:CC:DD:EE:FF"
            value={form.adresseMAC ?? ''}
            onChange={(e) => set('adresseMAC', e.target.value)}
            pattern="^([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}$"
            title={t('equipements.form.macHint')}
            disabled={fieldDisabled}
            required={reseauRequis}
          />
        </Field>

        <Field label={t('equipements.form.localisation')} error={err('localisation')}>
          <select
            className="input"
            value={form.localisation ?? ''}
            onChange={(e) => set('localisation', e.target.value)}
            disabled={fieldDisabled}
            required
          >
            <option value="" disabled>—</option>
            {SITES.map((site) => (
              <option key={site} value={site}>{site}</option>
            ))}
          </select>
        </Field>

        <Field label={t('equipements.form.dateFinGarantie')} error={err('dateAcquisition')}>
          <input type="date" className="input" value={form.dateAcquisition ?? ''} onChange={(e) => set('dateAcquisition', e.target.value)} disabled={fieldDisabled} />
        </Field>

        {warningAffectation && (
  <div className="col-span-2 rounded-lg border border-orange-200 bg-orange-50 p-3 text-sm text-orange-700">
    <p className="mb-2">{warningAffectation}</p>
    <label className="flex items-center gap-2 font-medium cursor-pointer">
      <input
        type="checkbox"
        checked={forcerAffectation}
        onChange={(e) => setForcerAffectation(e.target.checked)}
      />
      Forcer l'affectation
    </label>
  </div>
)}
<div className="col-span-2 mt-2 flex justify-end gap-2">
          <button type="button" className="btn" onClick={onClose}>
            {t('equipements.form.cancel')}
          </button>
          <button type="submit" className="btn btn-primary" disabled={busy}>
            {busy
              ? t('equipements.form.saving')
              : affecterOnly
                ? t('equipements.form.affecterSubmit')
                : t('equipements.form.save')}
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