import { useEffect, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { useAuth } from '@/features/auth/auth-context'
import { useEnums } from '@/lib/api/enums'
import type { Equipement } from '@/lib/api/types'
import { PageHeader } from '@/components/PageHeader'
import { StatusPill } from '@/components/StatusPill'
import { ConfirmDialog } from '@/components/ConfirmDialog'
import { Icons } from '@/components/icons'
import { useDeleteEquipement, useEquipements, useScanReseau, type EquipementFilters } from './api'
import { EquipementForm } from './EquipementForm'

export function EquipementsPage() {
  const { t } = useTranslation()
  const { user } = useAuth()
  const isAdmin = user?.role === 'ADMIN' || user?.role === 'SUPER_ADMIN'
  const { data: enums } = useEnums()

  const [filters, setFilters] = useState<EquipementFilters>({ page: 1 })
  const [search, setSearch] = useState('')
  const { data, isLoading, isFetching } = useEquipements(filters)

  const [formOpen, setFormOpen] = useState(false)
  const [editing, setEditing] = useState<Equipement | null>(null)
  const [toDelete, setToDelete] = useState<Equipement | null>(null)
  const [notice, setNotice] = useState<string | null>(null)

  const del = useDeleteEquipement()
  const scan = useScanReseau()

  // Debounce the search box into the query filter.
  useEffect(() => {
    const id = setTimeout(() => setFilters((f) => ({ ...f, q: search, page: 1 })), 350)
    return () => clearTimeout(id)
  }, [search])

  useEffect(() => {
    if (!notice) return
    const id = setTimeout(() => setNotice(null), 4000)
    return () => clearTimeout(id)
  }, [notice])

  function openCreate() {
    setEditing(null)
    setFormOpen(true)
  }
  function openEdit(e: Equipement) {
    setEditing(e)
    setFormOpen(true)
  }

  async function runScan() {
    const res = await scan.mutateAsync()
    setNotice(t('equipements.scanDone', { count: res.scan.nbDetectes }))
  }

  async function confirmDelete() {
    if (!toDelete) return
    await del.mutateAsync(toDelete.id)
    setToDelete(null)
  }

  const rows = data?.data ?? []
  const meta = data?.meta

  return (
    <>
      <PageHeader
        eyebrow={t('app.name')}
        title={t('equipements.title')}
        subtitle={t('equipements.subtitle')}
        actions={
          isAdmin && (
            <>
              <button className="btn" onClick={runScan} disabled={scan.isPending}>
                <Icons.scan size={16} />
                {scan.isPending ? t('equipements.scanning') : t('equipements.scan')}
              </button>
              <button className="btn btn-primary" onClick={openCreate}>
                <Icons.plus size={16} />
                {t('equipements.add')}
              </button>
            </>
          )
        }
      />

      {notice && (
        <div
          className="mb-4 rounded-[6px] border px-3 py-2 text-sm"
          style={{ borderColor: 'var(--color-brand-dim)', background: 'var(--color-brand-wash)', color: 'var(--color-ink)' }}
        >
          {notice}
        </div>
      )}

      <section className="panel">
        {/* Toolbar */}
        <div className="flex flex-wrap items-center gap-3 border-b border-[var(--color-line)] p-3">
          <div className="relative min-w-56 flex-1">
            <span className="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--color-faint)]">
              <Icons.search size={15} />
            </span>
            <input
              className="input pl-8"
              placeholder={t('equipements.searchPlaceholder')}
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
          <select
            className="input w-auto"
            value={filters.type ?? ''}
            onChange={(e) => setFilters((f) => ({ ...f, type: e.target.value, page: 1 }))}
          >
            <option value="">{t('equipements.allTypes')}</option>
            {enums?.typeEquipement.map((o) => (
              <option key={o.value} value={o.value}>{o.label}</option>
            ))}
          </select>
          <select
            className="input w-auto"
            value={filters.etat ?? ''}
            onChange={(e) => setFilters((f) => ({ ...f, etat: e.target.value, page: 1 }))}
          >
            <option value="">{t('equipements.allEtats')}</option>
            {enums?.etatEquipement.map((o) => (
              <option key={o.value} value={o.value}>{o.label}</option>
            ))}
          </select>
        </div>

        {/* Table */}
        <div className="overflow-x-auto">
          <table className="data-table">
            <thead>
              <tr>
                <th>{t('equipements.cols.nom')}</th>
                <th>{t('equipements.cols.type')}</th>
                <th>{t('equipements.cols.etat')}</th>
                <th>{t('equipements.cols.ip')}</th>
                <th>{t('equipements.cols.localisation')}</th>
                <th>{t('equipements.cols.affecte')}</th>
                {isAdmin && <th className="text-right">{t('equipements.cols.actions')}</th>}
              </tr>
            </thead>
            <tbody>
              {rows.map((e) => (
                <tr key={e.id}>
                  <td>
                    <div className="font-medium">{e.nom}</div>
                    <div className="mono text-[11px] text-[var(--color-faint)]">
                      {[e.marque, e.modele].filter(Boolean).join(' ') || '—'}
                    </div>
                  </td>
                  <td className="text-[var(--color-muted)]">{e.typeLabel}</td>
                  <td><StatusPill value={e.etat} label={e.etatLabel} /></td>
                  <td className="mono text-[var(--color-muted)]">{e.adresseIP ?? '—'}</td>
                  <td className="text-[var(--color-muted)]">{e.localisation ?? '—'}</td>
                  <td className="text-[var(--color-muted)]">{e.affectation?.employe ?? '—'}</td>
                  {isAdmin && (
                    <td>
                      <div className="flex items-center justify-end gap-1">
                        <button className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px]" onClick={() => openEdit(e)} aria-label={t('equipements.form.editTitle')}>
                          <Icons.edit size={15} />
                        </button>
                        <button className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px]" onClick={() => setToDelete(e)} aria-label={t('equipements.delete.confirm')}>
                          <Icons.trash size={15} />
                        </button>
                      </div>
                    </td>
                  )}
                </tr>
              ))}
              {!isLoading && rows.length === 0 && (
                <tr>
                  <td colSpan={isAdmin ? 7 : 6} className="py-12 text-center text-[var(--color-muted)]">
                    {t('equipements.empty')}
                  </td>
                </tr>
              )}
              {isLoading && (
                <tr>
                  <td colSpan={isAdmin ? 7 : 6} className="py-12 text-center text-[var(--color-faint)]">
                    {t('common.loading')}
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Footer / pagination */}
        {meta && meta.total > 0 && (
          <div className="flex items-center justify-between border-t border-[var(--color-line)] px-4 py-3">
            <span className="mono text-[11px] text-[var(--color-faint)]">
              {meta.from}–{meta.to} / {meta.total}
              {isFetching && ' · …'}
            </span>
            <div className="flex items-center gap-1">
              <button
                className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px] disabled:opacity-40"
                disabled={meta.current_page <= 1}
                onClick={() => setFilters((f) => ({ ...f, page: (f.page ?? 1) - 1 }))}
                aria-label="Précédent"
              >
                <Icons.chevronLeft size={16} />
              </button>
              <span className="mono px-2 text-xs text-[var(--color-muted)]">
                {meta.current_page} / {meta.last_page}
              </span>
              <button
                className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px] disabled:opacity-40"
                disabled={meta.current_page >= meta.last_page}
                onClick={() => setFilters((f) => ({ ...f, page: (f.page ?? 1) + 1 }))}
                aria-label="Suivant"
              >
                <Icons.chevronRight size={16} />
              </button>
            </div>
          </div>
        )}
      </section>

      {formOpen && (
        <EquipementForm open={formOpen} onClose={() => setFormOpen(false)} equipement={editing} />
      )}

      <ConfirmDialog
        open={!!toDelete}
        onClose={() => setToDelete(null)}
        onConfirm={confirmDelete}
        title={t('equipements.delete.title')}
        message={t('equipements.delete.message', { nom: toDelete?.nom ?? '' })}
        confirmLabel={t('equipements.delete.confirm')}
        cancelLabel={t('equipements.delete.cancel')}
        busy={del.isPending}
      />
    </>
  )
}
