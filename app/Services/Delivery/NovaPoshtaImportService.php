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
        foreach ($this->request('Address', 'getWarehouses') as $branchData) {
            $branchRef = $this->stringValue($branchData, ['Ref']);
            $cityRef = $this->stringValue($branchData, ['CityRef', 'SettlementRef']);
            $branchNumber = $this->stringValue($branchData, ['Number', 'SiteKey']);
            $branchAddress = $this->stringValue($branchData, ['ShortAddress', 'Description', 'DescriptionRu']);

            $city = $cityRef ? ($citiesByRef[$cityRef] ?? null) : null;

            if (! $city || ! $branchNumber || ! $branchAddress) {
                $stats['skipped']++;
                continue;
            }

            $branchName = sprintf("\u{2116}%s, %s", $branchNumber, $branchAddress);

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

    protected function request(string $modelName, string $calledMethod, array $methodProperties = []): array
    {
        $response = Http::acceptJson()->post(config('services.nova_poshta.base_url'), [
            'apiKey' => config('services.nova_poshta.api_key'),
            'modelName' => $modelName,
            'calledMethod' => $calledMethod,
            'methodProperties' => $methodProperties,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("Nova Poshta API request failed for {$calledMethod}.");
        }

        $payload = $response->json();

        if (! is_array($payload) || ! ($payload['success'] ?? false)) {
            $errors = $payload['errors'] ?? ['Unknown Nova Poshta API error.'];

            throw new RuntimeException(implode(' ', $errors));
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
}