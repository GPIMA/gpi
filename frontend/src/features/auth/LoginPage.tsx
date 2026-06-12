import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { isAxiosError } from 'axios'
import { useAuth } from './auth-context'
import { BrandMark } from '@/components/BrandMark'
import { NetworkBackdrop } from '@/components/NetworkBackdrop'
import { LanguageSwitcher } from '@/components/LanguageSwitcher'

export function LoginPage() {
  const { t } = useTranslation()
  const { login } = useAuth()
  const navigate = useNavigate()

  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState<string | null>(null)
  const [busy, setBusy] = useState(false)

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    setError(null)
    setBusy(true)
    try {
      await login(email, password)
      navigate('/', { replace: true })
    } catch (err) {
      const apiMessage = isAxiosError(err)
        ? (err.response?.data?.errors?.email?.[0] ?? err.response?.data?.message)
        : undefined
      setError(apiMessage ?? t('auth.error'))
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="grid min-h-screen lg:grid-cols-[1.1fr_1fr]">
      {/* Left — brand / context panel */}
      <aside className="app-canvas relative hidden flex-col justify-between overflow-hidden border-r border-[var(--color-line)] p-12 lg:flex">
        <NetworkBackdrop className="pointer-events-none absolute -bottom-12 -right-16 z-0 h-[460px] w-[760px] opacity-50" />
        <div className="relative z-10 flex items-center gap-3">
          <BrandMark size={40} />
          <div className="leading-none">
            <div className="font-[var(--font-display)] text-xl font-semibold tracking-tight">{t('app.name')}</div>
            <div className="mt-1 text-[10px] uppercase tracking-[0.14em] text-[var(--color-faint)]">{t('app.full')}</div>
          </div>
        </div>

        <div className="relative z-10 max-w-md">
          <p className="eyebrow mb-4">Console de supervision</p>
          <h1 className="font-[var(--font-display)] text-4xl leading-[1.05]">{t('app.tagline')}</h1>
          <div className="mt-8 flex flex-wrap gap-2">
            {['SNMP', 'Métriques', 'Alertes', 'Prédiction IA'].map((tag) => (
              <span key={tag} className="pill">
                {tag}
              </span>
            ))}
          </div>
        </div>

        <div className="relative z-10 flex items-center gap-2 text-[var(--color-faint)]">
          <span className="dot" style={{ background: 'var(--color-online)' }} />
          <span className="mono text-xs">api · {t('common.online')}</span>
        </div>
      </aside>

      {/* Right — form */}
      <main className="flex flex-col">
        <div className="flex items-center justify-between p-6">
          <div className="flex items-center gap-2 lg:hidden">
            <BrandMark />
            <span className="font-semibold">{t('app.name')}</span>
          </div>
          <div className="ml-auto">
            <LanguageSwitcher />
          </div>
        </div>

        <div className="flex flex-1 items-center justify-center px-6 pb-16">
          <form onSubmit={onSubmit} className="w-full max-w-sm">
            <h2 className="mb-1 text-2xl">{t('auth.title')}</h2>
            <p className="mb-8 text-[var(--color-muted)]">{t('auth.subtitle')}</p>

            <div className="mb-4">
              <label className="field-label" htmlFor="email">
                {t('auth.email')}
              </label>
              <input
                id="email"
                type="email"
                autoComplete="email"
                className="input"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
              />
            </div>

            <div className="mb-6">
              <label className="field-label" htmlFor="password">
                {t('auth.password')}
              </label>
              <input
                id="password"
                type="password"
                autoComplete="current-password"
                className="input"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
            </div>

            {error && (
              <p
                className="mb-4 rounded-[5px] border px-3 py-2 text-sm"
                style={{
                  borderColor: 'rgba(255,93,87,0.35)',
                  background: 'rgba(255,93,87,0.08)',
                  color: '#ff8983',
                }}
              >
                {error}
              </p>
            )}

            <button type="submit" className="btn btn-primary w-full" disabled={busy}>
              {busy ? t('auth.submitting') : t('auth.submit')}
            </button>
          </form>
        </div>
      </main>
    </div>
  )
}
