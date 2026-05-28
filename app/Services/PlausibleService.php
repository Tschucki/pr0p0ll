<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PlausibleService
{
    public function realtime(): int
    {
        return (int) $this->cached(
            key: 'plausible:realtime',
            ttl: 60,
            callback: fn () => $this->client()->get('realtime/visitors')->throw()->json()
        );
    }

    /**
     * @param  array<int, string>  $metrics
     * @return array<string, array{value: int|float}>
     */
    public function aggregates(string $period, array $metrics): array
    {
        $cacheKey = 'plausible:aggregates:'.$period.':'.implode(',', $metrics);

        return $this->cached(
            key: $cacheKey,
            ttl: (int) config('plausible.cache.duration', 180),
            callback: fn () => $this->client()->get('aggregate', [
                'period' => $period,
                'metrics' => implode(',', $metrics),
            ])->throw()->json('results', [])
        );
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl((string) config('plausible.base_url'))
            ->withToken((string) config('plausible.api_key'))
            ->withQueryParameters(['site_id' => (string) config('plausible.site_id')])
            ->timeout(150)
            ->acceptJson();
    }

    private function cached(string $key, int $ttl, \Closure $callback): mixed
    {
        if (config('plausible.cache.enabled', true) === false) {
            return $callback();
        }

        $store = Cache::store((string) config('plausible.cache.driver') ?: null);

        return $store->remember($key, $ttl, $callback);
    }
}
