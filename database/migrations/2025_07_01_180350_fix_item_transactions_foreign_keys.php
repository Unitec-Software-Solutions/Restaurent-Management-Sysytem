<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations to fix foreign key references for Laravel + PostgreSQL + Tailwind CSS
     */
    public function up(): void
    {
        try {
            // First, check what table actually exists
            $itemMasterTableExists = Schema::hasTable('item_master');
            $itemMastersTableExists = Schema::hasTable('item_master');

            if (!$itemMasterTableExists && !$itemMastersTableExists) {
                throw new \Exception('Neither item_master nor item_master table exists. Please run item master migration first.');
            }

            // Determine which table name to use
            $correctTableName = $itemMastersTableExists ? 'item_master' : 'item_master';
            Log::info("Using table name: {$correctTableName}");

            // Get existing foreign key constraints for item_transactions table
            $existingConstraints = $this->getExistingForeignKeys('item_transactions');
            Log::info('Existing foreign key constraints:', $existingConstraints);

            // Only drop existing foreign key constraints
            Schema::table('item_transactions', function (Blueprint $table) use ($existingConstraints) {
                // Drop inventory_item_id foreign key if it exists
                if (in_array('item_transactions_inventory_item_id_foreign', $existingConstraints)) {
                    try {
                        $table->dropForeign(['inventory_item_id']);
                        Log::info('Dropped inventory_item_id foreign key');
                    } catch (\Exception $e) {
                        Log::warning('Could not drop inventory_item_id foreign key: ' . $e->getMessage());
                    }
                }

                // Drop item_master_id foreign key if it exists (this constraint doesn't actually exist)
                if (in_array('item_transactions_item_master_id_foreign', $existingConstraints)) {
                    try {
                        $table->dropForeign(['item_master_id']);
                        Log::info('Dropped item_master_id foreign key');
                    } catch (\Exception $e) {
                        Log::warning('Could not drop item_master_id foreign key: ' . $e->getMessage());
                    }
                } else {
                    Log::info('item_master_id foreign key does not exist, skipping drop');
                }
            });

            // Add item_master_id column if it doesn't exist
            $existingColumns = Schema::getColumnListing('item_transactions');
            if (!in_array('item_master_id', $existingColumns)) {
                Schema::table('item_transactions', function (Blueprint $table) {
                    $table->unsignedBigInteger('item_master_id')->nullable()->after('inventory_item_id');
                    Log::info('Added item_master_id column');
                });
            }

            // Add corrected foreign key constraints
            Schema::table('item_transactions', function (Blueprint $table) use ($correctTableName, $existingConstraints) {
                // Add inventory_item_id foreign key with correct table reference
                if (!in_array('item_transactions_inventory_item_id_foreign', $existingConstraints)) {
                    try {
                        $table->foreign('inventory_item_id')->references('id')->on($correctTableName)->onDelete('cascade');
                        Log::info("Added inventory_item_id foreign key referencing {$correctTableName}");
                    } catch (\Exception $e) {
                        Log::warning('Could not add inventory_item_id foreign key: ' . $e->getMessage());
                    }
                }

                // Add item_master_id foreign key with correct table reference
                if (!in_array('item_transactions_item_master_id_foreign', $existingConstraints)) {
                    try {
                        $table->foreign('item_master_id')->references('id')->on($correctTableName)->onDelete('set null');
                        Log::info("Added item_master_id foreign key referencing {$correctTableName}");
                    } catch (\Exception $e) {
                        Log::warning('Could not add item_master_id foreign key: ' . $e->getMessage());
                    }
                }
            });

            // Add performance indexes for PostgreSQL + Tailwind CSS UI
            $this->addPerformanceIndexes();

            Log::info('Successfully fixed item_transactions foreign key references');

        } catch (\Exception $e) {
            Log::error('Error fixing item_transactions foreign keys: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get existing foreign key constraints for a table in PostgreSQL
     */
    private function getExistingForeignKeys(string $tableName): array
    {
        try {
            $constraints = DB::select("
                SELECT constraint_name
                FROM information_schema.table_constraints
                WHERE table_name = ? AND constraint_type = 'FOREIGN KEY'
            ", [$tableName]);

            return array_column($constraints, 'constraint_name');
        } catch (\Exception $e) {
            Log::warning('Could not fetch existing foreign keys: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Add PostgreSQL performance indexes for Tailwind CSS UI queries
     */
    private function addPerformanceIndexes(): void
    {
        $indexesToAdd = [
            [
                'columns' => ['inventory_item_id'],
                'index' => 'idx_item_transactions_inventory_item'
            ],
            [
                'columns' => ['item_master_id'],
                'index' => 'idx_item_transactions_item_master'
            ],
            [
                'columns' => ['transaction_type', 'organization_id'],
                'index' => 'idx_item_transactions_type_org'
            ],
            [
                'columns' => ['branch_id', 'transaction_type'],
                'index' => 'idx_item_transactions_branch_type'
            ],
            [
                'columns' => ['created_at'],
                'index' => 'idx_item_transactions_created_at'
            ]
        ];

        foreach ($indexesToAdd as $indexData) {
            $this->addIndexIfNotExists('item_transactions', $indexData['columns'], $indexData['index']);
        }
    }

    /**
     * Add index if it doesn't exist in PostgreSQL
     */
    private function addIndexIfNotExists(string $tableName, array $columns, string $indexName): void
    {
        try {
            // Check if all columns exist before creating index
            $existingColumns = Schema::getColumnListing($tableName);
            $missingColumns = array_diff($columns, $existingColumns);

            if (!empty($missingColumns)) {
                Log::warning("Skipping index {$indexName} - missing columns: " . implode(', ', $missingColumns));
                return;
            }

            // Check if index exists in PostgreSQL
            $indexExists = DB::selectOne("
                SELECT indexname
                FROM pg_indexes
                WHERE tablename = ? AND indexname = ?
            ", [$tableName, $indexName]);

            if (!$indexExists) {
                Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                    $table->index($columns, $indexName);
                });
                Log::info("Created index: {$indexName}");
            } else {
                Log::info("Index already exists: {$indexName}");
            }
        } catch (\Exception $e) {
            Log::warning("Could not create index {$indexName}: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('item_transactions', function (Blueprint $table) {
            // Get existing constraints before attempting to drop
            $existingConstraints = $this->getExistingForeignKeys('item_transactions');

            // Drop foreign keys only if they exist
            if (in_array('item_transactions_inventory_item_id_foreign', $existingConstraints)) {
                try {
                    $table->dropForeign(['inventory_item_id']);
                } catch (\Exception $e) {
                    Log::warning('Could not drop inventory_item_id foreign key in rollback: ' . $e->getMessage());
                }
            }

            if (in_array('item_transactions_item_master_id_foreign', $existingConstraints)) {
                try {
                    $table->dropForeign(['item_master_id']);
                } catch (\Exception $e) {
                    Log::warning('Could not drop item_master_id foreign key in rollback: ' . $e->getMessage());
                }
            }

            // Drop indexes
            $indexesToDrop = [
                'idx_item_transactions_inventory_item',
                'idx_item_transactions_item_master',
                'idx_item_transactions_type_org',
                'idx_item_transactions_branch_type',
                'idx_item_transactions_created_at'
            ];

            foreach ($indexesToDrop as $indexName) {
                try {
                    $table->dropIndex($indexName);
                } catch (\Exception $e) {
                    // Index might not exist
                }
            }

            // Drop item_master_id column if it was added
            $existingColumns = Schema::getColumnListing('item_transactions');
            if (in_array('item_master_id', $existingColumns)) {
                $table->dropColumn('item_master_id');
            }
        });
    }
};
