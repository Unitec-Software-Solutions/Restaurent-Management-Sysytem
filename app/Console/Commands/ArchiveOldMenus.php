<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MenuSafetyService;
use Carbon\Carbon;

class ArchiveOldMenus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:archive-old {--days=30 : Number of days old for archiving} {--dry-run : Show what would be archived without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive old inactive menus to keep the database clean';

    protected $menuSafetyService;

    public function __construct(MenuSafetyService $menuSafetyService)
    {
        parent::__construct();
        $this->menuSafetyService = $menuSafetyService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysOld = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Archiving menus older than {$daysOld} days...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No actual changes will be made');
        }

        if ($dryRun) {
            // For dry run, just show what would be archived
            $cutoffDate = Carbon::now()->subDays($daysOld);
            $menusToArchive = \App\Models\Menu::where('is_active', false)
                ->where('updated_at', '<', $cutoffDate)
                ->whereNull('archived_at')
                ->get();

            if ($menusToArchive->isEmpty()) {
                $this->info('No menus found that need archiving.');
                return 0;
            }

            $this->table(
                ['ID', 'Name', 'Branch', 'Type', 'Last Updated'],
                $menusToArchive->map(function($menu) {
                    return [
                        $menu->id,
                        $menu->name,
                        $menu->branch?->name ?? 'N/A',
                        $menu->type,
                        $menu->updated_at->format('Y-m-d H:i:s')
                    ];
                })
            );

            $this->info("Would archive {$menusToArchive->count()} menus.");
            return 0;
        }

        // Perform actual archiving
        $result = $this->menuSafetyService->archiveOldMenus($daysOld);

        $this->info("Successfully archived {$result['archived_count']} menus.");

        if (!empty($result['errors'])) {
            $this->error('Errors encountered:');
            foreach ($result['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }

        return 0;
    }
}
