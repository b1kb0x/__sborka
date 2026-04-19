<?php

namespace App\Http\Requests;

use App\Models\DeliveryBranch;
use App\Models\DeliveryCity;
use App\Models\DeliveryRegion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! ($this->user()?->isAdmin() ?? false);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
            'delivery_service_id' => ['required', 'integer', 'exists:delivery_services,id'],
            'delivery_region_id' => ['required', 'integer', 'exists:delivery_regions,id'],
            'delivery_city_id' => ['required', 'integer', 'exists:delivery_cities,id'],
            'delivery_branch_id' => ['required', 'integer', 'exists:delivery_branches,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $serviceId = (int) $this->input('delivery_service_id');
            $regionId = (int) $this->input('delivery_region_id');
            $cityId = (int) $this->input('delivery_city_id');
            $branchId = (int) $this->input('delivery_branch_id');

            $region = DeliveryRegion::query()->find($regionId);

            if (! $region || (int) $region->delivery_service_id !== $serviceId) {
                $validator->errors()->add('delivery_region_id', 'The selected region does not belong to the selected delivery service.');

                return;
            }

            $city = DeliveryCity::query()->find($cityId);

            if (! $city || (int) $city->delivery_region_id !== $regionId) {
                $validator->errors()->add('delivery_city_id', 'The selected city does not belong to the selected region.');

                return;
            }

            $branch = DeliveryBranch::query()->find($branchId);

            if (! $branch || (int) $branch->delivery_city_id !== $cityId) {
                $validator->errors()->add('delivery_branch_id', 'The selected branch does not belong to the selected city.');
            }
        });
    }
}
