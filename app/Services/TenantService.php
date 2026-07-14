<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantService
{
    protected static ?Tenant $current = null;

    public static function set(?Tenant $tenant): void
    {
        static::$current = $tenant;
    }

    public static function current(): ?Tenant
    {
        return static::$current;
    }

    public static function id(): ?int
    {
        return static::$current?->id;
    }

    public static function resolveFromHost(string $host): ?Tenant
    {
        $platformDomain = config('app.platform_domain');

        if ($host === $platformDomain || $host === 'www.'.$platformDomain || $host === 'localhost' || str_starts_with($host, '127.0.0.1')) {
            return null;
        }

        if (str_ends_with($host, '.'.$platformDomain)) {
            $subdomain = str_replace('.'.$platformDomain, '', $host);

            return Cache::remember("tenant:subdomain:{$subdomain}", 3600, function () use ($subdomain) {
                return Tenant::where('subdomain', $subdomain)->where('is_active', true)->first();
            });
        }

        return Cache::remember("tenant:domain:{$host}", 3600, function () use ($host) {
            return Tenant::where('custom_domain', $host)->where('is_active', true)->first();
        });
    }
}
