import { lazy, Suspense, useEffect } from 'react'
import { BrowserRouter, Navigate, Outlet, Route, Routes } from 'react-router-dom'
import { AppShell } from '@/app/AppShell'
import { RequireAuth } from '@/app/RequireAuth'
import { RequireRole } from '@/app/RequireRole'
import { ErrorBoundary } from '@/components/ErrorBoundary'
import { LoginPage } from '@/features/auth/LoginPage'

// Route-level code splitting keeps the initial bundle small; heavy pages
// (charts) load on demand.
const DashboardPage = lazy(() => import('@/features/dashboard/DashboardPage').then((m) => ({ default: m.DashboardPage })))
const EquipementsPage = lazy(() => import('@/features/equipements/EquipementsPage').then((m) => ({ default: m.EquipementsPage })))
const AlertesPage = lazy(() => import('@/features/alertes/AlertesPage').then((m) => ({ default: m.AlertesPage })))
const IncidentsPage = lazy(() => import('@/features/incidents/IncidentsPage').then((m) => ({ default: m.IncidentsPage })))
const PredictionsPage = lazy(() => import('@/features/predictions/PredictionsPage').then((m) => ({ default: m.PredictionsPage })))
const AssistantPage = lazy(() => import('@/features/assistant/AssistantPage').then((m) => ({ default: m.AssistantPage })))
const ReglesPage = lazy(() => import('@/features/regles/ReglesPage').then((m) => ({ default: m.ReglesPage })))
const AdministrationPage = lazy(() => import('@/features/administration/AdministrationPage').then((m) => ({ default: m.AdministrationPage })))
const DemandesInscriptionPage = lazy(() => import('@/features/administration/DemandesInscriptionPage').then((m) => ({ default: m.DemandesInscriptionPage })))
const DemandesChangementEtatPage = lazy(() => import('@/features/demandesChangementEtat/DemandesChangementEtatPage').then((m) => ({ default: m.DemandesChangementEtatPage })))

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<VitrineRedirect />} />
        <Route path="/login" element={<LoginPage />} />
        <Route
          element={
            <RequireAuth>
              <AppShell />
            </RequireAuth>
          }
        >
          <Route
            element={
              <ErrorBoundary>
                <Suspense fallback={<div className="mono p-6 text-sm text-[var(--color-faint)]">…</div>}>
                  <PageOutlet />
                </Suspense>
              </ErrorBoundary>
            }
          >
            <Route path="dashboard" element={<DashboardPage />} />
            <Route path="equipements" element={<EquipementsPage />} />
            <Route path="alertes" element={<AlertesPage />} />
            <Route path="incidents" element={<IncidentsPage />} />
            <Route path="predictions" element={<PredictionsPage />} />
            <Route path="assistant" element={<AssistantPage />} />
            <Route path="regles" element={<ReglesPage />} />
            <Route path="administration" element={<AdministrationPage />} />
            <Route
              path="demandes-inscription"
              element={
                <RequireRole roles={['SUPER_ADMIN', 'ADMIN']}>
                  <DemandesInscriptionPage />
                </RequireRole>
              }
            />
            <Route
              path="demandes-changement-etat"
              element={
                <RequireRole roles={['SUPER_ADMIN', 'ADMIN', 'TECHNICIEN']}>
                  <DemandesChangementEtatPage />
                </RequireRole>
              }
            />
          </Route>
        </Route>

        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  )
}

function VitrineRedirect() {
  useEffect(() => {
    window.location.replace('/vitrine/')
  }, [])

  return null
}

function PageOutlet() {
  return <Outlet />
}
