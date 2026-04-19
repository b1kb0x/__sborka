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

function fakeNovaPoshtaApi(array $areas, array $cities, array $warehousePages, ?callable $warehouseResponse = null): void
{
    Http::fake(function (Request $request) use ($areas, $cities, $warehousePages, $warehouseResponse) {
        $payload = $request->data();
        $calledMethod = $payload['calledMethod'] ?? null;
        $page = (int) (($payload['methodProperties']['Page'] ?? 1));

        return match ($calledMethod) {
            'getAreas' => Http::response([
                'success' => true,
                'data' => $areas,
            ]),
            'getCities' => Http::response([
                'success' => true,
                'data' => $cities,
            ]),
            'getWarehouses' => $warehouseResponse
                ? $warehouseResponse($request, $page)
                : Http::response([
                    'success' => true,
                    'data' => $warehousePages[$page] ?? [],
                ]),
            default => Http::response([
                'success' => false,
                'errors' => ['Unexpected Nova Poshta method.'],
            ], 422),
        };
    });
}

it('does not send method properties when they are empty', function () {
    config()->set('services.nova_poshta.api_key', 'test-key');

    fakeNovaPoshtaApi(
        areas: [
            ['Ref' => 'area-1', 'Description' => 'Kyivska'],
        ],
        cities: [],
        warehousePages: [],
    );

    app(NovaPoshtaImportService::class)->sync();

    Http::assertSent(function (Request $request) {
        $payload = $request->data();

        return ($payload['calledMethod'] ?? null) === 'getAreas'
            && ! array_key_exists('methodProperties', $payload);
    });
});

it('syncs nova poshta regions cities and branches across pages and stays idempotent', function () {
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
        warehousePages: [
            1 => [
                [
                    'Ref' => 'wh-1',
                    'CityRef' => 'city-1',
                    'Number' => '12',
                    'ShortAddress' => 'Centralna 15',
                    'CategoryOfWarehouse' => 'Branch',
                    'Description' => 'Відділення №12 (до 30 кг): Centralna 15',
                ],
                [
                    'Ref' => 'wh-skip',
                    'CityRef' => 'city-1',
                    'Number' => '',
                    'ShortAddress' => 'Porozhnya 2',
                    'CategoryOfWarehouse' => 'Branch',
                ],
            ],
            2 => [
                [
                    'Ref' => 'wh-2',
                    'CityRef' => 'city-3',
                    'Number' => '7',
                    'ShortAddress' => 'Svobody 1',
                    'CategoryOfWarehouse' => 'Postomat',
                    'Description' => 'Поштомат "Нова Пошта" №7: Svobody 1',
                ],
            ],
            3 => [],
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
    expect(DeliveryRegion::query()->count())->toBe(2);
    expect(DeliveryCity::query()->count())->toBe(3);
    expect(DeliveryBranch::query()->count())->toBe(2);

    $branch = DeliveryBranch::query()->where('external_id', 'wh-1')->first();
    $locker = DeliveryBranch::query()->where('external_id', 'wh-2')->first();

    expect($branch)->not->toBeNull();
    expect($branch->name)->toBe('Відділення №12 (до 30 кг): Centralna 15');
    expect($branch->address)->toBe('Centralna 15');
    expect($branch->postal_code)->toBeNull();
    expect($branch->type)->toBe('branch');

    expect($locker)->not->toBeNull();
    expect($locker->name)->toBe('Поштомат "Нова Пошта" №7: Svobody 1');
    expect($locker->address)->toBe('Svobody 1');
    expect($locker->type)->toBe('parcel_locker');

    Http::assertSentCount(5);

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
        warehousePages: [
            1 => [
                [
                    'Ref' => 'wh-1',
                    'CityRef' => 'city-1',
                    'Number' => '12',
                    'ShortAddress' => 'Centralna 15',
                    'CategoryOfWarehouse' => 'Branch',
                    'Description' => 'Відділення №12 (до 30 кг): Centralna 15',
                ],
                [
                    'Ref' => 'wh-skip',
                    'CityRef' => 'city-1',
                    'Number' => '',
                    'ShortAddress' => 'Porozhnya 2',
                    'CategoryOfWarehouse' => 'Branch',
                ],
            ],
            2 => [
                [
                    'Ref' => 'wh-2',
                    'CityRef' => 'city-3',
                    'Number' => '7',
                    'ShortAddress' => 'Svobody 1',
                    'CategoryOfWarehouse' => 'Postomat',
                    'Description' => 'Поштомат "Нова Пошта" №7: Svobody 1',
                ],
            ],
            3 => [],
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
        warehousePages: [
            1 => [
                [
                    'Ref' => 'wh-1',
                    'CityRef' => 'city-1',
                    'Number' => '12',
                    'ShortAddress' => 'Centralna 15',
                    'CategoryOfWarehouse' => 'Store',
                    'Description' => 'Пункт приймання-видачі (до 10 кг): Centralna 15',
                ],
            ],
            2 => [],
        ],
    );

    $this->artisan('delivery:sync-novaposhta')
        ->expectsOutputToContain('Starting Nova Poshta sync...')
        ->expectsOutputToContain('Nova Poshta sync completed.')
        ->assertSuccessful();

    expect(DeliveryService::query()->where('code', 'nova_poshta')->count())->toBe(1);
    expect(DeliveryBranch::query()->first()?->name)->toBe('Пункт №12 (до 10 кг): Centralna 15');
    expect(DeliveryBranch::query()->first()?->type)->toBe('pickup_point');
});

it('command fails with a clear error when nova poshta api returns a warning response', function () {
    config()->set('services.nova_poshta.api_key', 'test-key');

    Http::fake([
        '*' => Http::response([
            'success' => false,
            'errors' => [],
            'warnings' => ['Invalid method properties'],
        ], 200),
    ]);

    $this->artisan('delivery:sync-novaposhta')
        ->expectsOutputToContain('Starting Nova Poshta sync...')
        ->expectsOutputToContain('Invalid method properties')
        ->assertFailed();
});

it('propagates an api error from a later warehouses page', function () {
    config()->set('services.nova_poshta.api_key', 'test-key');

    fakeNovaPoshtaApi(
        areas: [
            ['Ref' => 'area-1', 'Description' => 'Kyivska'],
        ],
        cities: [
            ['Ref' => 'city-1', 'Description' => 'Kyiv', 'Area' => 'area-1'],
        ],
        warehousePages: [
            1 => [
                ['Ref' => 'wh-1', 'CityRef' => 'city-1', 'Number' => '12', 'ShortAddress' => 'Centralna 15', 'CategoryOfWarehouse' => 'Branch'],
            ],
        ],
        warehouseResponse: function (Request $request, int $page) {
            if ($page === 1) {
                return Http::response([
                    'success' => true,
                    'data' => [
                        ['Ref' => 'wh-1', 'CityRef' => 'city-1', 'Number' => '12', 'ShortAddress' => 'Centralna 15', 'CategoryOfWarehouse' => 'Branch'],
                    ],
                ]);
            }

            if ($page === 2) {
                return Http::response([
                    'success' => false,
                    'errors' => ['Warehouse page failed'],
                ], 200);
            }

            return Http::response([
                'success' => true,
                'data' => [],
            ]);
        },
    );

    expect(fn () => app(NovaPoshtaImportService::class)->sync())
        ->toThrow(\RuntimeException::class, 'Warehouse page failed');
});