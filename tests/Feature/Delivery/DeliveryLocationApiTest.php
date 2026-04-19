<?php

use App\Models\DeliveryBranch;
use App\Models\DeliveryCity;
use App\Models\DeliveryRegion;
use App\Models\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns only active delivery services', function () {
    $activeService = DeliveryService::query()->create([
        'name' => 'Нова пошта',
        'code' => 'novaposhta',
        'is_active' => true,
    ]);

    DeliveryService::query()->create([
        'name' => 'Архівна служба',
        'code' => 'archived',
        'is_active' => false,
    ]);

    $this->getJson(route('delivery.services'))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $activeService->id,
                'name' => 'Нова пошта',
            ],
        ]);
});

it('returns regions only for the selected service', function () {
    $service = DeliveryService::query()->create([
        'name' => 'Укрпошта',
        'code' => 'ukrposhta',
        'is_active' => true,
    ]);

    $otherService = DeliveryService::query()->create([
        'name' => 'Нова пошта',
        'code' => 'novaposhta',
        'is_active' => true,
    ]);

    $region = DeliveryRegion::query()->create([
        'delivery_service_id' => $service->id,
        'name' => 'Київська',
    ]);

    DeliveryRegion::query()->create([
        'delivery_service_id' => $otherService->id,
        'name' => 'Львівська',
    ]);

    $this->getJson(route('delivery.regions', $service))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $region->id,
                'name' => 'Київська',
            ],
        ]);
});

it('returns cities only for the selected region', function () {
    $service = DeliveryService::query()->create([
        'name' => 'Укрпошта',
        'code' => 'ukrposhta',
        'is_active' => true,
    ]);

    $region = DeliveryRegion::query()->create([
        'delivery_service_id' => $service->id,
        'name' => 'Київська',
    ]);

    $otherRegion = DeliveryRegion::query()->create([
        'delivery_service_id' => $service->id,
        'name' => 'Полтавська',
    ]);

    $city = DeliveryCity::query()->create([
        'delivery_region_id' => $region->id,
        'name' => 'Київ',
    ]);

    DeliveryCity::query()->create([
        'delivery_region_id' => $otherRegion->id,
        'name' => 'Полтава',
    ]);

    $this->getJson(route('delivery.cities', $region))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $city->id,
                'name' => 'Київ',
            ],
        ]);
});

it('returns branches only for the selected city', function () {
    $service = DeliveryService::query()->create([
        'name' => 'Укрпошта',
        'code' => 'ukrposhta',
        'is_active' => true,
    ]);

    $region = DeliveryRegion::query()->create([
        'delivery_service_id' => $service->id,
        'name' => 'Київська',
    ]);

    $city = DeliveryCity::query()->create([
        'delivery_region_id' => $region->id,
        'name' => 'Київ',
    ]);

    $otherCity = DeliveryCity::query()->create([
        'delivery_region_id' => $region->id,
        'name' => 'Бровари',
    ]);

    $branch = DeliveryBranch::query()->create([
        'delivery_city_id' => $city->id,
        'name' => 'Київ 1',
        'address' => 'Вул. Хрещатик, 1',
        'postal_code' => '01001',
        'is_active' => true,
    ]);

    DeliveryBranch::query()->create([
        'delivery_city_id' => $otherCity->id,
        'name' => 'Бровари 1',
        'address' => 'Вул. Київська, 1',
        'postal_code' => '07400',
        'is_active' => true,
    ]);

    $this->getJson(route('delivery.branches', $city))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $branch->id,
                'name' => 'Київ 1',
                'address' => 'Вул. Хрещатик, 1',
                'postal_code' => '01001',
            ],
        ]);
});

it('excludes inactive branches from the selected city', function () {
    $service = DeliveryService::query()->create([
        'name' => 'Укрпошта',
        'code' => 'ukrposhta',
        'is_active' => true,
    ]);

    $region = DeliveryRegion::query()->create([
        'delivery_service_id' => $service->id,
        'name' => 'Київська',
    ]);

    $city = DeliveryCity::query()->create([
        'delivery_region_id' => $region->id,
        'name' => 'Київ',
    ]);

    $activeBranch = DeliveryBranch::query()->create([
        'delivery_city_id' => $city->id,
        'name' => 'Київ 1',
        'address' => 'Вул. Хрещатик, 1',
        'postal_code' => '01001',
        'is_active' => true,
    ]);

    DeliveryBranch::query()->create([
        'delivery_city_id' => $city->id,
        'name' => 'Київ 2',
        'address' => 'Вул. Хрещатик, 2',
        'postal_code' => '01002',
        'is_active' => false,
    ]);

    $this->getJson(route('delivery.branches', $city))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $activeBranch->id,
                'name' => 'Київ 1',
                'address' => 'Вул. Хрещатик, 1',
                'postal_code' => '01001',
            ],
        ]);
});
