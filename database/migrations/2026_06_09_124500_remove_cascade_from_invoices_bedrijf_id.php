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
            // Drop the existing foreign key constraint
            $table->dropForeign(['bedrijf_id']);
            
            // Re-add it without cascadeOnDelete to respect SoftDeletes
            $table->foreign('bedrijf_id')->references('id')->on('bedrijven')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['bedrijf_id']);
            $table->foreign('bedrijf_id')->references('id')->on('bedrijven')->onDelete('cascade');
        });
    }
};
