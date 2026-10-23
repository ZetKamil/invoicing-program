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
            $table->date('loading_date')->nullable()->after('delivery_address');
            $table->date('delivery_date')->nullable()->after('loading_date');
            $table->integer('cargo_weight_kg')->nullable()->after('delivery_date');
            $table->integer('pallet_count')->nullable()->after('cargo_weight_kg');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('cargo_weight_kg')->nullable()->after('delivery_date');
            $table->integer('pallet_count')->nullable()->after('cargo_weight_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['loading_date', 'delivery_date', 'cargo_weight_kg', 'pallet_count']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['cargo_weight_kg', 'pallet_count']);
        });
    }
};
