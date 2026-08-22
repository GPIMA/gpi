<?php

/*
|--------------------------------------------------------------------------
| Assistant (chatbot) d'aide informatique
|--------------------------------------------------------------------------
| driver = "rule"      : base de connaissances locale, hors-ligne (par défaut)
|        = "openai"    : API compatible OpenAI (Groq, OpenRouter, OpenAI…)
|        = "anthropic" : API Anthropic (Claude)
| Aucune clé en dur : tout vient de l'environnement.
*/

return [
    'driver' => env('CHATBOT_DRIVER', 'rule'),

    'openai' => [
        'base_url' => env('CHATBOT_BASE_URL', 'https://api.openai.com/v1'),
        'api_key' => env('CHATBOT_API_KEY'),
        'model' => env('CHATBOT_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('CHATBOT_TIMEOUT', 20),
    ],

    'anthropic' => [
        'base_url' => env('CHATBOT_ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
        'api_key' => env('CHATBOT_ANTHROPIC_API_KEY'),
        'model' => env('CHATBOT_ANTHROPIC_MODEL', 'claude-sonnet-4-5'),
        'max_tokens' => (int) env('CHATBOT_ANTHROPIC_MAX_TOKENS', 1024),
        'timeout' => (int) env('CHATBOT_ANTHROPIC_TIMEOUT', 20),
        'version' => env('CHATBOT_ANTHROPIC_VERSION', '2023-06-01'),
    ],

    // Invite système : cadre le rôle de l'assistant (support IT interne).
    'system_prompt' => "Tu es l'assistant d'aide informatique interne de l'entreprise. "
        ."Tu réponds de façon concise, professionnelle et bienveillante, en français par défaut "
        ."(ou dans la langue de l'utilisateur). Tu aides sur les pannes courantes : mots de passe, "
        ."réseau, imprimantes, lenteurs, e-mail, matériel. Si un problème nécessite une intervention, "
        ."invite l'utilisateur à signaler un incident depuis l'application.",
];
