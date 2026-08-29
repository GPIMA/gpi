import { useEffect, useRef, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { Icons } from '@/components/icons'
import { useMarquerLues, useNotifications } from './api'

export function NotificationsBell() {
  const { t, i18n } = useTranslation()
  const { data } = useNotifications()
  const markRead = useMarquerLues()
  const [open, setOpen] = useState(false)
  const ref = useRef<HTMLDivElement>(null)

  const unread = data?.nonLues ?? 0
  const items = data?.data ?? []

  useEffect(() => {
    if (!open) return
    const onClick = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false)
    }
    document.addEventListener('mousedown', onClick)
    return () => document.removeEventListener('mousedown', onClick)
  }, [open])

  return (
    <div className="relative" ref={ref}>
      <button
        className="btn-ghost relative flex h-8 w-8 items-center justify-center rounded-[5px]"
        onClick={() => setOpen((o) => !o)}
        aria-label={t('notifications.title')}
      >
        <Icons.bell size={18} />
        {unread > 0 && (
          <span
            className="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full px-1 text-[10px] font-semibold"
            style={{ background: 'var(--color-down)', color: '#fff' }}
          >
            {unread}
          </span>
        )}
      </button>

      {open && (
        <div
          className="panel absolute right-0 top-10 z-50 w-80 overflow-hidden"
          style={{ boxShadow: '0 12px 32px rgba(0,0,0,0.45)' }}
        >
          <div className="panel-head">
            <h3 className="panel-title">{t('notifications.title')}</h3>
            {unread > 0 && (
              <button className="text-xs text-[var(--color-brand)] hover:underline" onClick={() => markRead.mutate()}>
                {t('notifications.markRead')}
              </button>
            )}
          </div>
          <div className="max-h-80 overflow-y-auto divide-y divide-[var(--color-line)]">
            {items.length === 0 && (
              <p className="p-6 text-center text-sm text-[var(--color-muted)]">{t('notifications.empty')}</p>
            )}
            {items.map((n) => (
              <div key={n.id} className="flex gap-2.5 px-4 py-3" style={{ background: n.lue ? 'transparent' : 'var(--color-brand-wash)' }}>
                {!n.lue && <span className="mt-1.5 dot shrink-0" style={{ background: 'var(--color-brand)' }} />}
                <div className={n.lue ? 'pl-[18px]' : ''}>
                  <p className="text-sm" style={{ color: '#ff0000' }}>{n.contenu}</p>
                  <p className="mono text-[10.5px] text-[var(--color-faint)]">
                    {n.canalLabel} · {new Date(n.dateEnvoi).toLocaleString(i18n.language, { dateStyle: 'short', timeStyle: 'short' })}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
