<?php

declare(strict_types=1);

return [
    'beta_users' => explode(',', env('BETA_USERS', '')),

    // Browsershot/Puppeteer-Pfade für den Server-Screenshot (null = Auto-Detection; auf Production setzen).
    'chrome_path' => env('PR0P0LL_CHROME_PATH'),
    'node_binary' => env('PR0P0LL_NODE_BINARY'),
    'npm_binary' => env('PR0P0LL_NPM_BINARY'),
];
