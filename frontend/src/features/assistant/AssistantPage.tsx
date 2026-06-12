import { useEffect, useRef, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { PageHeader } from '@/components/PageHeader'
import { BrandMark } from '@/components/BrandMark'
import { Icons } from '@/components/icons'
import { useConversation, useConversations, useSendMessage } from './api'

export function AssistantPage() {
  const { t, i18n } = useTranslation()
  const { data: conversations } = useConversations()
  const [activeId, setActiveId] = useState<number | null>(null)
  const { data: active } = useConversation(activeId)
  const send = useSendMessage()

  const [draft, setDraft] = useState('')
  const scrollRef = useRef<HTMLDivElement>(null)

  const messages = active?.messages ?? []

  useEffect(() => {
    scrollRef.current?.scrollTo({ top: scrollRef.current.scrollHeight, behavior: 'smooth' })
  }, [messages.length, send.isPending])

  async function onSend(e: React.FormEvent) {
    e.preventDefault()
    const contenu = draft.trim()
    if (!contenu) return
    setDraft('')
    const res = await send.mutateAsync({ conversationId: activeId, contenu })
    if (activeId == null) setActiveId(res.conversation.id)
  }

  return (
    <>
      <PageHeader eyebrow={t('app.name')} title={t('assistant.title')} subtitle={t('assistant.subtitle')} />

      <div className="grid gap-4 lg:grid-cols-[16rem_1fr]">
        {/* Conversations */}
        <section className="panel flex max-h-[34rem] flex-col">
          <div className="p-3">
            <button className="btn btn-primary w-full" onClick={() => setActiveId(null)}>
              <Icons.plus size={16} />
              {t('assistant.new')}
            </button>
          </div>
          <div className="flex-1 overflow-y-auto border-t border-[var(--color-line)]">
            {conversations?.length === 0 && (
              <p className="p-4 text-center text-sm text-[var(--color-faint)]">{t('assistant.noConversations')}</p>
            )}
            {conversations?.map((c) => (
              <button
                key={c.id}
                onClick={() => setActiveId(c.id)}
                className="block w-full truncate px-4 py-3 text-left text-sm transition-colors"
                style={{
                  background: activeId === c.id ? 'var(--color-overlay)' : 'transparent',
                  color: activeId === c.id ? 'var(--color-ink)' : 'var(--color-muted)',
                  boxShadow: activeId === c.id ? 'inset 2px 0 0 var(--color-brand)' : 'none',
                }}
              >
                {c.titre ?? `#${c.id}`}
              </button>
            ))}
          </div>
        </section>

        {/* Chat */}
        <section className="panel flex max-h-[34rem] flex-col">
          <div ref={scrollRef} className="flex-1 space-y-4 overflow-y-auto p-5">
            {messages.length === 0 && (
              <div className="flex h-full flex-col items-center justify-center gap-3 text-center">
                <BrandMark size={32} />
                <p className="max-w-sm text-sm text-[var(--color-muted)]">{t('assistant.welcome')}</p>
                <p className="mono text-[11px] text-[var(--color-faint)]">{t('assistant.examples')}</p>
              </div>
            )}
            {messages.map((m) => (
              <div key={m.id} className={`flex ${m.estChatbot ? 'justify-start' : 'justify-end'}`}>
                <div
                  className="max-w-[80%] whitespace-pre-wrap rounded-[8px] px-3.5 py-2.5 text-sm"
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
            {send.isPending && (
              <div className="flex justify-start">
                <div className="rounded-[8px] border border-[var(--color-line)] bg-[var(--color-raised)] px-3.5 py-2.5 text-sm text-[var(--color-faint)]">
                  {t('assistant.thinking')}
                </div>
              </div>
            )}
          </div>

          <form onSubmit={onSend} className="flex items-center gap-2 border-t border-[var(--color-line)] p-3">
            <input
              className="input flex-1"
              placeholder={t('assistant.placeholder')}
              value={draft}
              onChange={(e) => setDraft(e.target.value)}
            />
            <button type="submit" className="btn btn-primary" disabled={send.isPending || !draft.trim()}>
              <Icons.send size={16} />
              {t('assistant.send')}
            </button>
          </form>
        </section>
      </div>
    </>
  )
}
