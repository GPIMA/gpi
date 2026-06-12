import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { useQuery } from '@tanstack/react-query'
import { isAxiosError } from 'axios'
import { Modal } from '@/components/Modal'
import { api } from '@/lib/api/client'
import { useEnums } from '@/lib/api/enums'
import type { Equipement, Paginated } from '@/lib/api/types'
import { useCreateIncident, type IncidentInput } from './api'

export function IncidentForm({ open, onClose }: { open: boolean; onClose: () => void }) {
  const { t } = useTranslation()
  const { data: enums } = useEnums()
  const create = useCreateIncident()

  // Equipment options for the select (single page large enough for the picker).
  const { data: equipements } = useQuery({
    queryKey: ['equipements-options'],
    queryFn: async () =>
      (await api.get<Paginated<Equipement>>('/equipements', { params: { per_page: 200 } })).data.data,
    enabled: open,
  })

  const [form, setForm] = useState<IncidentInput>({ titre: '', description: '', equipementId: 0, priorite: 'MOYENNE' })
  const [errors, setErrors] = useState<Record<string, string[]>>({})

  function set<K extends keyof IncidentInput>(k: K, v: IncidentInput[K]) {
    setForm((f) => ({ ...f, [k]: v }))
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})
    try {
      await create.mutateAsync(form)
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

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="field-label">{t('incidents.form.equipement')}</label>
            <select className="input" value={form.equipementId || ''} onChange={(e) => set('equipementId', Number(e.target.value))} required>
              <option value="" disabled>—</option>
              {equipements?.map((e) => <option key={e.id} value={e.id}>{e.nom}</option>)}
            </select>
            {err('equipementId') && <p className="mt-1 text-xs" style={{ color: '#ff8983' }}>{err('equipementId')}</p>}
          </div>
          <div>
            <label className="field-label">{t('incidents.form.priorite')}</label>
            <select className="input" value={form.priorite} onChange={(e) => set('priorite', e.target.value)} required>
              {enums?.severite.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
            </select>
          </div>
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
