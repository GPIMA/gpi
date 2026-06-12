import { useTranslation } from 'react-i18next'
import { useAuth } from '@/features/auth/auth-context'
import { PageHeader } from '@/components/PageHeader'
import { Icons } from '@/components/icons'
import { useGenererPredictions, useModeleIA, usePredictions } from './api'

function riskColor(p: number): string {
  if (p >= 0.7) return 'var(--color-down)'
  if (p >= 0.4) return 'var(--color-warn)'
  return 'var(--color-online)'
}

export function PredictionsPage() {
  const { t, i18n } = useTranslation()
  const { user } = useAuth()
  const canGenerate = user?.role === 'ADMIN' || user?.role === 'TECHNICIEN'

  const { data, isLoading } = usePredictions()
  const { data: modele } = useModeleIA()
  const generer = useGenererPredictions()

  const rows = data?.data ?? []

  return (
    <>
      <PageHeader
        eyebrow={t('app.name')}
        title={t('predictions.title')}
        subtitle={t('predictions.subtitle')}
        actions={
          canGenerate && (
            <button className="btn btn-primary" onClick={() => generer.mutate()} disabled={generer.isPending}>
              <Icons.predictions size={16} />
              {generer.isPending ? t('predictions.generating') : t('predictions.generate')}
            </button>
          )
        }
      />

      {/* Model banner */}
      {modele && (
        <div className="panel mb-4 flex flex-wrap items-center gap-x-8 gap-y-2 p-4">
          <div>
            <p className="eyebrow mb-1">{t('predictions.model')}</p>
            <p className="font-medium">{modele.nom}</p>
            <p className="mono text-xs text-[var(--color-faint)]">{modele.algorithme}</p>
          </div>
          <div>
            <p className="eyebrow mb-1">{t('predictions.precision')}</p>
            <p className="mono text-sm">{Math.round(modele.precision * 100)}%</p>
          </div>
          <div>
            <p className="eyebrow mb-1">{t('predictions.version')}</p>
            <p className="mono text-sm text-[var(--color-muted)]">v{modele.version}</p>
          </div>
        </div>
      )}

      {isLoading ? (
        <p className="mono text-sm text-[var(--color-faint)]">{t('common.loading')}</p>
      ) : rows.length === 0 ? (
        <div className="panel p-10 text-center text-sm text-[var(--color-muted)]">{t('predictions.empty')}</div>
      ) : (
        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
          {rows.map((p) => {
            const pct = Math.round(p.probabilite * 100)
            const color = riskColor(p.probabilite)
            return (
              <div key={p.id} className="panel p-4">
                <div className="mb-3 flex items-start justify-between">
                  <div>
                    <p className="font-medium">{p.equipement?.nom}</p>
                    <p className="text-sm text-[var(--color-muted)]">{p.typePanneLabel}</p>
                  </div>
                  <span className="mono text-2xl font-semibold" style={{ color }}>{pct}%</span>
                </div>
                <div className="mb-3 h-1.5 overflow-hidden rounded-full" style={{ background: 'var(--color-overlay)' }}>
                  <div className="h-full rounded-full" style={{ width: `${pct}%`, background: color }} />
                </div>
                <div className="flex items-center justify-between text-[11px] text-[var(--color-faint)]">
                  <span className="mono uppercase tracking-wide">{t('predictions.horizon')}: {t('predictions.days', { count: p.horizonJours })}</span>
                  <span className="mono">{new Date(p.dateGeneration).toLocaleDateString(i18n.language, { dateStyle: 'short' })}</span>
                </div>
              </div>
            )
          })}
        </div>
      )}
    </>
  )
}
