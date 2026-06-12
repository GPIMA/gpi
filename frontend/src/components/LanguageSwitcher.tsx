import { useTranslation } from 'react-i18next'
import { SUPPORTED_LANGUAGES } from '@/lib/i18n'

export function LanguageSwitcher() {
  const { i18n } = useTranslation()
  const current = i18n.language.slice(0, 2)

  return (
    <div className="inline-flex items-center rounded-[5px] border border-[var(--color-line-strong)] p-0.5">
      {SUPPORTED_LANGUAGES.map((lng) => {
        const active = current === lng
        return (
          <button
            key={lng}
            onClick={() => i18n.changeLanguage(lng)}
            className="mono rounded-[3px] px-2 py-0.5 text-[11px] uppercase tracking-wider transition-colors"
            style={{
              background: active ? 'var(--color-overlay)' : 'transparent',
              color: active ? 'var(--color-ink)' : 'var(--color-faint)',
            }}
            aria-pressed={active}
          >
            {lng}
          </button>
        )
      })}
    </div>
  )
}
