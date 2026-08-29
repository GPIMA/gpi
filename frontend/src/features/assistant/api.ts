import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { api } from '@/lib/api/client'
import type { ChatMessage, Conversation } from '@/lib/api/types'

/** Le moteur actif est-il une vraie IA (clé API configurée) ou le mode hors-ligne ? */
export function useAssistantStatut() {
  return useQuery({
    queryKey: ['assistant-statut'],
    queryFn: async () => (await api.get<{ ia: boolean; moteur: string }>('/assistant/statut')).data,
    staleTime: 60 * 1000,
    refetchOnMount: 'always',
  })
}

export function useConversations() {
  return useQuery({
    queryKey: ['conversations'],
    queryFn: async () => (await api.get<{ data: Conversation[] }>('/assistant/conversations')).data.data,
  })
}

export function useConversation(id: number | null) {
  return useQuery({
    queryKey: ['conversation', id],
    enabled: id != null,
    queryFn: async () => (await api.get<{ data: Conversation }>(`/assistant/conversations/${id}`)).data.data,
  })
}

export function useSendMessage() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: async ({ conversationId, contenu }: { conversationId: number | null; contenu: string }) =>
      (
        await api.post<{ conversation: Conversation; messages: ChatMessage[] }>('/assistant/message', {
          conversationId: conversationId ?? undefined,
          contenu,
        })
      ).data,
    onSuccess: (res) => {
      qc.invalidateQueries({ queryKey: ['conversations'] })
      qc.invalidateQueries({ queryKey: ['conversation', res.conversation.id] })
    },
  })
}
