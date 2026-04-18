<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class SettingsService
{
    private const CACHE_KEY = 'settings.all';

    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
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
}
