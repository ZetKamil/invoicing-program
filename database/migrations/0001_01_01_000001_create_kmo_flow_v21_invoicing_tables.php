<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. PRODUCTS
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0.00);
            $table->string('type', 30)->default('service');
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type'], 'idx_products_tenant_type');
        });

        // 2. QUOTES
        Schema::create('quotes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUlid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('quote_number', 100);
            $table->string('status', 30)->default('draft');
            $table->date('valid_until')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();

            // Logistics fields (Transport & CMR)
            $table->string('trailer_type', 100)->nullable();
            $table->string('cmr_number', 100)->nullable();
            $table->string('truck_license_plate', 100)->nullable();
            $table->string('trailer_license_plate', 100)->nullable();
            $table->string('loading_address', 255)->nullable();
            $table->string('delivery_address', 255)->nullable();
            $table->date('loading_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->integer('cargo_weight_kg')->nullable();
            $table->integer('pallet_count')->nullable();
            $table->string('incoterms', 10)->nullable();
            $table->string('driver_name', 255)->nullable();
            $table->string('driver_phone', 50)->nullable();
            $table->boolean('is_reverse_charge')->default(false);
            $table->timestamp('last_reminded_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'quote_number'], 'idx_quotes_tenant_number');
            $table->index(['tenant_id', 'status'], 'idx_quotes_tenant_status');
        });

        // 3. QUOTE_ITEMS
        Schema::create('quote_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->string('description', 255);
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(21.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->timestamps();

            $table->index('quote_id', 'idx_quote_items_quote');
        });

        // 4. INVOICES
        Schema::create('invoices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('customer_type', 100);
            $table->char('customer_id', 26);
            $table->foreignUlid('quote_id')->nullable()->constrained('quotes')->nullOnDelete();
            $table->string('invoice_number', 100);

            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('tax_total', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);

            $table->string('ubl_xml_path', 500)->nullable();
            $table->string('buyer_reference', 255)->nullable();
            $table->date('due_date');

            $table->string('stripe_payment_intent_id', 100)->nullable()->index('idx_invoices_stripe_pi');
            $table->timestamp('paid_at')->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamp('last_reminder_sent_at')->nullable();

            // Logistics & CMR
            $table->string('trailer_type', 100)->nullable();
            $table->string('cmr_number', 100)->nullable();
            $table->string('truck_license_plate', 100)->nullable();
            $table->string('trailer_license_plate', 100)->nullable();
            $table->string('loading_address', 255)->nullable();
            $table->string('delivery_address', 255)->nullable();
            $table->date('loading_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->integer('cargo_weight_kg')->nullable();
            $table->integer('pallet_count')->nullable();
            $table->string('incoterms', 10)->nullable();
            $table->string('driver_name', 255)->nullable();
            $table->string('driver_phone', 50)->nullable();
            $table->boolean('is_reverse_charge')->default(false);
            $table->string('cmr_document_path', 500)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'invoice_number'], 'idx_invoices_tenant_number');
            $table->index(['tenant_id', 'status', 'due_date'], 'idx_invoices_tenant_reporting');
            $table->index(['customer_type', 'customer_id'], 'idx_invoices_polymorphic_customer');
        });

        // 5. INVOICE_ITEMS
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->char('product_id', 26)->nullable();
            $table->string('description', 255);
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(21.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->timestamps();

            $table->index('invoice_id', 'idx_invoice_items_invoice');
            $table->index('product_id', 'idx_invoice_items_product');
        });

        // 6. COMMUNICATIONS
        Schema::create('communications', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('subject', 255);
            $table->text('body');
            $table->string('type', 50);
            $table->string('related_type', 100)->nullable();
            $table->char('related_id', 26)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'type'], 'idx_comm_tenant_type');
            $table->index(['related_type', 'related_id'], 'idx_comm_related');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('products');
    }
};
