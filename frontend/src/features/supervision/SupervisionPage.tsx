import { useEffect, useMemo, useState } from 'react'
import { useTranslation } from 'react-i18next'
import {
  Area,
  AreaChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'
import { PageHeader } from '@/components/PageHeader'
import { MetricBar } from '@/components/MetricBar'
import { useMetriqueHistorique, useSupervisionApercu } from './api'

const SERIES = [
  { key: 'cpu', color: 'var(--color-info)' },
  { key: 'ram', color: 'var(--color-warn)' },
  { key: 'disque', color: 'var(--color-down)' },
] as const

export function SupervisionPage() {
  const { t, i18n } = useTranslation()
  const { data: rows, isLoading } = useSupervisionApercu()
  const online = useMemo(() => rows?.filter((r) => r.metrique) ?? [], [rows])

  const [selected, setSelected] = useState<number | null>(null)
  useEffect(() => {
    if (selected == null && online.length) setSelected(online[0].equipement.id)
  }, [online, selected])

  const { data: histo } = useMetriqueHistorique(selected)
  const chartData = (histo?.data ?? []).map((m) => ({
    t: new Date(m.dateHeure).toLocaleTimeString(i18n.language, { hour: '2-digit', minute: '2-digit' }),
    cpu: m.cpu,
    ram: m.ram,
    disque: m.disque,
  }))

  return (
    <>
      <PageHeader eyebrow={t('app.name')} title={t('supervision.title')} subtitle={t('supervision.subtitle')} />

      {isLoading ? (
        <p className="mono text-sm text-[var(--color-faint)]">{t('common.loading')}</p>
      ) : online.length === 0 ? (
        <div className="panel p-10 text-center text-sm text-[var(--color-muted)]">{t('supervision.noOnline')}</div>
      ) : (
        <div className="grid gap-4 lg:grid-cols-[20rem_1fr]">
          {/* Device list */}
          <section className="panel max-h-[36rem] overflow-y-auto">
            <div className="divide-y divide-[var(--color-line)]">
              {online.map(({ equipement: e, metrique: m }) => {
                const active = selected === e.id
                return (
                  <button
                    key={e.id}
                    onClick={() => setSelected(e.id)}
                    className="block w-full px-4 py-3 text-left transition-colors"
                    style={{ background: active ? 'var(--color-overlay)' : 'transparent', boxShadow: active ? 'inset 2px 0 0 var(--color-brand)' : 'none' }}
                  >
                    <div className="mb-2 flex items-center justify-between">
                      <span className="text-sm font-medium">{e.nom}</span>
                      <span className="mono text-[11px] text-[var(--color-faint)]">{e.adresseIP}</span>
                    </div>
                    <div className="space-y-1.5">
                      <MetricBar label="CPU" value={m?.cpu ?? null} />
                      <MetricBar label="RAM" value={m?.ram ?? null} />
                      <MetricBar label="DSK" value={m?.disque ?? null} />
                    </div>
                  </button>
                )
              })}
            </div>
          </section>

          {/* History chart */}
          <section className="panel">
            <div className="panel-head">
              <h2 className="panel-title">
                {online.find((r) => r.equipement.id === selected)?.equipement.nom ?? '—'}
              </h2>
              <span className="mono text-[11px] text-[var(--color-faint)]">{t('supervision.history')}</span>
            </div>
            <div className="p-4" style={{ height: 360 }}>
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={chartData} margin={{ top: 8, right: 8, left: -16, bottom: 0 }}>
                  <defs>
                    {SERIES.map((s) => (
                      <linearGradient key={s.key} id={`g-${s.key}`} x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor={s.color} stopOpacity={0.35} />
                        <stop offset="100%" stopColor={s.color} stopOpacity={0} />
                      </linearGradient>
                    ))}
                  </defs>
                  <CartesianGrid stroke="var(--color-line)" vertical={false} />
                  <XAxis dataKey="t" tick={{ fill: 'var(--color-faint)', fontSize: 11 }} stroke="var(--color-line-strong)" minTickGap={40} />
                  <YAxis domain={[0, 100]} tick={{ fill: 'var(--color-faint)', fontSize: 11 }} stroke="var(--color-line-strong)" unit="%" />
                  <Tooltip
                    contentStyle={{ background: 'var(--color-overlay)', border: '1px solid var(--color-line-strong)', borderRadius: 8, fontSize: 12 }}
                    labelStyle={{ color: 'var(--color-muted)' }}
                  />
                  {SERIES.map((s) => (
                    <Area
                      key={s.key}
                      type="monotone"
                      dataKey={s.key}
                      stroke={s.color}
                      fill={`url(#g-${s.key})`}
                      strokeWidth={1.6}
                      isAnimationActive={false}
                    />
                  ))}
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </section>
        </div>
      )}
    </>
  )
}
