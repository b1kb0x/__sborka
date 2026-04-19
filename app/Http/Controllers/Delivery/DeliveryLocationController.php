<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCity;
use App\Models\DeliveryRegion;
use App\Models\DeliveryService;
use Illuminate\Http\JsonResponse;

class DeliveryLocationController extends Controller
{
    public function services(): JsonResponse
    {
        $services = DeliveryService::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($services);
    }

    public function regions(DeliveryService $service): JsonResponse
    {
        $regions = $service->regions()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($regions);
    }

    public function cities(DeliveryRegion $region): JsonResponse
    {
        $cities = $region->cities()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    public function branches(DeliveryCity $city): JsonResponse
    {
        $branches = $city->branches()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'address', 'postal_code']);

        return response()->json($branches);
    }
}
