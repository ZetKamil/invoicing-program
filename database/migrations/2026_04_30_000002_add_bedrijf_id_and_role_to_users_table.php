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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUlid('bedrijf_id')->after('id')->nullable()->constrained('bedrijven')->cascadeOnDelete();
            $table->string('role')->after('email')->default('dispatcher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['bedrijf_id']);
            $table->dropColumn(['bedrijf_id', 'role']);
        });
    }
};
