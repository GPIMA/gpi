import { useEffect, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { useAuth } from '@/features/auth/auth-context'
import { useEnums } from '@/lib/api/enums'
import type { Utilisateur } from '@/lib/api/types'
import { PageHeader } from '@/components/PageHeader'
import { ConfirmDialog } from '@/components/ConfirmDialog'
import { Icons } from '@/components/icons'
import { useDeleteUtilisateur, useUtilisateurs, type UtilisateurFilters } from './api'
import { UtilisateurForm } from './UtilisateurForm'
import { HistoriqueModal } from './HistoriqueModal'

export function AdministrationPage() {
  const { t } = useTranslation()
  const { user } = useAuth()
  const isAdmin = user?.role === 'SUPER_ADMIN' || user?.role === 'ADMIN'
  const { data: enums } = useEnums()

  const [filters, setFilters] = useState<UtilisateurFilters>({ page: 1 })
  const [search, setSearch] = useState('')
  const { data, isLoading } = useUtilisateurs(filters)

  const [formOpen, setFormOpen] = useState(false)
  const [editing, setEditing] = useState<Utilisateur | null>(null)
  const [toDelete, setToDelete] = useState<Utilisateur | null>(null)
  const [historiqueUser, setHistoriqueUser] = useState<Utilisateur | null>(null)
  const del = useDeleteUtilisateur()

  useEffect(() => {
    const id = setTimeout(() => setFilters((f) => ({ ...f, q: search, page: 1 })), 350)
    return () => clearTimeout(id)
  }, [search])

  const rows = data?.data ?? []
  const meta = data?.meta

  return (
    <>
      <PageHeader
        eyebrow={t('app.name')}
        title={t('administration.title')}
        subtitle={t('administration.subtitle')}
        actions={
          isAdmin && (
            <button className="btn btn-primary" onClick={() => { setEditing(null); setFormOpen(true) }}>
              <Icons.plus size={16} />
              {t('administration.add')}
            </button>
          )
        }
      />

      <section className="panel">
        <div className="flex flex-wrap items-center gap-3 border-b border-[var(--color-line)] p-3">
          <div className="relative min-w-56 flex-1">
            <span className="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[var(--color-faint)]">
              <Icons.search size={15} />
            </span>
            <input className="input pl-8" placeholder={t('administration.searchPlaceholder')} value={search} onChange={(e) => setSearch(e.target.value)} />
          </div>
          <select className="input w-auto" value={filters.role ?? ''} onChange={(e) => setFilters((f) => ({ ...f, role: e.target.value, page: 1 }))}>
            <option value="">{t('administration.allRoles')}</option>
            {enums?.roleUtilisateur.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
         <select className="input w-auto" value={filters.localisation ?? ''} onChange={(e) => setFilters((f) => ({ ...f, localisation: e.target.value, page: 1 }))}>
  <option value="">Toutes les localisations</option>
  <option value="Rabat">Rabat</option>
  <option value="Casablanca">Casablanca</option>
  <option value="Tanger">Tanger</option>
</select>
        </div>

        <div className="overflow-x-auto">
          <table className="data-table">
            <thead>
              <tr>
                <th>{t('administration.cols.nom')}</th>
                <th>{t('administration.cols.email')}</th>
                <th>{t('administration.cols.role')}</th>
                <th>{t('administration.cols.detail')}</th>
                <th>{t('administration.cols.poste')}</th>
                <th className="text-right">{t('administration.cols.actions')}</th>
              </tr>
            </thead>
            <tbody>
              {rows.map((u) => (
                <tr key={u.id}>
                  <td className="font-medium">{u.nomComplet}</td>
                  <td className="mono text-[var(--color-muted)]">{u.email}</td>
                  <td className="text-[var(--color-muted)]">{u.roleLabel}</td>
                  <td className="text-[var(--color-muted)]">{u.departement ?? '—'}</td>
                  <td className="text-[var(--color-muted)]">{u.posteActuel?.nom ?? '—'}</td>
                  <td>
                    <div className="flex items-center justify-end gap-1">
                      <button className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px]" onClick={() => setHistoriqueUser(u)} aria-label="Historique">
                        <Icons.history size={15} />
                      </button>
                      {isAdmin && (
                        <>
                          <button className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px]" onClick={() => { setEditing(u); setFormOpen(true) }} aria-label={t('administration.form.editTitle')}>
                            <Icons.edit size={15} />
                          </button>
                          <button className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px]" onClick={() => setToDelete(u)} aria-label={t('administration.delete.confirm')}>
                            <Icons.trash size={15} />
                          </button>
                        </>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
              {!isLoading && rows.length === 0 && (
                <tr><td colSpan={6} className="py-12 text-center text-[var(--color-muted)]">{t('administration.empty')}</td></tr>
              )}
              {isLoading && (
                <tr><td colSpan={6} className="py-12 text-center text-[var(--color-faint)]">{t('common.loading')}</td></tr>
              )}
            </tbody>
          </table>
        </div>

        {meta && meta.total > 0 && (
          <div className="flex items-center justify-between border-t border-[var(--color-line)] px-4 py-3">
            <span className="mono text-[11px] text-[var(--color-faint)]">{meta.from}–{meta.to} / {meta.total}</span>
            <div className="flex items-center gap-1">
              <button className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px] disabled:opacity-40" disabled={meta.current_page <= 1} onClick={() => setFilters((f) => ({ ...f, page: (f.page ?? 1) - 1 }))} aria-label="Précédent">
                <Icons.chevronLeft size={16} />
              </button>
              <span className="mono px-2 text-xs text-[var(--color-muted)]">{meta.current_page} / {meta.last_page}</span>
              <button className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px] disabled:opacity-40" disabled={meta.current_page >= meta.last_page} onClick={() => setFilters((f) => ({ ...f, page: (f.page ?? 1) + 1 }))} aria-label="Suivant">
                <Icons.chevronRight size={16} />
              </button>
            </div>
          </div>
        )}
      </section>

      {formOpen && <UtilisateurForm open={formOpen} onClose={() => setFormOpen(false)} utilisateur={editing} />}

      <ConfirmDialog
        open={!!toDelete}
        onClose={() => setToDelete(null)}
        onConfirm={async () => { if (toDelete) { await del.mutateAsync(toDelete.id); setToDelete(null) } }}
        title={t('administration.delete.title')}
        message={t('administration.delete.message', { nom: toDelete?.nomComplet ?? '' })}
        confirmLabel={t('administration.delete.confirm')}
        cancelLabel={t('administration.delete.cancel')}
        busy={del.isPending}
      />

      {historiqueUser && (
        <HistoriqueModal utilisateur={historiqueUser} onClose={() => setHistoriqueUser(null)} />
      )}
    </>
  )
}