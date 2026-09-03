<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
     * Bureau de Crédito externo. No desafio ele é simulado pela rota interna
     * GET /api/mock/bureau/{cpf}, então a aplicação chama a si mesma.
     *
     * Atenção: esta é a URL *interna* (de dentro do container), que não
     * coincide necessariamente com a APP_URL pública — no Sail a aplicação
     * escuta na porta 80 do container ainda que esteja publicada em outra
     * porta no host. Por isso o valor é explícito e não derivado da APP_URL.
     */
    'score_bureau' => [
        'url' => env('SCORE_BUREAU_API_URL', 'http://localhost/api/mock/bureau'),
        'timeout' => (int) env('SCORE_BUREAU_TIMEOUT', 3),
    ],

];
