import { Navigate, useLocation } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { useAuth } from '@/features/auth/auth-context'
import { BrandMark } from '@/components/BrandMark'

export function RequireAuth({ children }: { children: React.ReactNode }) {
  const { user, ready } = useAuth()
  const location = useLocation()
  const { t } = useTranslation()

  if (!ready) {
    return (
      <div className="app-canvas flex min-h-screen flex-col items-center justify-center gap-5 text-[var(--color-ink)]">
        <div className="rounded-[28px] border border-[var(--color-line)] bg-white/92 px-8 py-7 text-center shadow-[0_24px_70px_rgba(7,59,103,.14)] backdrop-blur">
          <div className="mx-auto mb-4 flex justify-center">
            <BrandMark size={44} />
          </div>
          <p className="mono text-xs font-bold uppercase tracking-widest text-[var(--color-brand)]">
            {t('common.loading')}
          </p>
        </div>
      </div>
    )
  }

  if (!user) {
    return <Navigate to="/login" replace state={{ from: location.pathname }} />
  }

  return <>{children}</>
}
