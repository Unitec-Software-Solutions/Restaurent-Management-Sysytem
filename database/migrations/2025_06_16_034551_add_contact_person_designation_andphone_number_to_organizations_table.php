<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactPersonDesignationAndPhoneNumberToOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('contact_person')->nullable();
            $table->string('contact_person_designation')->nullable();
            $table->string('contact_person_phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'contact_person_designation', 'contact_person_phone']);
        });
    }
};
