<?php

use App\Models\DeliveryBranch;
use App\Models\DeliveryCity;
use App\Models\DeliveryRegion;
use App\Models\DeliveryService;
use App\Services\Delivery\UkrposhtaImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

function createUkrposhtaExcel(array $rows): string
{
    $directory = storage_path('framework/testing');

    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $path = $directory.DIRECTORY_SEPARATOR.'ukrposhta-import-'.uniqid().'.xlsx';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray($rows, null, 'A1');

    (new Xlsx($spreadsheet))->save($path);

    return $path;
}

it('imports ukrposhta data from excel, skips invalid rows, and is idempotent', function () {
    $filePath = createUkrposhtaExcel([
        ['', '', '', ''],
        [
            '  Область  ',
            'Населений   пункт',
            'Iндекс ВПЗ',
            'Адреса',
        ],
        ['Вінницька', 'м. Вінниця', '21001', 'вул. Соборна, 1'],
        ['Вінницька', 'м. Вінниця', '21002', ' Пересувне відділення 2 '],
        ['Вінницька', 'м. Вінниця', '21003', 'вул. Центральна, 3'],
        ['Вінницька', 'м. Вінниця', '21004', ''],
    ]);

    $service = app(UkrposhtaImportService::class);

    $firstRun = $service->import($filePath);

    expect($firstRun['processed'])->toBe(2);
    expect($firstRun['skipped'])->toBe(2);
    expect($firstRun['regions_created'])->toBe(1);
    expect($firstRun['cities_created'])->toBe(1);
    expect($firstRun['branches_created'])->toBe(2);
    expect($firstRun['branches_updated'])->toBe(0);

    expect(DeliveryService::query()->where('code', 'ukrposhta')->count())->toBe(1);
    expect(DeliveryRegion::query()->count())->toBe(1);
    expect(DeliveryCity::query()->count())->toBe(1);
    expect(DeliveryBranch::query()->count())->toBe(2);

    $serviceModel = DeliveryService::query()->where('code', 'ukrposhta')->first();
    $region = DeliveryRegion::query()->first();
    $city = DeliveryCity::query()->first();
    $firstBranch = DeliveryBranch::query()->where('postal_code', '21001')->first();
    $secondBranch = DeliveryBranch::query()->where('postal_code', '21003')->first();

    expect($serviceModel)->not->toBeNull();
    expect($serviceModel->name)->toBe('Укрпошта');
    expect($region)->not->toBeNull();
    expect($region->name)->toBe('Вінницька');
    expect($city)->not->toBeNull();
    expect($city->name)->toBe('м. Вінниця');
    expect($firstBranch)->not->toBeNull();
    expect($firstBranch->name)->toBe('21001, вул. Соборна, 1');
    expect($firstBranch->postal_code)->toBe('21001');
    expect($firstBranch->type)->toBe('branch');
    expect($firstBranch->address)->toBe('вул. Соборна, 1');
    expect($secondBranch)->not->toBeNull();
    expect($secondBranch->type)->toBe('branch');
    expect($secondBranch->name)->toBe('21003, вул. Центральна, 3');

    $secondRun = $service->import($filePath);

    expect($secondRun['processed'])->toBe(2);
    expect($secondRun['skipped'])->toBe(2);
    expect($secondRun['regions_created'])->toBe(0);
    expect($secondRun['cities_created'])->toBe(0);
    expect($secondRun['branches_created'])->toBe(0);
    expect($secondRun['branches_updated'])->toBe(2);

    expect(DeliveryService::query()->where('code', 'ukrposhta')->count())->toBe(1);
    expect(DeliveryRegion::query()->count())->toBe(1);
    expect(DeliveryCity::query()->count())->toBe(1);
    expect(DeliveryBranch::query()->count())->toBe(2);
});

it('runs the ukrposhta import artisan command successfully', function () {
    $filePath = createUkrposhtaExcel([
        ['', '', '', ''],
        [
            'Область',
            'Населений пункт',
            'Індекс ВПЗ',
            'Адреса',
        ],
        ['Київська', 'м. Бориспіль', '08301', 'вул. Київський Шлях, 79'],
    ]);

    $this->artisan('delivery:import-ukrposhta', [
        'file' => $filePath,
    ])
        ->expectsOutputToContain('Starting Ukrposhta import...')
        ->expectsOutputToContain('Ukrposhta import completed.')
        ->assertSuccessful();

    expect(DeliveryService::query()->where('code', 'ukrposhta')->count())->toBe(1);
    expect(DeliveryRegion::query()->count())->toBe(1);
    expect(DeliveryCity::query()->count())->toBe(1);
    expect(DeliveryBranch::query()->count())->toBe(1);
    expect(DeliveryBranch::query()->first()?->name)->toBe('08301, вул. Київський Шлях, 79');
});
