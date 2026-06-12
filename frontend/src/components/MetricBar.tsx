// A compact usage bar (CPU/RAM/disk). Colour shifts with load so a hot machine
// reads at a glance — green < 70 ≤ amber < 90 ≤ red.
function loadColor(value: number): string {
  if (value >= 90) return 'var(--color-down)'
  if (value >= 70) return 'var(--color-warn)'
  return 'var(--color-online)'
}

export function MetricBar({ label, value }: { label: string; value: number | null }) {
  const v = value ?? 0
  return (
    <div className="flex items-center gap-2">
      <span className="w-9 shrink-0 text-[11px] uppercase tracking-wide text-[var(--color-faint)]">
        {label}
      </span>
      <div className="h-1.5 flex-1 overflow-hidden rounded-full" style={{ background: 'var(--color-overlay)' }}>
        <div
          className="h-full rounded-full transition-[width]"
          style={{ width: `${Math.min(100, v)}%`, background: value == null ? 'var(--color-idle)' : loadColor(v) }}
        />
      </div>
      <span className="mono w-10 shrink-0 text-right text-xs text-[var(--color-muted)]">
        {value == null ? '—' : `${Math.round(v)}%`}
      </span>
    </div>
  )
}
