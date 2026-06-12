import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { useAuth } from '@/features/auth/auth-context'
import { useEnums } from '@/lib/api/enums'
import type { Incident } from '@/lib/api/types'
import { PageHeader } from '@/components/PageHeader'
import { StatusPill } from '@/components/StatusPill'
import { Modal } from '@/components/Modal'
import { Icons } from '@/components/icons'
import { useIncidents, usePrendreIncident, useResoudreIncident, type IncidentFilters } from './api'
import { IncidentForm } from './IncidentForm'

export function IncidentsPage() {
  const { t, i18n } = useTranslation()
  const { user } = useAuth()
  const canHandle = user?.role === 'ADMIN' || user?.role === 'TECHNICIEN'

  const { data: enums } = useEnums()
  const [filters, setFilters] = useState<IncidentFilters>({ page: 1 })
  const { data, isLoading } = useIncidents(filters)

  const [reportOpen, setReportOpen] = useState(false)
  const [resolving, setResolving] = useState<Incident | null>(null)
  const [solution, setSolution] = useState('')

  const prendre = usePrendreIncident()
  const resoudre = useResoudreIncident()

  const rows = data?.data ?? []

  async function submitResolve(e: React.FormEvent) {
    e.preventDefault()
    if (!resolving) return
    await resoudre.mutateAsync({ id: resolving.id, solution })
    setResolving(null)
    setSolution('')
  }

  return (
    <>
      <PageHeader
        eyebrow={t('app.name')}
        title={t('incidents.title')}
        subtitle={t('incidents.subtitle')}
        actions={
          <button className="btn btn-primary" onClick={() => setReportOpen(true)}>
            <Icons.plus size={16} />
            {t('incidents.report')}
          </button>
        }
      />

      <section className="panel">
        <div className="flex flex-wrap items-center gap-3 border-b border-[var(--color-line)] p-3">
          <select className="input w-auto" value={filters.statut ?? ''} onChange={(e) => setFilters((f) => ({ ...f, statut: e.target.value, page: 1 }))}>
            <option value="">{t('incidents.allStatuts')}</option>
            {enums?.statutIncident.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
          <select className="input w-auto" value={filters.priorite ?? ''} onChange={(e) => setFilters((f) => ({ ...f, priorite: e.target.value, page: 1 }))}>
            <option value="">{t('incidents.allPriorites')}</option>
            {enums?.severite.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
        </div>

        <div className="overflow-x-auto">
          <table className="data-table">
            <thead>
              <tr>
                <th>{t('incidents.cols.titre')}</th>
                <th>{t('incidents.cols.equipement')}</th>
                <th>{t('incidents.cols.priorite')}</th>
                <th>{t('incidents.cols.statut')}</th>
                <th>{t('incidents.cols.signalePar')}</th>
                <th>{t('incidents.cols.traitePar')}</th>
                <th>{t('incidents.cols.date')}</th>
                {canHandle && <th className="text-right">{t('incidents.cols.actions')}</th>}
              </tr>
            </thead>
            <tbody>
              {rows.map((x) => (
                <tr key={x.id}>
                  <td>
                    <div className="font-medium">{x.titre}</div>
                    <div className="max-w-xs truncate text-[11px] text-[var(--color-faint)]">{x.description}</div>
                  </td>
                  <td className="mono text-[var(--color-muted)]">{x.equipement?.nom ?? '—'}</td>
                  <td><StatusPill value={x.priorite} label={x.prioriteLabel} /></td>
                  <td><StatusPill value={x.statut} label={x.statutLabel} /></td>
                  <td className="text-[var(--color-muted)]">{x.signalePar ?? '—'}</td>
                  <td className="text-[var(--color-muted)]">{x.traitePar ?? '—'}</td>
                  <td className="mono text-[11px] text-[var(--color-faint)]">
                    {new Date(x.dateSignalement).toLocaleDateString(i18n.language, { dateStyle: 'short' })}
                  </td>
                  {canHandle && (
                    <td>
                      <div className="flex items-center justify-end gap-2">
                        {x.statut === 'OUVERT' && (
                          <button className="btn px-2.5 py-1 text-xs" onClick={() => prendre.mutate(x.id)} disabled={prendre.isPending}>
                            {t('incidents.take')}
                          </button>
                        )}
                        {(x.statut === 'OUVERT' || x.statut === 'EN_COURS') && (
                          <button className="btn btn-primary px-2.5 py-1 text-xs" onClick={() => { setResolving(x); setSolution('') }}>
                            {t('incidents.resolve')}
                          </button>
                        )}
                      </div>
                    </td>
                  )}
                </tr>
              ))}
              {!isLoading && rows.length === 0 && (
                <tr><td colSpan={canHandle ? 8 : 7} className="py-12 text-center text-[var(--color-muted)]">{t('incidents.empty')}</td></tr>
              )}
              {isLoading && (
                <tr><td colSpan={canHandle ? 8 : 7} className="py-12 text-center text-[var(--color-faint)]">{t('common.loading')}</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </section>

      {reportOpen && <IncidentForm open={reportOpen} onClose={() => setReportOpen(false)} />}

      <Modal open={!!resolving} onClose={() => setResolving(null)} title={t('incidents.resolveForm.title')} width={460}>
        <form onSubmit={submitResolve} className="space-y-4">
          <p className="text-sm text-[var(--color-muted)]">{resolving?.titre}</p>
          <div>
            <label className="field-label">{t('incidents.resolveForm.solution')}</label>
            <textarea className="input" rows={4} value={solution} onChange={(e) => setSolution(e.target.value)} required />
          </div>
          <div className="flex justify-end gap-2">
            <button type="button" className="btn" onClick={() => setResolving(null)}>{t('incidents.resolveForm.cancel')}</button>
            <button type="submit" className="btn btn-primary" disabled={resoudre.isPending}>
              {resoudre.isPending ? t('incidents.resolveForm.submitting') : t('incidents.resolveForm.submit')}
            </button>
          </div>
        </form>
      </Modal>
    </>
  )
}
