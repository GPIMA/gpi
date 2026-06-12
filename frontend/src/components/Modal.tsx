import { useEffect, useRef } from 'react'
import { Icons } from './icons'

/**
 * Lightweight accessible dialog: scrim dismiss, Escape to close, focus moved in
 * on open. Used for forms and confirmations.
 */
export function Modal({
  open,
  onClose,
  title,
  children,
  width = 480,
}: {
  open: boolean
  onClose: () => void
  title: string
  children: React.ReactNode
  width?: number
}) {
  const ref = useRef<HTMLDivElement>(null)

  useEffect(() => {
    if (!open) return
    const onKey = (e: KeyboardEvent) => e.key === 'Escape' && onClose()
    document.addEventListener('keydown', onKey)
    ref.current?.focus()
    return () => document.removeEventListener('keydown', onKey)
  }, [open, onClose])

  if (!open) return null

  return (
    <div
      className="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:p-8"
      style={{ background: 'rgba(5, 7, 10, 0.55)' }}
      onMouseDown={(e) => e.target === e.currentTarget && onClose()}
    >
      <div
        ref={ref}
        tabIndex={-1}
        role="dialog"
        aria-modal="true"
        aria-label={title}
        className="panel mt-8 w-full outline-none"
        style={{ maxWidth: width }}
      >
        <div className="panel-head">
          <h2 className="panel-title">{title}</h2>
          <button className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px]" onClick={onClose} aria-label="Fermer">
            <Icons.close size={16} />
          </button>
        </div>
        <div className="p-5">{children}</div>
      </div>
    </div>
  )
}
