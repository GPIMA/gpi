import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { useAuth } from '@/features/auth/auth-context'
import { useEnums } from '@/lib/api/enums'
import { PageHeader } from '@/components/PageHeader'
import { StatusPill, signalColor } from '@/components/StatusPill'
import { useAlertes, usePrendreAlerte, useResoudreAlerte, type AlerteFilters } from './api'

export function AlertesPage() {
  const { t, i18n } = useTranslation()
  const { user } = useAuth()
  const canHandle = user?.role === 'ADMIN' || user?.role === 'TECHNICIEN'
  const { data: enums } = useEnums()

  const [filters, setFilters] = useState<AlerteFilters>({ page: 1 })
  const { data, isLoading } = useAlertes(filters)
  const prendre = usePrendreAlerte()
  const resoudre = useResoudreAlerte()

  const rows = data?.data ?? []

  return (
    <>
      <PageHeader eyebrow={t('app.name')} title={t('alertes.title')} subtitle={t('alertes.subtitle')} />

      <section className="panel">
        <div className="flex flex-wrap items-center gap-3 border-b border-[var(--color-line)] p-3">
          <select
            className="input w-auto"
            value={filters.etat ?? ''}
            onChange={(e) => setFilters((f) => ({ ...f, etat: e.target.value, page: 1 }))}
          >
            <option value="">{t('alertes.allEtats')}</option>
            {enums?.etatAlerte.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
          <select
            className="input w-auto"
            value={filters.severite ?? ''}
            onChange={(e) => setFilters((f) => ({ ...f, severite: e.target.value, page: 1 }))}
          >
            <option value="">{t('alertes.allSeverites')}</option>
            {enums?.severite.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
        </div>

        <div className="overflow-x-auto">
          <table className="data-table">
            <thead>
              <tr>
                <th>{t('alertes.cols.severite')}</th>
                <th>{t('alertes.cols.type')}</th>
                <th>{t('alertes.cols.message')}</th>
                <th>{t('alertes.cols.equipement')}</th>
                <th>{t('alertes.cols.etat')}</th>
                <th>{t('alertes.cols.date')}</th>
                {canHandle && <th className="text-right">{t('alertes.cols.actions')}</th>}
              </tr>
            </thead>
            <tbody>
              {rows.map((a) => (
                <tr key={a.id} style={{ boxShadow: `inset 3px 0 0 ${signalColor(a.severite)}` }}>
                  <td><StatusPill value={a.severite} label={a.severiteLabel} /></td>
                  <td className="text-[var(--color-muted)]">{a.typeLabel}</td>
                  <td className="max-w-md"><span className="line-clamp-2">{a.message}</span></td>
                  <td className="mono text-[var(--color-muted)]">{a.equipement?.nom ?? '—'}</td>
                  <td><StatusPill value={a.etat} label={a.etatLabel} /></td>
                  <td className="mono text-[11px] text-[var(--color-faint)]">
                    {new Date(a.dateCreation).toLocaleString(i18n.language, { dateStyle: 'short', timeStyle: 'short' })}
                  </td>
                  {canHandle && (
                    <td>
                      <div className="flex items-center justify-end gap-2">
                        {a.etat === 'ACTIVE' && (
                          <button className="btn px-2.5 py-1 text-xs" onClick={() => prendre.mutate(a.id)} disabled={prendre.isPending}>
                            {t('alertes.take')}
                          </button>
                        )}
                        {a.etat !== 'RESOLUE' && (
                          <button className="btn btn-primary px-2.5 py-1 text-xs" onClick={() => resoudre.mutate(a.id)} disabled={resoudre.isPending}>
                            {t('alertes.resolve')}
                          </button>
                        )}
                      </div>
                    </td>
                  )}
                </tr>
              ))}
              {!isLoading && rows.length === 0 && (
                <tr>
                  <td colSpan={canHandle ? 7 : 6} className="py-12 text-center text-[var(--color-muted)]">{t('alertes.empty')}</td>
                </tr>
              )}
              {isLoading && (
                <tr>
                  <td colSpan={canHandle ? 7 : 6} className="py-12 text-center text-[var(--color-faint)]">{t('common.loading')}</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </section>
    </>
  )
}
