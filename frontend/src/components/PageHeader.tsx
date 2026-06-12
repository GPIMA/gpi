export function PageHeader({
  title,
  subtitle,
  actions,
}: {
  /** Accepted for compatibility with existing call sites; no longer rendered —
   *  the per-page uppercase kicker read as boilerplate, so it was removed. */
  eyebrow?: string
  title: string
  subtitle?: string
  actions?: React.ReactNode
}) {
  return (
    <header className="mb-5 flex flex-wrap items-end justify-between gap-4 border-b border-[var(--color-line)] pb-3">
      <div className="min-w-0">
        <h1 className="text-[1.35rem] font-semibold tracking-tight">{title}</h1>
        {subtitle && <p className="mt-0.5 text-[0.85rem] text-[var(--color-muted)]">{subtitle}</p>}
      </div>
      {actions && <div className="flex items-center gap-2">{actions}</div>}
    </header>
  )
}
