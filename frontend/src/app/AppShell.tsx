import { NavLink, Outlet } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { useAuth } from '@/features/auth/auth-context'
import { BrandMark } from '@/components/BrandMark'
import { LanguageSwitcher } from '@/components/LanguageSwitcher'
import { Icons } from '@/components/icons'
import { NotificationsBell } from '@/features/notifications/NotificationsBell'
import { visibleSections } from './navigation'

export function AppShell() {
  const { t } = useTranslation()
  const { user, logout } = useAuth()
  if (!user) return null

  const sections = visibleSections(user.role)

  return (
    <div className="grid min-h-screen grid-cols-[15rem_1fr]">
      {/* Sidebar */}
      <aside className="flex flex-col border-r border-[var(--color-line)] bg-[var(--color-surface)]">
        <div className="flex h-14 items-center gap-2.5 border-b border-[var(--color-line)] px-4">
          <BrandMark size={30} />
          <div className="leading-none">
            <div className="font-[var(--font-display)] text-base font-semibold tracking-tight">
              {t('app.name')}
            </div>
            <div className="mt-0.5 text-[10px] uppercase tracking-[0.12em] text-[var(--color-faint)]">
              {t('app.full')}
            </div>
          </div>
        </div>

        <nav className="flex-1 overflow-y-auto px-3 py-4">
          {sections.map((section) => (
            <div key={section.titleKey} className="mb-6">
              <p className="eyebrow px-2.5 pb-2">{t(`nav.sections.${section.titleKey}`)}</p>
              <ul className="space-y-0.5">
                {section.items.map((item) => {
                  const Icon = Icons[item.icon]
                  return (
                    <li key={item.to}>
                      <NavLink to={item.to} end={item.to === '/'} className="nav-link">
                        {({ isActive }) => (
                          <span
                            className="flex items-center gap-2.5 rounded-[5px] px-2.5 py-2 text-[0.85rem] transition-colors"
                            style={{
                              background: isActive ? 'var(--color-overlay)' : 'transparent',
                              color: isActive ? 'var(--color-ink)' : 'var(--color-muted)',
                              boxShadow: isActive
                                ? 'inset 2px 0 0 var(--color-brand)'
                                : 'none',
                            }}
                          >
                            <Icon size={17} />
                            {t(`nav.${item.key}`)}
                          </span>
                        )}
                      </NavLink>
                    </li>
                  )
                })}
              </ul>
            </div>
          ))}
        </nav>

        {/* User footer */}
        <div className="border-t border-[var(--color-line)] p-3">
          <div className="flex items-center gap-2.5 px-1.5 py-1">
            <div
              className="flex h-8 w-8 flex-none items-center justify-center rounded-[5px] text-xs font-semibold"
              style={{ background: 'var(--color-brand-wash)', color: 'var(--color-brand)' }}
            >
              {user.prenom[0]}
              {user.nom[0]}
            </div>
            <div className="min-w-0 flex-1">
              <p className="truncate text-[0.82rem] text-[var(--color-ink)]">{user.nomComplet}</p>
              <p className="mono truncate text-[10.5px] uppercase tracking-wider text-[var(--color-faint)]">
                {user.roleLabel}
              </p>
            </div>
            <button
              className="btn-ghost flex h-7 w-7 items-center justify-center rounded-[5px]"
              onClick={() => logout()}
              title={t('auth.logout')}
              aria-label={t('auth.logout')}
            >
              <Icons.logout size={16} />
            </button>
          </div>
        </div>
      </aside>

      {/* Main column */}
      <div className="flex min-w-0 flex-col">
        <header className="flex h-14 items-center gap-4 border-b border-[var(--color-line)] bg-[var(--color-surface)] px-6">
          <div className="flex items-center gap-2 text-[var(--color-faint)]">
            <span className="dot" style={{ background: 'var(--color-online)' }} />
            <span className="mono text-xs">{t('common.online')}</span>
          </div>
          <div className="ml-auto flex items-center gap-3">
            <NotificationsBell />
            <LanguageSwitcher />
          </div>
        </header>

        <main className="app-canvas flex-1 overflow-y-auto p-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
