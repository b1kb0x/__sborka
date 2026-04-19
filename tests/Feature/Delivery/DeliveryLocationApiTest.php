<?php

use App\Models\DeliveryBranch;
use App\Models\DeliveryCity;
use App\Models\DeliveryRegion;
use App\Models\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns only active delivery services', function () {
    $activeService = DeliveryService::query()->create([
        'name' => 'Nova Poshta',
        'code' => 'novaposhta',
        'is_active' => true,
    ]);

    DeliveryService::query()->create([
        'name' => 'Archived service',
        'code' => 'archived',
        'is_active' => false,
    ]);

    $this->getJson(route('delivery.services'))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $activeService->id,
                'name' => 'Nova Poshta',
            ],
        ]);
});

it('returns regions only for the selected service', function () {
    $service = DeliveryService::query()->create([
        'name' => 'Ukrposhta',
        'code' => 'ukrposhta',
        'is_active' => true,
    ]);

    $otherService = DeliveryService::query()->create([
        'name' => 'Nova Poshta',
        'code' => 'novaposhta',
        'is_active' => true,
    ]);

    $region = DeliveryRegion::query()->create([
        'delivery_service_id' => $service->id,
        'name' => 'Kyivska',
    ]);

    DeliveryRegion::query()->create([
        'delivery_service_id' => $otherService->id,
        'name' => 'Lvivska',
    ]);

    $this->getJson(route('delivery.regions', $service))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $region->id,
                'name' => 'Kyivska',
            ],
        ]);
});

it('returns cities only for the selected region', function () {
    $service = DeliveryService::query()->create([
        'name' => 'Ukrposhta',
        'code' => 'ukrposhta',
        'is_active' => true,
    ]);

    $region = DeliveryRegion::query()->create([
        'delivery_service_id' => $service->id,
        'name' => 'Kyivska',
    ]);

    $otherRegion = DeliveryRegion::query()->create([
        'delivery_service_id' => $service->id,
        'name' => 'Poltavska',
    ]);

    $city = DeliveryCity::query()->create([
        'delivery_region_id' => $region->id,
        'name' => 'Kyiv',
    ]);

    DeliveryCity::query()->create([
        'delivery_region_id' => $otherRegion->id,
        'name' => 'Poltava',
    ]);

    $this->getJson(route('delivery.cities', $region))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $city->id,
                'name' => 'Kyiv',
            ],
        ]);
});

it('returns branches only for the selected city in backend sorted order', function () {
    $service = DeliveryService::query()->create([
        'name' => 'Nova Poshta',
        'code' => 'novaposhta',
        'is_active' => true,
    ]);

    $region = DeliveryRegion::query()->create([
        'delivery_service_id' => $service->id,
        'name' => 'Kyivska',
    ]);

    $city = DeliveryCity::query()->create([
        'delivery_region_id' => $region->id,
        'name' => 'Kyiv',
    ]);

    $otherCity = DeliveryCity::query()->create([
        'delivery_region_id' => $region->id,
        'name' => 'Brovary',
    ]);

    $branchTen = DeliveryBranch::query()->create([
        'delivery_city_id' => $city->id,
        'name' => 'Відділення №10',
        'address' => 'Branch street 10',
        'postal_code' => null,
        'type' => 'branch',
        'is_active' => true,
    ]);

    $branchTwo = DeliveryBranch::query()->create([
        'delivery_city_id' => $city->id,
        'name' => 'Відділення №2',
        'address' => 'Branch street 2',
        'postal_code' => null,
        'type' => 'branch',
        'is_active' => true,
    ]);

    $locker = DeliveryBranch::query()->create([
        'delivery_city_id' => $city->id,
        'name' => 'Поштомат №145',
        'address' => 'Locker street 145',
        'postal_code' => null,
        'type' => 'parcel_locker',
        'is_active' => true,
    ]);

    $pickup = DeliveryBranch::query()->create([
        'delivery_city_id' => $city->id,
        'name' => 'Пункт видачі №3',
        'address' => 'Pickup street 3',
        'postal_code' => null,
        'type' => 'pickup_point',
        'is_active' => true,
    ]);

    DeliveryBranch::query()->create([
        'delivery_city_id' => $otherCity->id,
        'name' => 'Відділення №1',
        'address' => 'Other city branch',
        'postal_code' => null,
        'type' => 'branch',
        'is_active' => true,
    ]);

    $this->getJson(route('delivery.branches', $city))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $branchTwo->id,
                'name' => 'Відділення №2',
                'address' => 'Branch street 2',
                'postal_code' => null,
            ],
            [
                'id' => $branchTen->id,
                'name' => 'Відділення №10',
                'address' => 'Branch street 10',
                'postal_code' => null,
            ],
            [
                'id' => $locker->id,
                'name' => 'Поштомат №145',
                'address' => 'Locker street 145',
                'postal_code' => null,
            ],
            [
                'id' => $pickup->id,
                'name' => 'Пункт видачі №3',
                'address' => 'Pickup street 3',
                'postal_code' => null,
            ],
        ]);
});

it('excludes inactive branches from the selected city', function () {
    $service = DeliveryService::query()->create([
        'name' => 'Ukrposhta',
        'code' => 'ukrposhta',
        'is_active' => true,
    ]);

    $region = DeliveryRegion::query()->create([
        'delivery_service_id' => $service->id,
        'name' => 'Kyivska',
    ]);

    $city = DeliveryCity::query()->create([
        'delivery_region_id' => $region->id,
        'name' => 'Kyiv',
    ]);

    $activeBranch = DeliveryBranch::query()->create([
        'delivery_city_id' => $city->id,
        'name' => '01001, Khreshchatyk 1',
        'address' => 'Khreshchatyk 1',
        'postal_code' => '01001',
        'type' => 'branch',
        'is_active' => true,
    ]);

    DeliveryBranch::query()->create([
        'delivery_city_id' => $city->id,
        'name' => '01002, Khreshchatyk 2',
        'address' => 'Khreshchatyk 2',
        'postal_code' => '01002',
        'type' => 'branch',
        'is_active' => false,
    ]);

    $this->getJson(route('delivery.branches', $city))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $activeBranch->id,
                'name' => '01001, Khreshchatyk 1',
                'address' => 'Khreshchatyk 1',
                'postal_code' => '01001',
            ],
        ]);
});