<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ReservationType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Add reservation type
            if (!Schema::hasColumn('reservations', 'type')) {
                $table->enum('type', ReservationType::values())
                      ->default(ReservationType::ONLINE->value)
                      ->after('id');
            }

            // Add table size for reservation
            if (!Schema::hasColumn('reservations', 'table_size')) {
                $table->unsignedInteger('table_size')->default(2)->after('number_of_people');
            }

            // Enhance fee columns (they already exist)
            // Update reservation_fee and cancellation_fee precision if needed

            // Add customer relationship via phone
            if (!Schema::hasColumn('reservations', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('phone');
                $table->foreign('customer_phone')->references('phone')->on('customers')->onDelete('set null');
            }

            // Add additional timestamps
            if (!Schema::hasColumn('reservations', 'seated_at')) {
                $table->timestamp('seated_at')->nullable();
            }
            if (!Schema::hasColumn('reservations', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }

            // Add cancellation reason
            if (!Schema::hasColumn('reservations', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable();
            }

            // Add fee-related flags
            if (!Schema::hasColumn('reservations', 'fee_charged')) {
                $table->boolean('fee_charged')->default(false);
            }
            if (!Schema::hasColumn('reservations', 'cancellation_fee_charged')) {
                $table->boolean('cancellation_fee_charged')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['customer_phone']);
            $table->dropColumn([
                'type',
                'table_size',
                'customer_phone',
                'seated_at',
                'cancelled_at',
                'cancellation_reason',
                'fee_charged',
                'cancellation_fee_charged'
            ]);
        });
    }
};
