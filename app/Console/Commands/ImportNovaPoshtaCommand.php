<?php

namespace App\Console\Commands;

use App\Services\Delivery\NovaPoshtaImportService;
use Illuminate\Console\Command;
use Throwable;

class ImportNovaPoshtaCommand extends Command
{
    protected $signature = 'delivery:sync-novaposhta';

    protected $description = 'Sync Nova Poshta regions, cities and branches from API';

    public function handle(NovaPoshtaImportService $importService): int
    {
        if (blank(config('services.nova_poshta.api_key'))) {
            $this->error('NOVA_POSHTA_API_KEY is not configured.');

            return self::FAILURE;
        }

        $this->info('Starting Nova Poshta sync...');

        try {
            $stats = $importService->sync();
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Metric', 'Value'],
            [
                ['Skipped rows', $stats['skipped']],
                ['Regions created', $stats['regions_created']],
                ['Regions updated', $stats['regions_updated']],
                ['Cities created', $stats['cities_created']],
                ['Cities updated', $stats['cities_updated']],
                ['Branches created', $stats['branches_created']],
                ['Branches updated', $stats['branches_updated']],
            ]
        );

        $this->info('Nova Poshta sync completed.');

        return self::SUCCESS;
    }
}
