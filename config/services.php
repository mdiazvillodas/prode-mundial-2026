<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'brevo' => [
        'api_key' => env('BREVO_API_KEY'),
        'transactional_from_email' => env('BREVO_TRANSACTIONAL_FROM_EMAIL', env('MAIL_FROM_ADDRESS')),
        'transactional_from_name' => env('BREVO_TRANSACTIONAL_FROM_NAME', env('MAIL_FROM_NAME', 'Mi Prode')),
        'api_timeout' => (int) env('BREVO_API_TIMEOUT', 10),
    ],

    'api_football' => [
        'base_url' => env('API_FOOTBALL_BASE_URL', 'https://v3.football.api-sports.io'),
        'key' => env('API_FOOTBALL_KEY'),
        'world_cup_league_id' => (int) env('API_FOOTBALL_WORLD_CUP_LEAGUE_ID', 1),
        'world_cup_season' => (int) env('API_FOOTBALL_WORLD_CUP_SEASON', 2026),
        'sync_health_warning_minutes' => (int) env('API_SYNC_HEALTH_WARNING_MINUTES', 15),
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

];
