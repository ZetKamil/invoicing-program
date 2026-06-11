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
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('incoterms')->nullable()->after('status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('incoterms')->nullable()->after('status');
            $table->boolean('is_reverse_charge')->default(false)->after('incoterms');
            $table->string('cmr_document_path')->nullable()->after('is_reverse_charge');
            $table->string('driver_name')->nullable()->after('trailer_license_plate');
            $table->string('driver_phone')->nullable()->after('driver_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['incoterms']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['incoterms', 'is_reverse_charge', 'cmr_document_path', 'driver_name', 'driver_phone']);
        });
    }
};
