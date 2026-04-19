<?php

namespace App\Console\Commands;

use App\Services\Delivery\UkrposhtaImportService;
use Illuminate\Console\Command;

class ImportUkrposhtaCommand extends Command
{
    protected $signature = 'delivery:import-ukrposhta {file : Path to Excel file}';

    protected $description = 'Import Ukrposhta regions, cities and branches from Excel file';

    public function handle(UkrposhtaImportService $importService): int
    {
        $file = $this->argument('file');
        $fullPath = base_path($file);

        if (! file_exists($fullPath)) {
            $this->error("File not found: {$fullPath}");

            return self::FAILURE;
        }

        $this->info('Starting Ukrposhta import...');

        $stats = $importService->import($fullPath);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Processed rows', $stats['processed']],
                ['Skipped rows', $stats['skipped']],
                ['Regions created', $stats['regions_created']],
                ['Cities created', $stats['cities_created']],
                ['Branches created', $stats['branches_created']],
                ['Branches updated', $stats['branches_updated']],
            ]
        );

        $this->info('Ukrposhta import completed.');

        return self::SUCCESS;
    }
}
