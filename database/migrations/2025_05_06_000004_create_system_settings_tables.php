<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string')->comment('string, boolean, integer, json');
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider');
            $table->json('credentials');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_test_mode')->default(true);
            $table->timestamps();
        });

        Schema::create('notification_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->comment('sms, email, push');
            $table->json('credentials');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        // Insert default system settings
        DB::table('system_settings')->insert([
            ['key' => 'reservation_fee', 'value' => '0', 'type' => 'integer', 'group' => 'reservation', 'description' => 'Default reservation fee'],
            ['key' => 'cancellation_policy_hours', 'value' => '24', 'type' => 'integer', 'group' => 'reservation', 'description' => 'Hours before reservation for free cancellation'],
            ['key' => 'service_charge_percentage', 'value' => '10', 'type' => 'integer', 'group' => 'billing', 'description' => 'Default service charge percentage'],
            ['key' => 'loyalty_points_per_dollar', 'value' => '1', 'type' => 'integer', 'group' => 'loyalty', 'description' => 'Loyalty points earned per dollar spent'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notification_providers');
        Schema::dropIfExists('payment_gateways');
        Schema::dropIfExists('system_settings');
    }
}; 