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
        Schema::table('bedrijven', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->after('stripe_id');
            $table->string('stripe_subscription_status')->nullable()->after('stripe_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bedrijven', function (Blueprint $table) {
            $table->dropColumn(['stripe_subscription_id', 'stripe_subscription_status']);
        });
    }
};
