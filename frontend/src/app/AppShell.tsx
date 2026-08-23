import { NavLink, Outlet } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { useAuth } from '@/features/auth/auth-context'
import { BrandMark } from '@/components/BrandMark'
import { LanguageSwitcher } from '@/components/LanguageSwitcher'
import { Icons } from '@/components/icons'
import { NotificationsBell } from '@/features/notifications/NotificationsBell'
import { ChatWidget } from '@/features/assistant/ChatWidget'
import { visibleSections } from './navigation'

export function AppShell() {
  const { t } = useTranslation()
  const { user, logout } = useAuth()
  if (!user) return null

  const sections = visibleSections(user.role)

  return (
    <div className="app-canvas grid min-h-screen grid-cols-[17rem_1fr] text-[var(--color-ink)]">
      <aside className="m-4 mr-0 flex min-h-[calc(100vh-2rem)] flex-col rounded-[28px] border border-[var(--color-line)] bg-white/90 shadow-[0_24px_70px_rgba(7,59,103,.12)] backdrop-blur">
        <div className="flex h-20 items-center gap-3 border-b border-[var(--color-line)] px-5">
          <BrandMark size={38} />
          <div className="leading-none">
            <div className="font-[var(--font-display)] text-lg font-black tracking-tight text-[var(--color-brand)]">
              {t('app.name')}
            </div>
            <div className="mt-1 text-[10px] font-black uppercase tracking-[0.14em] text-[var(--color-faint)]">
              {t('app.full')}
            </div>
          </div>
        </div>

        <nav className="flex-1 overflow-y-auto px-4 py-5">
          {sections.map((section) => (
            <div key={section.titleKey} className="mb-7">
              <p className="eyebrow px-3 pb-3">{t(`nav.sections.${section.titleKey}`)}</p>
              <ul className="space-y-1.5">
                {section.items.map((item) => {
                  const Icon = Icons[item.icon]
                  return (
                    <li key={item.to}>
                      <NavLink to={item.to} end={item.to === '/dashboard'}>
                        {({ isActive }) => (
                          <span
                            className="flex items-center gap-3 rounded-2xl px-3.5 py-3 text-[0.9rem] font-bold transition-all"
                            style={{
                              background: isActive ? 'var(--color-brand-wash)' : 'transparent',
                              color: isActive ? 'var(--color-brand)' : 'var(--color-muted)',
                              boxShadow: isActive ? 'inset 4px 0 0 var(--color-brand)' : 'none',
                            }}
                          >
                            <Icon size={18} />
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

        <div className="border-t border-[var(--color-line)] p-4">
          <a
            href="/vitrine/index.html"
            className="mb-3 flex items-center justify-center rounded-full border border-[var(--color-line)] bg-[var(--color-raised)] px-4 py-2 text-xs font-black uppercase tracking-[0.08em] text-[var(--color-brand)] transition hover:bg-[var(--color-overlay)]"
          >
            Voir la vitrine
          </a>
          <div className="flex items-center gap-3 rounded-[22px] bg-[var(--color-raised)] p-3 ring-1 ring-[var(--color-line)]">
            <div
              className="flex h-10 w-10 flex-none items-center justify-center rounded-2xl text-sm font-black"
              style={{ background: 'var(--color-brand)', color: 'var(--color-on-brand)' }}
            >
              {user.prenom[0]}
              {user.nom[0]}
            </div>
            <div className="min-w-0 flex-1">
              <p className="truncate text-[0.86rem] font-black text-[var(--color-ink)]">{user.nomComplet}</p>
              <p className="mono truncate text-[10px] uppercase tracking-wider text-[var(--color-faint)]">
                {user.roleLabel}
              </p>
            </div>
            <button
              className="btn-ghost flex h-9 w-9 items-center justify-center rounded-full"
              onClick={() => logout()}
              title={t('auth.logout')}
              aria-label={t('auth.logout')}
            >
              <Icons.logout size={17} />
            </button>
          </div>
          <p className="mono mt-3 text-center text-[10px] uppercase tracking-wider text-[var(--color-faint)]">
            Powered by GPI
          </p>
        </div>
      </aside>

      <div className="flex min-w-0 flex-col p-4 pl-5">
        <header className="sticky top-4 z-10 flex h-16 items-center gap-4 rounded-[24px] border border-[var(--color-line)] bg-white/92 px-6 shadow-[0_18px_48px_rgba(7,59,103,.10)] backdrop-blur">
          <div className="flex items-center gap-2 text-[var(--color-muted)]">
            <span className="dot" style={{ background: 'var(--color-online)' }} />
            <span className="mono text-xs font-bold uppercase tracking-[0.08em]">{t('common.online')}</span>
          </div>
          <div className="ml-auto flex items-center gap-3">
            <NotificationsBell />
            <LanguageSwitcher />
          </div>
        </header>

        <main className="flex-1 overflow-y-auto px-1 py-5">
          <Outlet />
        </main>
      </div>

      <ChatWidget />
    </div>
  )
}
