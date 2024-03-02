<?php

declare(strict_types=1);

namespace App\Connectors;

use Illuminate\Support\Facades\Cache;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Drivers\LaravelCacheDriver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\Http\Connector;

class PlausibleConnector extends Connector implements Cacheable
{
    use HasCaching;

    public function __construct()
    {
        $this->withTokenAuth(token: config(key: 'plausible.api_key'));
    }

    public function resolveBaseUrl(): string
    {
        return config('plausible.base_url');
    }

    public function defaultConfig(): array
    {
        return [
            'timeout' => 150,
        ];
    }

    protected function defaultQuery(): array
    {
        return [
            'site_id' => config(key: 'plausible.site_id'),
        ];
    }

    public function resolveCacheDriver(): Driver
    {
        return new LaravelCacheDriver(
            store: Cache::store(config(key: 'plausible.cache.driver'))
        );
    }

    public function cacheExpiryInSeconds(): int
    {
        return config(key: 'plausible.cache.duration');
    }
}
