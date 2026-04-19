<?php

namespace App\Services\Delivery;

use App\Models\DeliveryBranch;
use App\Models\DeliveryCity;
use App\Models\DeliveryRegion;
use App\Models\DeliveryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class NovaPoshtaImportService
{
    protected const WAREHOUSES_PAGE_LIMIT = 500;

    protected const MAX_WAREHOUSE_PAGES = 1000;

    public function sync(): array
    {
        $apiKey = trim((string) config('services.nova_poshta.api_key'));

        if ($apiKey === '') {
            throw new RuntimeException('NOVA_POSHTA_API_KEY is not configured.');
        }

        $service = DeliveryService::firstOrCreate(
            ['code' => 'nova_poshta'],
            [
                'name' => 'Нова пошта',
                'is_active' => true,
            ]
        );

        if (! $service->is_active || $service->name !== 'Нова пошта') {
            $service->forceFill([
                'name' => 'Нова пошта',
                'is_active' => true,
            ])->save();
        }

        $stats = [
            'skipped' => 0,
            'regions_created' => 0,
            'regions_updated' => 0,
            'cities_created' => 0,
            'cities_updated' => 0,
            'branches_created' => 0,
            'branches_updated' => 0,
        ];

        $regionsByRef = $this->syncRegions($service, $stats);
        $citiesByRef = $this->syncCities($service, $regionsByRef, $stats);
        $this->syncBranches($citiesByRef, $stats);

        return $stats;
    }

    protected function syncRegions(DeliveryService $service, array &$stats): array
    {
        $regionsByRef = [];

        foreach ($this->request('Address', 'getAreas') as $regionData) {
            $regionRef = $this->stringValue($regionData, ['Ref']);
            $regionName = $this->stringValue($regionData, ['Description', 'DescriptionRu']);

            if (! $regionName) {
                $stats['skipped']++;
                continue;
            }

            $region = $this->firstByExternalOrName(
                DeliveryRegion::query()->where('delivery_service_id', $service->id),
                $regionRef,
                $regionName
            );

            $created = ! $region->exists;

            $region->delivery_service_id = $service->id;
            $region->name = $regionName;
            $region->external_id = $regionRef;

            if ($created) {
                $region->save();
                $stats['regions_created']++;
            } elseif ($region->isDirty()) {
                $region->save();
                $stats['regions_updated']++;
            }

            if ($regionRef) {
                $regionsByRef[$regionRef] = $region;
            }
        }

        return $regionsByRef;
    }

    protected function syncCities(DeliveryService $service, array $regionsByRef, array &$stats): array
    {
        $citiesByRef = [];

        foreach ($this->request('Address', 'getCities') as $cityData) {
            $cityRef = $this->stringValue($cityData, ['Ref']);
            $cityName = $this->stringValue($cityData, ['Description', 'Present', 'DescriptionRu']);
            $regionRef = $this->stringValue($cityData, ['Area', 'AreaRef', 'SettlementAreaRef']);
            $regionName = $this->stringValue($cityData, ['AreaDescription', 'AreaDescriptionRu']);

            $region = $regionRef ? ($regionsByRef[$regionRef] ?? null) : null;

            if (! $region && $regionName) {
                $region = DeliveryRegion::query()
                    ->where('delivery_service_id', $service->id)
                    ->where('name', $regionName)
                    ->first();
            }

            if (! $region || ! $cityName) {
                $stats['skipped']++;
                continue;
            }

            $city = $this->firstByExternalOrName(
                DeliveryCity::query()->where('delivery_region_id', $region->id),
                $cityRef,
                $cityName
            );

            $created = ! $city->exists;

            $city->delivery_region_id = $region->id;
            $city->name = $cityName;
            $city->external_id = $cityRef;

            if ($created) {
                $city->save();
                $stats['cities_created']++;
            } elseif ($city->isDirty()) {
                $city->save();
                $stats['cities_updated']++;
            }

            if ($cityRef) {
                $citiesByRef[$cityRef] = $city;
            }
        }

        return $citiesByRef;
    }

    protected function syncBranches(array $citiesByRef, array &$stats): void
    {
        for ($page = 1; $page <= self::MAX_WAREHOUSE_PAGES; $page++) {
            $branchPage = $this->request('Address', 'getWarehouses', [
                'Page' => $page,
                'Limit' => self::WAREHOUSES_PAGE_LIMIT,
            ]);

            if ($branchPage === []) {
                return;
            }

            foreach ($branchPage as $branchData) {
                $branchRef = $this->stringValue($branchData, ['Ref']);
                $cityRef = $this->stringValue($branchData, ['CityRef', 'SettlementRef']);
                $branchNumber = $this->stringValue($branchData, ['Number', 'SiteKey']);
                $branchAddress = $this->stringValue($branchData, ['ShortAddress', 'ShortAddressRu', 'Description', 'DescriptionRu']);
                $branchType = $this->resolveBranchType($branchData);

                $city = $cityRef ? ($citiesByRef[$cityRef] ?? null) : null;

                if (! $city || ! $branchNumber || ! $branchAddress) {
                    $stats['skipped']++;
                    continue;
                }

                $branchName = $this->buildBranchDisplayName(
                    $branchType,
                    $branchNumber,
                    $branchAddress,
                    $this->extractWeightSuffix($branchData)
                );

                $branch = DeliveryBranch::query()
                    ->where('delivery_city_id', $city->id)
                    ->when(
                        $branchRef,
                        fn (Builder $query) => $query->where('external_id', $branchRef),
                        fn (Builder $query) => $query->where('name', $branchName)
                    )
                    ->first() ?? new DeliveryBranch();

                $created = ! $branch->exists;

                $branch->delivery_city_id = $city->id;
                $branch->name = $branchName;
                $branch->address = $branchAddress;
                $branch->postal_code = null;
                $branch->type = $branchType;
                $branch->external_id = $branchRef;
                $branch->is_active = true;

                if ($created) {
                    $branch->save();
                    $stats['branches_created']++;
                } elseif ($branch->isDirty()) {
                    $branch->save();
                    $stats['branches_updated']++;
                }
            }
        }

        throw new RuntimeException('Nova Poshta warehouses sync exceeded the maximum page limit.');
    }

    protected function request(string $modelName, string $calledMethod, array $methodProperties = []): array
    {
        $payload = [
            'apiKey' => config('services.nova_poshta.api_key'),
            'modelName' => $modelName,
            'calledMethod' => $calledMethod,
        ];

        if ($methodProperties !== []) {
            $payload['methodProperties'] = $methodProperties;
        }

        $response = Http::acceptJson()->post(config('services.nova_poshta.base_url'), $payload);

        if (! $response->successful()) {
            throw new RuntimeException("Nova Poshta API request failed for {$calledMethod}.");
        }

        $payload = $response->json();

        if (! is_array($payload) || ! ($payload['success'] ?? false)) {
            $messages = array_filter([
                ...($payload['errors'] ?? []),
                ...($payload['warnings'] ?? []),
                ...($payload['info'] ?? []),
            ]);

            throw new RuntimeException(implode(' ', $messages ?: ['Unknown Nova Poshta API error.']));
        }

        return is_array($payload['data'] ?? null) ? $payload['data'] : [];
    }

    protected function firstByExternalOrName(Builder $query, ?string $externalId, string $name): DeliveryRegion|DeliveryCity
    {
        if ($externalId) {
            $existing = (clone $query)->where('external_id', $externalId)->first();

            if ($existing) {
                return $existing;
            }
        }

        return (clone $query)->where('name', $name)->first() ?? $query->getModel()->newInstance();
    }

    protected function stringValue(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $payload[$key] ?? null;

            if ($value === null) {
                continue;
            }

            $value = preg_replace('/\s+/u', ' ', trim((string) $value)) ?? trim((string) $value);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function resolveBranchType(array $branchData): string
    {
        $category = $this->stringValue($branchData, ['CategoryOfWarehouse']);
        $description = $this->stringValue($branchData, ['Description', 'DescriptionRu']);

        if ($category === 'Postomat') {
            return 'parcel_locker';
        }

        if ($category === 'Store') {
            return 'pickup_point';
        }

        $description = $description ? mb_strtolower($description) : null;

        if ($description && str_contains($description, 'поштомат')) {
            return 'parcel_locker';
        }

        if ($description && str_contains($description, 'пункт')) {
            return 'pickup_point';
        }

        return 'branch';
    }

    protected function buildBranchDisplayName(
        string $branchType,
        string $branchNumber,
        string $branchAddress,
        ?string $weightSuffix
    ): string
    {
        $weight = $weightSuffix ? " {$weightSuffix}" : '';

        return match ($branchType) {
            'parcel_locker' => "Поштомат \"Нова Пошта\" №{$branchNumber}: {$branchAddress}",
            'pickup_point' => "Пункт №{$branchNumber}{$weight}: {$branchAddress}",
            default => "Відділення №{$branchNumber}{$weight}: {$branchAddress}",
        };
    }

    protected function extractWeightSuffix(array $branchData): ?string
    {
        foreach (['Description', 'DescriptionRu'] as $field) {
            $description = $this->stringValue($branchData, [$field]);

            if (! $description) {
                continue;
            }

            if (preg_match('/\((до\s+\d+\s*кг)\)/ui', $description, $matches) === 1) {
                $normalized = preg_replace('/\s+/u', ' ', trim($matches[1])) ?? trim($matches[1]);

                return "({$normalized})";
            }
        }

        return null;
    }
}
