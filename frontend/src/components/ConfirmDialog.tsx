import { Modal } from './Modal'

export function ConfirmDialog({
  open,
  onClose,
  onConfirm,
  title,
  message,
  confirmLabel,
  cancelLabel,
  busy,
  destructive = true,
}: {
  open: boolean
  onClose: () => void
  onConfirm: () => void
  title: string
  message: string
  confirmLabel: string
  cancelLabel: string
  busy?: boolean
  destructive?: boolean
}) {
  return (
    <Modal open={open} onClose={onClose} title={title} width={420}>
      <p className="text-sm text-[var(--color-muted)]">{message}</p>
      <div className="mt-6 flex justify-end gap-2">
        <button className="btn" onClick={onClose}>{cancelLabel}</button>
        <button
          className="btn"
          onClick={onConfirm}
          disabled={busy}
          style={
            destructive
              ? { background: 'var(--color-down)', borderColor: 'var(--color-down)', color: '#fff', fontWeight: 600 }
              : undefined
          }
        >
          {confirmLabel}
        </button>
      </div>
    </Modal>
  )
}
