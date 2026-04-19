<?php

use App\Models\DeliveryBranch;
use App\Models\DeliveryCity;
use App\Models\DeliveryRegion;
use App\Models\DeliveryService;
use App\Services\Delivery\NovaPoshtaImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function fakeNovaPoshtaApi(array $areas, array $cities, array $warehouses): void
{
    Http::fake(function (Request $request) use ($areas, $cities, $warehouses) {
        $payload = $request->data();
        $calledMethod = $payload['calledMethod'] ?? null;

        return match ($calledMethod) {
            'getAreas' => Http::response([
                'success' => true,
                'data' => $areas,
            ]),
            'getCities' => Http::response([
                'success' => true,
                'data' => $cities,
            ]),
            'getWarehouses' => Http::response([
                'success' => true,
                'data' => $warehouses,
            ]),
            default => Http::response([
                'success' => false,
                'errors' => ['Unexpected Nova Poshta method.'],
            ], 422),
        };
    });
}

it('syncs nova poshta regions cities and branches and stays idempotent', function () {
    config()->set('services.nova_poshta.api_key', 'test-key');

    fakeNovaPoshtaApi(
        areas: [
            ['Ref' => 'area-1', 'Description' => 'Kyivska'],
            ['Ref' => 'area-2', 'Description' => 'Lvivska'],
        ],
        cities: [
            ['Ref' => 'city-1', 'Description' => 'Kyiv', 'Area' => 'area-1'],
            ['Ref' => 'city-2', 'Description' => 'Boryspil', 'Area' => 'area-1'],
            ['Ref' => 'city-3', 'Description' => 'Lviv', 'Area' => 'area-2'],
        ],
        warehouses: [
            ['Ref' => 'wh-1', 'CityRef' => 'city-1', 'Number' => '12', 'ShortAddress' => 'Centralna 15'],
            ['Ref' => 'wh-2', 'CityRef' => 'city-3', 'Number' => '7', 'ShortAddress' => 'Svobody 1'],
            ['Ref' => 'wh-skip', 'CityRef' => 'city-1', 'Number' => '', 'ShortAddress' => 'Porozhnya 2'],
        ],
    );

    $service = app(NovaPoshtaImportService::class);

    $firstRun = $service->sync();

    expect($firstRun['skipped'])->toBe(1);
    expect($firstRun['regions_created'])->toBe(2);
    expect($firstRun['regions_updated'])->toBe(0);
    expect($firstRun['cities_created'])->toBe(3);
    expect($firstRun['cities_updated'])->toBe(0);
    expect($firstRun['branches_created'])->toBe(2);
    expect($firstRun['branches_updated'])->toBe(0);

    expect(DeliveryService::query()->where('code', 'nova_poshta')->count())->toBe(1);
    expect(DeliveryService::query()->where('code', 'nova_poshta')->value('name'))->toBe('Нова пошта');
    expect(DeliveryRegion::query()->count())->toBe(2);
    expect(DeliveryCity::query()->count())->toBe(3);
    expect(DeliveryBranch::query()->count())->toBe(2);

    $branch = DeliveryBranch::query()->where('external_id', 'wh-1')->first();

    expect($branch)->not->toBeNull();
    expect($branch->name)->toBe("\u{2116}12, Centralna 15");
    expect($branch->address)->toBe('Centralna 15');
    expect($branch->postal_code)->toBeNull();

    fakeNovaPoshtaApi(
        areas: [
            ['Ref' => 'area-1', 'Description' => 'Kyivska'],
            ['Ref' => 'area-2', 'Description' => 'Lvivska'],
        ],
        cities: [
            ['Ref' => 'city-1', 'Description' => 'Kyiv', 'Area' => 'area-1'],
            ['Ref' => 'city-2', 'Description' => 'Boryspil', 'Area' => 'area-1'],
            ['Ref' => 'city-3', 'Description' => 'Lviv', 'Area' => 'area-2'],
        ],
        warehouses: [
            ['Ref' => 'wh-1', 'CityRef' => 'city-1', 'Number' => '12', 'ShortAddress' => 'Centralna 15'],
            ['Ref' => 'wh-2', 'CityRef' => 'city-3', 'Number' => '7', 'ShortAddress' => 'Svobody 1'],
            ['Ref' => 'wh-skip', 'CityRef' => 'city-1', 'Number' => '', 'ShortAddress' => 'Porozhnya 2'],
        ],
    );

    $secondRun = $service->sync();

    expect($secondRun['skipped'])->toBe(1);
    expect($secondRun['regions_created'])->toBe(0);
    expect($secondRun['regions_updated'])->toBe(0);
    expect($secondRun['cities_created'])->toBe(0);
    expect($secondRun['cities_updated'])->toBe(0);
    expect($secondRun['branches_created'])->toBe(0);
    expect($secondRun['branches_updated'])->toBe(0);

    expect(DeliveryService::query()->where('code', 'nova_poshta')->count())->toBe(1);
    expect(DeliveryRegion::query()->count())->toBe(2);
    expect(DeliveryCity::query()->count())->toBe(3);
    expect(DeliveryBranch::query()->count())->toBe(2);
});

it('command fails with a clear error when nova poshta api key is missing', function () {
    config()->set('services.nova_poshta.api_key', null);

    $this->artisan('delivery:sync-novaposhta')
        ->expectsOutputToContain('NOVA_POSHTA_API_KEY is not configured.')
        ->assertFailed();
});

it('runs the nova poshta sync artisan command successfully', function () {
    config()->set('services.nova_poshta.api_key', 'test-key');

    fakeNovaPoshtaApi(
        areas: [
            ['Ref' => 'area-1', 'Description' => 'Kyivska'],
        ],
        cities: [
            ['Ref' => 'city-1', 'Description' => 'Kyiv', 'Area' => 'area-1'],
        ],
        warehouses: [
            ['Ref' => 'wh-1', 'CityRef' => 'city-1', 'Number' => '12', 'ShortAddress' => 'Centralna 15'],
        ],
    );

    $this->artisan('delivery:sync-novaposhta')
        ->expectsOutputToContain('Starting Nova Poshta sync...')
        ->expectsOutputToContain('Nova Poshta sync completed.')
        ->assertSuccessful();

    expect(DeliveryService::query()->where('code', 'nova_poshta')->count())->toBe(1);
    expect(DeliveryBranch::query()->first()?->name)->toBe("\u{2116}12, Centralna 15");
});

it('command fails with a clear error when nova poshta api returns an error response', function () {
    config()->set('services.nova_poshta.api_key', 'test-key');

    Http::fake([
        '*' => Http::response([
            'success' => false,
            'errors' => ['Invalid API key'],
        ], 200),
    ]);

    $this->artisan('delivery:sync-novaposhta')
        ->expectsOutputToContain('Starting Nova Poshta sync...')
        ->expectsOutputToContain('Invalid API key')
        ->assertFailed();
});