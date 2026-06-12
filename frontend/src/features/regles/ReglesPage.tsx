import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { PageHeader } from '@/components/PageHeader'
import { StatusPill } from '@/components/StatusPill'
import { ConfirmDialog } from '@/components/ConfirmDialog'
import { Icons } from '@/components/icons'
import type { RegleAlerte } from '@/lib/api/types'
import { useDeleteRegle, useRegles } from './api'
import { RegleForm } from './RegleForm'

export function ReglesPage() {
  const { t } = useTranslation()
  const { data: regles, isLoading } = useRegles()

  const [formOpen, setFormOpen] = useState(false)
  const [editing, setEditing] = useState<RegleAlerte | null>(null)
  const [toDelete, setToDelete] = useState<RegleAlerte | null>(null)
  const del = useDeleteRegle()

  return (
    <>
      <PageHeader
        eyebrow={t('app.name')}
        title={t('regles.title')}
        subtitle={t('regles.subtitle')}
        actions={
          <button className="btn btn-primary" onClick={() => { setEditing(null); setFormOpen(true) }}>
            <Icons.plus size={16} />
            {t('regles.add')}
          </button>
        }
      />

      <section className="panel overflow-x-auto">
        <table className="data-table">
          <thead>
            <tr>
              <th>{t('regles.cols.nom')}</th>
              <th>{t('regles.cols.condition')}</th>
              <th>{t('regles.cols.severite')}</th>
              <th>{t('regles.cols.type')}</th>
              <th>{t('regles.cols.etat')}</th>
              <th className="text-right">{t('regles.cols.actions')}</th>
            </tr>
          </thead>
          <tbody>
            {regles?.map((r) => (
              <tr key={r.id}>
                <td className="font-medium">{r.nom}</td>
                <td className="mono text-[var(--color-muted)]">
                  {t(`regles.targets.${r.metriqueCible}`)} {r.operateur} {r.seuil}%
                </td>
                <td><StatusPill value={r.severite} label={r.severiteLabel} /></td>
                <td className="text-[var(--color-muted)]">{r.typeAlerteLabel}</td>
                <td>
                  <span className="pill" style={{ color: r.actif ? 'var(--color-online)' : 'var(--color-faint)' }}>
                    <span className="dot" style={{ background: r.actif ? 'var(--color-online)' : 'var(--color-idle)' }} />
                    {r.actif ? t('regles.active') : t('regles.inactive')}
                  </span>
                </td>
                <td>
                  <div className="flex items-center justify-end gap-1">
                    <button className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px]" onClick={() => { setEditing(r); setFormOpen(true) }} aria-label={t('regles.form.editTitle')}>
                      <Icons.edit size={15} />
                    </button>
                    <button className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px]" onClick={() => setToDelete(r)} aria-label={t('regles.delete.confirm')}>
                      <Icons.trash size={15} />
                    </button>
                  </div>
                </td>
              </tr>
            ))}
            {!isLoading && regles?.length === 0 && (
              <tr><td colSpan={6} className="py-12 text-center text-[var(--color-muted)]">{t('regles.empty')}</td></tr>
            )}
            {isLoading && (
              <tr><td colSpan={6} className="py-12 text-center text-[var(--color-faint)]">{t('common.loading')}</td></tr>
            )}
          </tbody>
        </table>
      </section>

      {formOpen && <RegleForm open={formOpen} onClose={() => setFormOpen(false)} regle={editing} />}

      <ConfirmDialog
        open={!!toDelete}
        onClose={() => setToDelete(null)}
        onConfirm={async () => { if (toDelete) { await del.mutateAsync(toDelete.id); setToDelete(null) } }}
        title={t('regles.delete.title')}
        message={t('regles.delete.message', { nom: toDelete?.nom ?? '' })}
        confirmLabel={t('regles.delete.confirm')}
        cancelLabel={t('regles.delete.cancel')}
        busy={del.isPending}
      />
    </>
  )
}
