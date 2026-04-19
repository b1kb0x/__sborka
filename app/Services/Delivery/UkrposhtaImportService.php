<?php

namespace App\Services\Delivery;

use App\Models\DeliveryBranch;
use App\Models\DeliveryCity;
use App\Models\DeliveryRegion;
use App\Models\DeliveryService;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UkrposhtaImportService
{
    public function import(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (count($rows) < 2) {
            return [
                'processed' => 0,
                'skipped' => 0,
                'regions_created' => 0,
                'cities_created' => 0,
                'branches_created' => 0,
                'branches_updated' => 0,
            ];
        }

        $headers = array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            $rows[0]
        );

        $service = DeliveryService::firstOrCreate(
            ['code' => 'ukrposhta'],
            [
                'name' => 'Укрпошта',
                'is_active' => true,
            ]
        );

        $stats = [
            'processed' => 0,
            'skipped' => 0,
            'regions_created' => 0,
            'cities_created' => 0,
            'branches_created' => 0,
            'branches_updated' => 0,
        ];

        foreach (array_slice($rows, 1) as $row) {
            $data = $this->combineRow($headers, $row);

            $regionName = $this->value($data, 'Область');
            $cityName = $this->value($data, 'Населений пункт');
            $districtName = $this->value($data, 'Район (новий)');
            $cityPostalCode = $this->value($data, 'Поштовий індекс (Postal code)');
            $branchName = $this->value($data, "Вiддiлення зв'язку");
            $branchPostalCode = $this->value($data, "Поштовий індекс відділення зв'язку (Post code of post office)");

            if (! $regionName || ! $cityName || ! $branchName) {
                $stats['skipped']++;
                continue;
            }

            $stats['processed']++;

            $region = DeliveryRegion::firstOrCreate(
                [
                    'delivery_service_id' => $service->id,
                    'name' => $regionName,
                ]
            );

            if ($region->wasRecentlyCreated) {
                $stats['regions_created']++;
            }

            $city = DeliveryCity::firstOrCreate(
                [
                    'delivery_region_id' => $region->id,
                    'name' => $cityName,
                ],
                [
                    'district_name' => $districtName,
                    'postal_code' => $cityPostalCode,
                ]
            );

            if (! $city->wasRecentlyCreated) {
                $dirty = false;

                if ($districtName && $city->district_name !== $districtName) {
                    $city->district_name = $districtName;
                    $dirty = true;
                }

                if ($cityPostalCode && $city->postal_code !== $cityPostalCode) {
                    $city->postal_code = $cityPostalCode;
                    $dirty = true;
                }

                if ($dirty) {
                    $city->save();
                }
            }

            if ($city->wasRecentlyCreated) {
                $stats['cities_created']++;
            }

            $branch = DeliveryBranch::updateOrCreate(
                [
                    'delivery_city_id' => $city->id,
                    'name' => $branchName,
                    'postal_code' => $branchPostalCode,
                ],
                [
                    'is_active' => true,
                ]
            );

            if ($branch->wasRecentlyCreated) {
                $stats['branches_created']++;
            } else {
                $stats['branches_updated']++;
            }
        }

        return $stats;
    }

    private function combineRow(array $headers, array $row): array
    {
        $data = [];

        foreach ($headers as $index => $header) {
            if (! is_string($header) || $header === '') {
                continue;
            }

            $data[$header] = $row[$index] ?? null;
        }

        return $data;
    }

    private function value(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
