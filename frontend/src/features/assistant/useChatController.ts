import { useEffect, useRef, useState } from 'react'
import { useConversation, useConversations, useSendMessage } from './api'

/**
 * Logique partagée entre la page Assistant (plein écran) et le widget de
 * chat flottant : conversation active, envoi de message, auto-scroll.
 */
export function useChatController() {
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

  return {
    conversations,
    activeId,
    setActiveId,
    messages,
    draft,
    setDraft,
    send,
    onSend,
    scrollRef,
  }
}
