import { useTranslation } from 'react-i18next'
import { BrandMark } from '@/components/BrandMark'
import type { ChatMessage } from '@/lib/api/types'

/** Liste des messages d'une conversation, avec état vide et indicateur « en train d'écrire ». */
export function ChatMessages({
  messages,
  isPending,
  scrollRef,
  compact = false,
}: {
  messages: ChatMessage[]
  isPending: boolean
  scrollRef: React.RefObject<HTMLDivElement | null>
  compact?: boolean
}) {
  const { t, i18n } = useTranslation()

  return (
    <div ref={scrollRef} className={`flex-1 space-y-3 overflow-y-auto ${compact ? 'p-3' : 'space-y-4 p-5'}`}>
      {messages.length === 0 && (
        <div className="flex h-full flex-col items-center justify-center gap-3 text-center">
          <BrandMark size={compact ? 26 : 32} />
          <p className="max-w-sm text-sm text-[var(--color-muted)]">{t('assistant.welcome')}</p>
          {!compact && <p className="mono text-[11px] text-[var(--color-faint)]">{t('assistant.examples')}</p>}
        </div>
      )}
      {messages.map((m) => (
        <div key={m.id} className={`flex ${m.estChatbot ? 'justify-start' : 'justify-end'}`}>
          <div
            className={`max-w-[85%] whitespace-pre-wrap rounded-[8px] py-2.5 text-sm ${compact ? 'px-3 text-[13px]' : 'px-3.5'}`}
            style={
              m.estChatbot
                ? { background: 'var(--color-raised)', border: '1px solid var(--color-line)' }
                : { background: 'var(--color-brand)', color: 'var(--color-on-brand)' }
            }
          >
            {m.contenu}
            <div className="mono mt-1 text-[10px]" style={{ color: m.estChatbot ? 'var(--color-faint)' : 'rgba(28,22,2,0.6)' }}>
              {new Date(m.dateEnvoi).toLocaleTimeString(i18n.language, { hour: '2-digit', minute: '2-digit' })}
            </div>
          </div>
        </div>
      ))}
      {isPending && (
        <div className="flex justify-start">
          <div className="rounded-[8px] border border-[var(--color-line)] bg-[var(--color-raised)] px-3.5 py-2.5 text-sm text-[var(--color-faint)]">
            {t('assistant.thinking')}
          </div>
        </div>
      )}
    </div>
  )
}
