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
            $table->string('trailer_type')->nullable()->after('quote_number');
            $table->string('loading_address')->nullable()->after('trailer_type');
            $table->string('delivery_address')->nullable()->after('loading_address');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('trailer_type')->nullable()->after('invoice_number');
            $table->string('loading_address')->nullable()->after('trailer_type');
            $table->string('delivery_address')->nullable()->after('loading_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['trailer_type', 'loading_address', 'delivery_address']);
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['trailer_type', 'loading_address', 'delivery_address']);
        });
    }
};
