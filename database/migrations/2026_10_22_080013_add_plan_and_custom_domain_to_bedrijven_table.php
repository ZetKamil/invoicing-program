<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Dodaje do tabeli 'bedrijven' (tenants) dwie kolumny:
     * - plan: steruje dostępem do modułów (Feature Flag przez TenantPlan Enum).
     *   Domyślna wartość 'start' chroni istniejące rekordy — nie widzą ERP.
     * - custom_domain: umożliwia podpięcie własnej domeny klienta.
     */
    public function up(): void
    {
        Schema::table('bedrijven', function (Blueprint $table) {
            // Kolumna plan jako string — castowana przez model na TenantPlan Enum.
            // default('start') = bezpieczna wartość dla istniejących rekordów.
            $table->string('plan')->default('start')->after('slug');

            // custom_domain: nullable + unique — każda domena należy tylko do 1 tenanta.
            $table->string('custom_domain')->nullable()->unique()->after('plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bedrijven', function (Blueprint $table) {
            $table->dropColumn(['plan', 'custom_domain']);
        });
    }
};
