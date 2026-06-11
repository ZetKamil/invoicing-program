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
            $table->text('description')->nullable()->after('quote_number');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('buyer_reference');
            $table->string('cmr_number')->nullable()->after('notes');
            $table->string('license_plate')->nullable()->after('cmr_number');
            $table->date('loading_date')->nullable()->after('license_plate');
            $table->date('delivery_date')->nullable()->after('loading_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['notes', 'cmr_number', 'license_plate', 'loading_date', 'delivery_date']);
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
