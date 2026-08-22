import { Navigate } from 'react-router-dom'
import { useAuth } from '@/features/auth/auth-context'
import type { Role } from '@/lib/api/types'

/**
 * Gates a route to a set of roles, on top of RequireAuth (which only checks
 * that someone is logged in). Anyone with a role not in the list is bounced
 * to a safe default page instead of seeing the (empty/erroring) page.
 */
export function RequireRole({
  roles,
  redirectTo = '/equipements',
  children,
}: {
  roles: Role[]
  redirectTo?: string
  children: React.ReactNode
}) {
  const { user } = useAuth()

  if (user && !roles.includes(user.role)) {
    return <Navigate to={redirectTo} replace />
  }

  return <>{children}</>
}
