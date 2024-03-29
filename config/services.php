<?php

declare(strict_types=1);

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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'pr0gramm' => [
        'client_id' => env('PR0GRAMM_CLIENT_ID'),
        'client_secret' => env('PR0GRAMM_CLIENT_SECRET'),
        'redirect' => env('PR0GRAMM_REDIRECT_URI'),
        'username' => env('PR0GRAMM_USERNAME'),
        'password' => env('PR0GRAMM_PASSWORD'),
    ],

    'telegram-bot-api' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
        'channel' => env('TELEGRAM_CHANNEL_CHAT_ID'),
    ],

    'discord' => [
        'token' => env('DISCORD_BOT_TOKEN'),
        'channel_id' => env('DISCORD_CHANNEL_ID'),
    ],

];
