import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { BrandMark } from '@/components/BrandMark'
import { Icons } from '@/components/icons'
import { ChatMessages } from './ChatMessages'
import { useAssistantStatut } from './api'
import { useChatController } from './useChatController'

/**
 * Widget de chat flottant, épinglé en bas à droite de l'écran et visible sur
 * toutes les pages de l'application (monté dans AppShell). Complète la page
 * Assistant dédiée : accès rapide sans quitter la page en cours.
 */
export function ChatWidget() {
  const { t } = useTranslation()
  const [open, setOpen] = useState(false)
  const { conversations, activeId, setActiveId, messages, draft, setDraft, send, onSend, scrollRef } =
    useChatController()
  const { data: statut } = useAssistantStatut()

  return (
    <div className="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3">
      {open && (
        <section
          className="panel flex h-[30rem] w-[22rem] max-w-[calc(100vw-2.5rem)] flex-col"
          style={{ boxShadow: '0 24px 70px rgba(7,59,103,.22)' }}
        >
          <div className="panel-head">
            <div className="flex min-w-0 items-center gap-2.5">
              <BrandMark size={24} />
              <div className="min-w-0 leading-none">
                <p className="panel-title truncate">{t('assistant.title')}</p>
                {statut && (
                  <p
                    className="mono mt-1 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider"
                    style={{ color: statut.ia ? 'var(--color-online, #1a9c6a)' : 'var(--color-faint)' }}
                  >
                    <span
                      className="dot"
                      style={{ background: statut.ia ? 'var(--color-online, #1a9c6a)' : 'var(--color-faint)' }}
                    />
                    {statut.ia ? t('assistant.iaActive') : t('assistant.iaHorsLigne')}
                  </p>
                )}
              </div>
            </div>
            <div className="flex items-center gap-1">
              <button
                className="btn-ghost flex h-8 w-8 items-center justify-center rounded-full"
                onClick={() => setActiveId(null)}
                title={t('assistant.new')}
                aria-label={t('assistant.new')}
              >
                <Icons.plus size={16} />
              </button>
              <button
                className="btn-ghost flex h-8 w-8 items-center justify-center rounded-full"
                onClick={() => setOpen(false)}
                title={t('assistant.close')}
                aria-label={t('assistant.close')}
              >
                <Icons.close size={16} />
              </button>
            </div>
          </div>

          {conversations && conversations.length > 0 && (
            <select
              className="input mx-3 mt-3 !py-1.5 text-xs"
              value={activeId ?? ''}
              onChange={(e) => setActiveId(e.target.value ? Number(e.target.value) : null)}
            >
              <option value="">{t('assistant.new')}</option>
              {conversations.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.titre ?? `#${c.id}`}
                </option>
              ))}
            </select>
          )}

          <ChatMessages messages={messages} isPending={send.isPending} scrollRef={scrollRef} compact />

          <form onSubmit={onSend} className="flex items-center gap-2 border-t border-[var(--color-line)] p-2.5">
            <input
              className="input flex-1 !py-2 text-sm"
              placeholder={t('assistant.placeholder')}
              value={draft}
              onChange={(e) => setDraft(e.target.value)}
              autoFocus
            />
            <button
              type="submit"
              className="btn btn-primary !px-3"
              disabled={send.isPending || !draft.trim()}
              aria-label={t('assistant.send')}
            >
              <Icons.send size={15} />
            </button>
          </form>
        </section>
      )}

      <button
        onClick={() => setOpen((v) => !v)}
        aria-label={open ? t('assistant.close') : t('assistant.open')}
        title={statut ? (statut.ia ? t('assistant.iaActive') : t('assistant.iaHorsLigne')) : undefined}
        className="relative flex h-14 w-14 items-center justify-center rounded-full transition-transform hover:scale-105 active:scale-95"
        style={{
          background: 'var(--color-brand)',
          color: 'var(--color-on-brand)',
          boxShadow: '0 18px 40px rgba(7,59,103,.32)',
        }}
      >
        {open ? <Icons.close size={22} /> : <Icons.chatbot size={22} />}
        {!open && statut && (
          <span
            className="absolute -right-0.5 -top-0.5 h-3.5 w-3.5 rounded-full"
            style={{
              background: statut.ia ? 'var(--color-online)' : 'var(--color-faint)',
              border: '2px solid var(--color-raised)',
            }}
          />
        )}
      </button>
    </div>
  )
}
