<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class SettingsService
{
    private const CACHE_KEY = 'settings.all';
    private const ADMIN_PER_PAGE_FALLBACK = 20;

    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            if (! Schema::hasTable('settings')) {
                return [];
            }

            return Setting::query()
                ->get()
                ->mapWithKeys(function (Setting $setting): array {
                    return [
                        "{$setting->group}.{$setting->key}" => $this->castValue(
                            $setting->value,
                            $setting->type
                        ),
                    ];
                })
                ->all();
        });
    }

    public function get(string $fullKey, mixed $default = null): mixed
    {
        return $this->all()[$fullKey] ?? $default;
    }

    public function set(string $fullKey, mixed $value, ?string $type = null): void
    {
        [$group, $key] = $this->parseKey($fullKey);

        $resolvedType = $type ?? $this->detectType($value);

        Setting::query()->updateOrCreate(
            [
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => $this->prepareValue($value, $resolvedType),
                'type' => $resolvedType,
            ]
        );

        $this->forgetCache();
    }

    public function storeName(): string
    {
        $value = trim((string) $this->get('general.store_name', ''));

        return $value !== '' ? $value : (string) config('app.name');
    }

    public function guestCheckoutEnabled(): bool
    {
        return (bool) $this->get('checkout.guest_checkout_enabled', true);
    }

    public function adminProductsPerPage(): int
    {
        return $this->normalizePositiveInteger(
            $this->get('admin.products_per_page'),
            self::ADMIN_PER_PAGE_FALLBACK
        );
    }

    public function adminOrdersPerPage(): int
    {
        return $this->normalizePositiveInteger(
            $this->get('admin.orders_per_page'),
            self::ADMIN_PER_PAGE_FALLBACK
        );
    }

    public function adminCustomersPerPage(): int
    {
        return $this->normalizePositiveInteger(
            $this->get('admin.customers_per_page'),
            self::ADMIN_PER_PAGE_FALLBACK
        );
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function parseKey(string $fullKey): array
    {
        $parts = explode('.', $fullKey, 2);

        if (count($parts) !== 2 || blank($parts[0]) || blank($parts[1])) {
            throw new InvalidArgumentException("Invalid settings key [{$fullKey}]");
        }

        return [$parts[0], $parts[1]];
    }

    private function detectType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => Setting::TYPE_BOOLEAN,
            is_int($value) => Setting::TYPE_INTEGER,
            is_array($value) => Setting::TYPE_JSON,
            default => Setting::TYPE_STRING,
        };
    }

    private function prepareValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            Setting::TYPE_BOOLEAN => $value ? '1' : '0',
            Setting::TYPE_INTEGER => (string) $value,
            Setting::TYPE_JSON => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            Setting::TYPE_STRING => (string) $value,
            default => throw new InvalidArgumentException("Unsupported settings type [{$type}]"),
        };
    }

    private function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            Setting::TYPE_BOOLEAN => $value === '1',
            Setting::TYPE_INTEGER => (int) $value,
            Setting::TYPE_JSON => json_decode($value, true),
            Setting::TYPE_STRING => $value,
            default => $value,
        };
    }

    private function normalizePositiveInteger(mixed $value, int $default): int
    {
        if (! is_numeric($value)) {
            return $default;
        }

        $normalized = (int) $value;

        return $normalized > 0 ? $normalized : $default;
    }
}
