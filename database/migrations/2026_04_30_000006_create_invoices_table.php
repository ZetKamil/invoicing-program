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
        Schema::create('invoices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('bedrijf_id')->constrained('bedrijven')->cascadeOnDelete();
            $table->ulidMorphs('customer'); // ULID IDs for Lead/User
            $table->string('invoice_number');
            
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            
            $table->string('ubl_xml_path')->nullable();
            $table->string('buyer_reference')->nullable();
            $table->date('due_date');
            
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            
            $table->string('status')->default('draft')->index();
            $table->timestamp('last_reminder_sent_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['bedrijf_id', 'invoice_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
