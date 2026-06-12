import { useTranslation } from 'react-i18next'
import { Link } from 'react-router-dom'
import { PageHeader } from '@/components/PageHeader'
import { StatusPill } from '@/components/StatusPill'
import { NetworkBackdrop } from '@/components/NetworkBackdrop'
import { useDashboard } from './api'

const STATE_COLOR: Record<string, string> = {
  EN_LIGNE: 'var(--color-online)',
  MAINTENANCE: 'var(--color-warn)',
  EN_PANNE: 'var(--color-down)',
  HORS_LIGNE: 'var(--color-idle)',
}

function Kpi({ label, value, color }: { label: string; value: string | number; color?: string }) {
  return (
    <div className="kpi">
      <p className="kpi-label">{label}</p>
      <p className="kpi-value" style={color ? { color } : undefined}>{value}</p>
    </div>
  )
}

export function DashboardPage() {
  const { t, i18n } = useTranslation()
  const { data, isLoading } = useDashboard()

  const parc = data?.parc
  const alertes = data?.alertes
  const maxEtat = Math.max(1, ...(parc?.parEtat.map((e) => e.total) ?? [1]))

  const total = parc?.total ?? 0
  const enLigne = parc?.parEtat.find((e) => e.etat === 'EN_LIGNE')?.total ?? 0
  const actives = alertes?.actives ?? 0
  const dispo = total > 0 ? Math.round((enLigne / total) * 1000) / 10 : 0

  return (
    <div className="relative">
      <NetworkBackdrop className="pointer-events-none absolute -top-10 right-0 z-0 h-[360px] w-[600px] max-w-[55%] opacity-[0.55]" />
      <div className="relative z-10">
      <PageHeader title={t('dashboard.title')} subtitle={t('dashboard.subtitle')} />

      {isLoading ? (
        <p className="mono text-sm text-[var(--color-faint)]">{t('common.loading')}</p>
      ) : (
        <div className="space-y-4">
          {/* KPI strip — connected instrument readouts */}
          <div className="panel grid grid-cols-2 divide-x divide-[var(--color-line)] sm:grid-cols-4">
            <Kpi label={t('dashboard.fleet')} value={total} />
            <Kpi label={t('common.online')} value={enLigne} color="var(--color-online)" />
            <Kpi
              label={t('dashboard.activeAlerts')}
              value={actives}
              color={actives > 0 ? 'var(--color-down)' : 'var(--color-online)'}
            />
            <Kpi
              label={t('dashboard.availability')}
              value={`${dispo}%`}
              color={dispo >= 90 ? 'var(--color-online)' : dispo >= 70 ? 'var(--color-warn)' : 'var(--color-down)'}
            />
          </div>

          <div className="grid gap-4 lg:grid-cols-3">
            {/* Fleet by state */}
            <section className="panel lg:col-span-1">
              <div className="panel-head"><h2 className="panel-title">{t('dashboard.byState')}</h2></div>
              <div className="space-y-3 p-5">
                {parc?.parEtat.map((e) => (
                  <div key={e.etat} className="flex items-center gap-3">
                    <span className="w-28 shrink-0 text-[0.82rem] text-[var(--color-muted)]">{e.label}</span>
                    <div className="h-2 flex-1 overflow-hidden rounded-[1px]" style={{ background: 'var(--color-base)' }}>
                      <div className="h-full" style={{ width: `${(e.total / maxEtat) * 100}%`, background: STATE_COLOR[e.etat] ?? 'var(--color-idle)' }} />
                    </div>
                    <span className="mono w-7 text-right text-[0.82rem]">{e.total}</span>
                  </div>
                ))}
              </div>
            </section>

            {/* Activity feed */}
            <section className="panel lg:col-span-2">
              <div className="panel-head">
                <h2 className="panel-title">{t('dashboard.activity')}</h2>
                <Link to="/alertes" className="mono text-[11px] uppercase tracking-wider text-[var(--color-brand)] hover:underline">
                  {t('dashboard.all')} →
                </Link>
              </div>
              <div className="divide-y divide-[var(--color-line)]">
                {alertes?.recentes.length === 0 && (
                  <p className="p-8 text-center text-sm text-[var(--color-muted)]">{t('dashboard.noAlerts')}</p>
                )}
                {alertes?.recentes.map((a) => (
                  <div
                    key={a.id}
                    className="flex items-center gap-3 px-5 py-2.5"
                    style={{ boxShadow: `inset 3px 0 0 ${STATE_COLOR[a.severite] ?? 'var(--color-line-strong)'}` }}
                  >
                    <span className="mono w-12 shrink-0 text-[11px] text-[var(--color-faint)]">
                      {new Date(a.dateCreation).toLocaleTimeString(i18n.language, { hour: '2-digit', minute: '2-digit' })}
                    </span>
                    <StatusPill value={a.severite} label={a.severiteLabel} />
                    <div className="min-w-0 flex-1">
                      <p className="truncate text-[0.83rem]">{a.message}</p>
                    </div>
                    <span className="mono shrink-0 text-[11px] text-[var(--color-faint)]">{a.equipement?.nom}</span>
                  </div>
                ))}
              </div>
            </section>
          </div>
        </div>
      )}
      </div>
    </div>
  )
}
