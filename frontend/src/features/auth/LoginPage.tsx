import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { isAxiosError } from 'axios'
import { useAuth } from './auth-context'
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
    <main
      className="relative min-h-screen overflow-hidden bg-[#f6fbff] text-[#08111c]"
      style={{
        background:
          'linear-gradient(135deg,#e5f3f9 0%,#ffffff 45%,#d8e8f0 100%)',
      }}
    >
      <div
        className="pointer-events-none absolute inset-0 opacity-90"
        style={{
          background:
            'radial-gradient(circle at 78% 20%,rgba(255,255,255,.96),transparent 25%),linear-gradient(135deg,transparent 0 14%,rgba(101,174,220,.18) 14% 15%,transparent 15% 32%,rgba(7,59,103,.10) 32% 33%,transparent 33%),repeating-linear-gradient(0deg,rgba(7,59,103,.08) 0 1px,transparent 1px 92px),repeating-linear-gradient(90deg,rgba(7,59,103,.08) 0 1px,transparent 1px 92px)',
        }}
      />

      <header className="relative z-10 mx-auto flex w-[min(1180px,calc(100%-28px))] items-center justify-between gap-4 px-4 py-5 sm:px-6">
        <a
          href="/vitrine/"
          className="inline-flex items-center gap-3 rounded-full bg-white/85 px-4 py-3 shadow-[0_14px_34px_rgba(7,59,103,.12)] ring-1 ring-[#d8e5ee] backdrop-blur"
        >
          <img src="/vitrine/assets/gpi-logo.svg" alt="Logo GPI" className="h-10 w-auto" />
          <span className="hidden text-sm font-black uppercase tracking-[0.16em] text-[#073b67] sm:inline">
            Application web
          </span>
        </a>

        <div className="flex items-center gap-3">
          <a
            href="/vitrine/"
            className="hidden rounded-full bg-white px-4 py-2 text-sm font-black text-[#073b67] shadow-sm ring-1 ring-[#d8e5ee] transition hover:-translate-y-0.5 hover:bg-[#dff2fb] sm:inline-flex"
          >
            Retour vitrine
          </a>
          <LanguageSwitcher />
        </div>
      </header>

      <section className="relative z-10 mx-auto grid w-[min(1180px,calc(100%-40px))] min-h-[calc(100vh-96px)] items-center gap-10 py-10 lg:grid-cols-[1.05fr_.95fr]">
        <aside className="hidden lg:block">
          <p className="mb-5 inline-flex items-center gap-3 text-xs font-black uppercase tracking-[0.16em] text-[#073b67] before:h-[3px] before:w-8 before:bg-[#d6df00] before:content-['']">
            Connexion sécurisée
          </p>
          <h1 className="max-w-2xl text-[clamp(44px,6vw,82px)] font-black leading-[0.95] tracking-[-0.06em] text-[#08111c]">
            Accédez à votre espace GPI.
          </h1>
          <p className="mt-6 max-w-xl text-[clamp(21px,2.4vw,32px)] font-extrabold leading-tight text-[#173d5b]">
            Gérez votre parc informatique depuis une interface claire, rapide et professionnelle.
          </p>
          <p className="mt-5 max-w-xl text-lg leading-8 text-[#526273]">
            Connectez-vous pour consulter les équipements, suivre les réclamations, traiter les anomalies et piloter les alertes de supervision.
          </p>

          <div className="mt-8 grid max-w-xl grid-cols-3 gap-3">
            {[
              ['3', 'Profils'],
              ['24/7', 'Suivi'],
              ['IA', 'Prévision'],
            ].map(([value, label]) => (
              <div key={label} className="rounded-[22px] bg-white p-5 shadow-[0_18px_44px_rgba(7,59,103,.13)] ring-1 ring-[#d8e5ee]">
                <strong className="block text-3xl font-black leading-none text-[#073b67]">{value}</strong>
                <span className="mt-2 block text-sm font-bold text-[#526273]">{label}</span>
              </div>
            ))}
          </div>
        </aside>

        <section className="mx-auto w-full max-w-[520px] rounded-[32px] bg-white/92 p-6 shadow-[0_24px_70px_rgba(7,59,103,.18)] ring-1 ring-[#d8e5ee] backdrop-blur sm:p-8">
          <div className="mb-7 flex items-start justify-between gap-5">
            <div>
              <p className="mb-3 inline-flex items-center gap-3 text-xs font-black uppercase tracking-[0.16em] text-[#073b67] before:h-[3px] before:w-7 before:bg-[#d6df00] before:content-['']">
                Espace utilisateur
              </p>
              <h2 className="text-3xl font-black tracking-[-0.04em] text-[#08111c] sm:text-4xl">
                Se connecter
              </h2>
              <p className="mt-3 text-sm font-semibold leading-6 text-[#526273]">
                Entrez vos identifiants pour accéder à l’application web GPI.
              </p>
            </div>
            <img src="/vitrine/assets/favicon.svg" alt="GPI" className="h-14 w-14 rounded-2xl" />
          </div>

          <form onSubmit={onSubmit} className="space-y-5">
            <div>
              <label className="mb-2 block text-sm font-black text-[#173d5b]" htmlFor="email">
                Adresse email
              </label>
              <input
                id="email"
                type="email"
                autoComplete="email"
                className="w-full rounded-2xl border border-[#d8e5ee] bg-[#fbfdff] px-4 py-4 text-[#08111c] outline-none transition placeholder:text-[#8aa0b2] focus:border-[#65aedc] focus:shadow-[0_0_0_4px_rgba(101,174,220,.18)]"
                placeholder="exemple@gpi.com"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
              />
            </div>

            <div>
              <label className="mb-2 block text-sm font-black text-[#173d5b]" htmlFor="password">
                Mot de passe
              </label>
              <input
                id="password"
                type="password"
                autoComplete="current-password"
                className="w-full rounded-2xl border border-[#d8e5ee] bg-[#fbfdff] px-4 py-4 text-[#08111c] outline-none transition placeholder:text-[#8aa0b2] focus:border-[#65aedc] focus:shadow-[0_0_0_4px_rgba(101,174,220,.18)]"
                placeholder="Votre mot de passe"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
            </div>

            {error && (
              <p className="rounded-2xl border border-[#f2b8b5] bg-[#fff3f2] px-4 py-3 text-sm font-bold leading-6 text-[#b42318]">
                {error}
              </p>
            )}

            <button
              type="submit"
              className="flex min-h-[54px] w-full items-center justify-center rounded-full bg-[#073b67] px-6 py-4 text-base font-black text-white shadow-[0_14px_28px_rgba(7,59,103,.24)] transition hover:-translate-y-0.5 hover:bg-[#0b4b7c] disabled:cursor-not-allowed disabled:opacity-70"
              disabled={busy}
            >
              {busy ? t('auth.submitting') : 'Se connecter à l’application'}
            </button>
          </form>

          <div className="mt-6 rounded-[24px] bg-[#f3fbff] p-5 ring-1 ring-[#d8e5ee]">
            <p className="text-sm font-bold leading-6 text-[#526273]">
              Vous n’avez pas encore de compte ?
            </p>
            <a
              href="/vitrine/#contact"
              className="mt-3 inline-flex w-full items-center justify-center rounded-full border border-[#073b67] bg-white px-5 py-3 text-sm font-black text-[#073b67] transition hover:bg-[#dff2fb]"
            >
              Demander une inscription
            </a>
          </div>
        </section>
      </section>
    </main>
  )
}
