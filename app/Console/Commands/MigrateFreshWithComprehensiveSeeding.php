<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateFreshWithComprehensiveSeeding extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrate:fresh-comprehensive 
                            {--seed : Run database seeding after migration}
                            {--seeder= : Specify which seeder to run}
                            {--verify : Verify seeded data integrity}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     */
    protected $description = 'Drop all tables and re-run all migrations with comprehensive seeding';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Fresh Migration with Comprehensive Seeding...');

        // Prepare migration options
        $migrationOptions = ['--force' => true];
        
        if ($this->option('force')) {
            $migrationOptions['--force'] = true;
        }

        try {
            // Run fresh migration
            $this->info('🔄 Running fresh migration...');
            Artisan::call('migrate:fresh', $migrationOptions);
            $this->info('✅ Migration completed successfully');

            // Run seeding if requested
            if ($this->option('seed')) {
                $this->info('🌱 Starting database seeding...');
                
                $seederClass = $this->option('seeder');
                if ($seederClass) {
                    // Run specific seeder
                    Artisan::call('db:seed', ['--class' => $seederClass]);
                    $this->info("✅ Seeder {$seederClass} completed");
                } else {
                    // Run comprehensive seeding
                    Artisan::call('seed:comprehensive', [
                        '--verify' => $this->option('verify'),
                        '--summary' => true
                    ]);
                }
            }

            $this->info('🎉 Fresh migration with comprehensive seeding completed!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Operation failed: ' . $e->getMessage());
            return 1;
        }
    }
}
