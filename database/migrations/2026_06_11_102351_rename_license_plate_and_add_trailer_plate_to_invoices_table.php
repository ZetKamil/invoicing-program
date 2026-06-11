<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('license_plate', 'truck_license_plate');
            $table->string('trailer_license_plate')->nullable()->after('license_plate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('truck_license_plate', 'license_plate');
            $table->dropColumn('trailer_license_plate');
        });
    }
};
