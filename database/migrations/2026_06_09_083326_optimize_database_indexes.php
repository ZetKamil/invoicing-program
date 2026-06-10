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
        // 1. Optimize leads indexes
        Schema::table('leads', function (Blueprint $table) {
            // Drop old index
            $table->dropIndex(['tenant_id', 'status']);
            // Add new index including deleted_at for SoftDeletes queries
            $table->index(['tenant_id', 'deleted_at', 'status'], 'leads_tenant_deleted_status_index');
        });

        // 2. Optimize invoices indexes (if they had a status index, upgrade it. The original only had ->index() on status)
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['tenant_id', 'deleted_at', 'status'], 'invoices_tenant_deleted_status_index');
        });

        // 3. Optimize users table for roles
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_tenant_deleted_status_index');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_tenant_deleted_status_index');
            $table->index(['tenant_id', 'status']);
        });
    }
};
