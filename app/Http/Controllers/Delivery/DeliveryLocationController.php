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
            ->get(['id', 'name', 'address', 'postal_code', 'type']);

        $branches = $branches
            ->sort(function ($left, $right) {
                $leftPriority = $this->branchTypePriority($left->type);
                $rightPriority = $this->branchTypePriority($right->type);

                if ($leftPriority !== $rightPriority) {
                    return $leftPriority <=> $rightPriority;
                }

                $leftNumber = $this->branchNumber($left->name);
                $rightNumber = $this->branchNumber($right->name);

                if ($leftNumber !== $rightNumber) {
                    return $leftNumber <=> $rightNumber;
                }

                return strcasecmp($left->name, $right->name);
            })
            ->values()
            ->map(fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'address' => $branch->address,
                'postal_code' => $branch->postal_code,
            ]);

        return response()->json($branches);
    }

    protected function branchTypePriority(?string $type): int
    {
        return match ($type) {
            'branch' => 0,
            'parcel_locker' => 1,
            'pickup_point' => 2,
            default => 3,
        };
    }

    protected function branchNumber(string $name): int
    {
        if (preg_match('/№\s*(\d+)/u', $name, $matches) === 1) {
            return (int) $matches[1];
        }

        return PHP_INT_MAX;
    }
}
