<?php

declare(strict_types=1);

return [

    'name' => env('APP_NAME', 'Laravel'),

    'version' => 'v'.json_decode(file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR)['version'] ?? '?.?.?',

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    'timezone' => 'UTC',

    'locale' => 'de',

    'fallback_locale' => 'en',

    'faker_locale' => 'en_US',

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'maintenance' => [
        'driver' => 'file',
        // 'store' => 'redis',
    ],

];
