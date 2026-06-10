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
            $table->dropIndex(['tenant_id', 'status']);
            $table->index(['tenant_id', 'deleted_at', 'status'], 'quotes_tenant_deleted_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex('quotes_tenant_deleted_status_index');
            $table->index(['tenant_id', 'status']);
        });
    }
};
