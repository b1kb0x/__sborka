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
            return $this->emptyStats();
        }

        $headerRowIndex = $this->detectHeaderRowIndex($rows);

        if ($headerRowIndex === null) {
            return array_merge($this->emptyStats(), [
                'skipped' => max(count($rows) - 1, 0),
            ]);
        }

        $columnMap = $this->resolveColumnMap($rows[$headerRowIndex]);

        $service = DeliveryService::firstOrCreate(
            ['code' => 'ukrposhta'],
            [
                'name' => 'Укрпошта',
                'is_active' => true,
            ]
        );

        $stats = $this->emptyStats();

        foreach (array_slice($rows, $headerRowIndex + 1) as $row) {
            $regionName = $this->cellValue($row, $columnMap['region'] ?? null);
            $cityName = $this->cellValue($row, $columnMap['city'] ?? null);
            $branchPostalCode = $this->cellValue($row, $columnMap['branch_postal_code'] ?? null);
            $branchAddress = $this->cellValue($row, $columnMap['branch_address'] ?? null);

            if (! $regionName || ! $cityName || ! $branchPostalCode || ! $branchAddress) {
                $stats['skipped']++;
                continue;
            }

            if ($this->isMobileBranchAddress($branchAddress)) {
                $stats['skipped']++;
                continue;
            }

            $stats['processed']++;

            $region = DeliveryRegion::firstOrCreate([
                'delivery_service_id' => $service->id,
                'name' => $regionName,
            ]);

            if ($region->wasRecentlyCreated) {
                $stats['regions_created']++;
            }

            $city = DeliveryCity::firstOrCreate([
                'delivery_region_id' => $region->id,
                'name' => $cityName,
            ]);

            if ($city->wasRecentlyCreated) {
                $stats['cities_created']++;
            }

            $branchName = sprintf('%s, %s', $branchPostalCode, $branchAddress);

            $branch = DeliveryBranch::updateOrCreate(
                [
                    'delivery_city_id' => $city->id,
                    'name' => $branchName,
                    'postal_code' => $branchPostalCode,
                ],
                [
                    'address' => $branchAddress,
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

    private function emptyStats(): array
    {
        return [
            'processed' => 0,
            'skipped' => 0,
            'regions_created' => 0,
            'cities_created' => 0,
            'branches_created' => 0,
            'branches_updated' => 0,
        ];
    }

    private function detectHeaderRowIndex(array $rows): ?int
    {
        foreach ($rows as $index => $row) {
            $columnMap = $this->resolveColumnMap($row);

            if (isset($columnMap['region'], $columnMap['city'], $columnMap['branch_postal_code'], $columnMap['branch_address'])) {
                return $index;
            }
        }

        return null;
    }

    private function resolveColumnMap(array $headerRow): array
    {
        $normalizedHeaders = [];

        foreach ($headerRow as $index => $header) {
            $normalized = $this->normalizeHeader($header);

            if ($normalized !== '') {
                $normalizedHeaders[$index] = $normalized;
            }
        }

        $aliases = [
            'region' => ['область'],
            'city' => ['населений пункт'],
            'branch_postal_code' => ['індекс впз', 'iндекс впз', 'индекс впз'],
            'branch_address' => ['адреса'],
        ];

        $resolved = [];

        foreach ($aliases as $field => $expectedHeaders) {
            foreach ($normalizedHeaders as $index => $normalizedHeader) {
                if (in_array($normalizedHeader, $expectedHeaders, true)) {
                    $resolved[$field] = $index;
                    break;
                }
            }
        }

        return $resolved;
    }

    private function normalizeHeader(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $value = preg_replace('/^\xEF\xBB\xBF/u', '', $value) ?? $value;
        $value = str_replace(
            ["\u{00A0}", "\u{2007}", "\u{202F}", '`', '’', '‘', 'Кј', 'Вґ'],
            [' ', ' ', ' ', "'", "'", "'", "'", "'"],
            $value
        );
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
        $value = strtr($value, [
            'I' => 'І',
            'i' => 'і',
        ]);

        return mb_strtolower($value);
    }

    private function cellValue(array $row, ?int $index): ?string
    {
        if ($index === null) {
            return null;
        }

        $value = $row[$index] ?? null;

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function isMobileBranchAddress(string $address): bool
    {
        $normalized = mb_strtolower(trim($address));
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;

        return str_contains($normalized, 'пересувне');
    }
}
