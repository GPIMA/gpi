<?php

namespace App\Http\Controllers;

use App\Enums\ExpediteurType;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Assistant d'aide informatique (cas d'utilisation « Poser des questions au
 * chatbot » / « Obtenir des réponses »).
 */
class ConversationController extends Controller
{
    /** Conversations de l'utilisateur connecté. */
    public function index(Request $request): JsonResponse
    {
        $conversations = Conversation::where('user_id', $request->user()->id)
            ->with('messages')
            ->orderByDesc('date_debut')
            ->get();

        return response()->json(['data' => ConversationResource::collection($conversations)]);
    }

    public function show(Request $request, Conversation $conversation): ConversationResource
    {
        abort_unless($conversation->user_id === $request->user()->id, 403);

        return new ConversationResource($conversation->load('messages'));
    }

    /**
     * Pose une question : crée/charge la conversation, enregistre le message de
     * l'utilisateur, génère et enregistre la réponse de l'assistant.
     */
    public function envoyer(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $data = $request->validate([
            'conversationId' => ['nullable', 'integer', 'exists:conversations,id'],
            'contenu' => ['required', 'string', 'max:1000'],
        ]);

        $conversation = $this->resoudreConversation($request, $data);

        $messageUtilisateur = $conversation->ajouterMessage($data['contenu'], ExpediteurType::UTILISATEUR);
        $reponse = $chatbot->repondre($conversation->fresh('messages'), $data['contenu']);
        $messageBot = $conversation->ajouterMessage($reponse, ExpediteurType::CHATBOT);

        return response()->json([
            'conversation' => new ConversationResource($conversation),
            'messages' => MessageResource::collection([$messageUtilisateur, $messageBot]),
        ], 201);
    }

    private function resoudreConversation(Request $request, array $data): Conversation
    {
        if (! empty($data['conversationId'])) {
            $conversation = Conversation::findOrFail($data['conversationId']);
            abort_unless($conversation->user_id === $request->user()->id, 403);

            return $conversation;
        }

        return Conversation::create([
            'user_id' => $request->user()->id,
            'titre' => Str::limit($data['contenu'], 50),
            'date_debut' => now(),
        ]);
    }
}
