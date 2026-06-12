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
      <div className="flex min-h-screen flex-col items-center justify-center gap-4">
        <BrandMark size={36} />
        <p className="mono text-xs uppercase tracking-widest text-[var(--color-faint)]">
          {t('common.loading')}
        </p>
      </div>
    )
  }

  if (!user) {
    return <Navigate to="/login" replace state={{ from: location.pathname }} />
  }

  return <>{children}</>
}
