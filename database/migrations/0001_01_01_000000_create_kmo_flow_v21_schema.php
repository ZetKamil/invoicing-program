<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TENANTS
        Schema::create('tenants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 255);
            $table->string('slug', 100)->unique('idx_tenants_slug');
            $table->string('custom_domain', 255)->nullable()->unique('idx_tenants_custom_domain');
            $table->string('plan', 20)->default('start');
            $table->string('status', 20)->default('active');
            $table->string('vat_number', 50)->nullable();
            $table->string('kbo_number', 20)->nullable();
            $table->string('peppol_endpoint_id', 50)->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('bic', 11)->nullable();
            $table->json('settings')->nullable();
            $table->string('stripe_customer_id', 100)->nullable()->index();
            $table->string('stripe_subscription_id', 100)->nullable();
            $table->timestamps();

            $table->index(['plan', 'status'], 'idx_tenants_plan_status');
        });

        // 2. USERS
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 255);
            $table->string('email', 255)->unique('idx_users_email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->boolean('is_super_admin')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUlid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 3. TENANT_USER (Pivot dla Filament v3 Multi-Tenancy)
        Schema::create('tenant_user', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 30)->default('owner');
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id'], 'idx_tenant_user_unique');
            $table->index(['user_id', 'tenant_id'], 'idx_tenant_user_lookup');
        });

        // 4. AUDIT_LOGS
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('auditable_type', 100);
            $table->char('auditable_id', 26);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'auditable_type', 'auditable_id'], 'idx_audit_lookup');
        });

        // 5. CATEGORIES
        Schema::create('categories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('type', 20); // 'catalog' lub 'post'
            $table->string('name', 150);
            $table->string('slug', 150);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'type', 'slug'], 'idx_categories_tenant_type_slug');
        });

        // 6. CATALOG_ITEMS
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUlid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('sku', 100)->nullable();
            $table->string('status', 30)->default('available');
            $table->bigInteger('price_minor')->nullable();
            $table->text('description')->nullable();
            $table->json('attributes')->nullable();
            $table->string('whatsapp_prefill_text', 500)->nullable();
            $table->integer('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug'], 'idx_catalog_items_tenant_slug');
            $table->index(['tenant_id', 'status', 'price_minor'], 'idx_catalog_items_status_price');
        });

        // 7. PAGES
        Schema::create('pages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('slug', 150);
            $table->boolean('is_homepage')->default(false);
            $table->boolean('is_published')->default(true);
            $table->json('blocks')->nullable();
            $table->json('seo_meta')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug'], 'idx_pages_tenant_slug');
            $table->index(['tenant_id', 'is_homepage'], 'idx_pages_tenant_homepage');
        });

        // 8. MEDIA
        Schema::create('media', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('model_type', 100);
            $table->char('model_id', 26);
            $table->string('collection_name', 50)->default('default');
            $table->string('file_name', 255);
            $table->string('mime_type', 100);
            $table->string('disk', 50)->default('public');
            $table->bigInteger('size_bytes');
            $table->json('custom_properties')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'model_type', 'model_id', 'collection_name'], 'idx_media_tenant_polymorphic');
        });

        // 9. POSTS
        Schema::create('posts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUlid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255);
            $table->text('excerpt')->nullable();
            $table->json('sections')->nullable();
            $table->string('status', 30)->default('draft');
            $table->boolean('is_ai_generated')->default(false);
            $table->boolean('show_global_announcement')->default(true);
            $table->integer('views_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug'], 'idx_posts_tenant_slug');
            $table->index(['tenant_id', 'status', 'published_at'], 'idx_posts_reporting');
        });

        // 10. DAILY_METRICS
        Schema::create('daily_metrics', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->date('date');
            $table->string('trackable_type', 100);
            $table->char('trackable_id', 26);
            $table->integer('views_count')->default(0);
            $table->integer('whatsapp_clicks')->default(0);
            $table->integer('messenger_clicks')->default(0);
            $table->integer('phone_clicks')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'date', 'trackable_type', 'trackable_id'], 'idx_daily_metrics_unique');
            $table->index(['tenant_id', 'date'], 'idx_daily_metrics_date_lookup');
        });

        // 11. AI_GENERATION_LOGS
        Schema::create('ai_generation_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUlid('post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('model_used', 100);
            $table->string('topic_prompt', 500);
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->integer('estimated_cost_micro_usd')->default(0);
            $table->integer('execution_time_ms');
            $table->string('status', 30);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'created_at'], 'idx_ai_logs_tenant_date');
        });

        // 12. AI_SCHEDULES
        Schema::create('ai_schedules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->string('frequency', 20)->default('weekly');
            $table->integer('day_of_week')->default(1);
            $table->integer('preferred_hour')->default(9);
            $table->boolean('auto_publish')->default(false);
            $table->foreignUlid('target_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->text('niche_guidelines')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable()->index();
            $table->timestamps();
        });

        // 13. CUSTOMERS
        Schema::create('customers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('company_name', 255);
            $table->string('contact_person', 255)->nullable();
            $table->string('email', 255);
            $table->string('phone', 50)->nullable();
            $table->string('vat_number', 50)->nullable();
            $table->string('kbo_number', 20)->nullable();
            $table->string('peppol_endpoint_id', 50)->nullable();
            $table->string('address_line1', 255);
            $table->string('address_line2', 255)->nullable();
            $table->string('postal_code', 20);
            $table->string('city', 100);
            $table->char('country_code', 2)->default('BE');
            $table->timestamps();

            $table->index(['tenant_id', 'vat_number'], 'idx_customers_tenant_vat');
            $table->index(['tenant_id', 'company_name'], 'idx_customers_tenant_name');
        });

        // 14. LEADS (Tworzone po Customers z uwagi na klucz obcy converted_customer_id!)
        Schema::create('leads', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('phone', 50)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('source', 50)->default('contact_form');
            $table->string('status', 30)->default('new');
            $table->string('audit_report_url', 500)->nullable();
            $table->text('notes')->nullable();
            $table->foreignUlid('converted_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamps();
        });

        // 15. TAX_RATES
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->decimal('rate_percentage', 5, 2);
            $table->string('peppol_category_code', 5)->default('S');
            $table->string('peppol_exemption_code', 50)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'idx_tax_rates_tenant_code');
        });

        // 16. COMMERCIAL_DOCUMENTS
        Schema::create('commercial_documents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUlid('customer_id')->constrained('customers')->restrictOnDelete();
            $table->char('parent_document_id', 26)->nullable()->index('idx_commercial_docs_parent');
            $table->string('type', 30);
            $table->string('document_number', 100);
            $table->string('status', 30);
            $table->char('billing_period', 7); // np. '2026-10'
            $table->char('currency', 3)->default('EUR');
            $table->bigInteger('subtotal_minor')->default(0);
            $table->bigInteger('tax_total_minor')->default(0);
            $table->bigInteger('total_minor')->default(0);
            $table->date('issued_at');
            $table->date('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('vcs_reference', 17)->nullable();
            $table->string('ubl_xml_path', 500)->nullable();
            $table->string('peppol_message_id', 255)->nullable();
            $table->string('snapshot_customer_name', 255);
            $table->string('snapshot_customer_vat', 50)->nullable();
            $table->json('snapshot_customer_address')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'type', 'document_number'], 'idx_commercial_docs_tenant_number');
            $table->index(['tenant_id', 'billing_period', 'type', 'status'], 'idx_commercial_docs_reporting');
        });

        // Self-referencing FK dodawany po utworzeniu tabeli (kompatybilność z PostgreSQL)
        Schema::table('commercial_documents', function (Blueprint $table) {
            $table->foreign('parent_document_id')
                ->references('id')
                ->on('commercial_documents')
                ->nullOnDelete();
        });

        // 17. COMMERCIAL_DOCUMENT_LINES
        Schema::create('commercial_document_lines', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUlid('document_id')->constrained('commercial_documents')->cascadeOnDelete();
            $table->foreignUlid('catalog_item_id')->nullable()->constrained('catalog_items')->nullOnDelete();
            $table->foreignUlid('tax_rate_id')->constrained('tax_rates')->restrictOnDelete();
            $table->text('description');
            $table->decimal('quantity', 10, 3)->default(1.000);
            $table->bigInteger('unit_price_minor');
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            $table->bigInteger('line_net_minor');
            $table->bigInteger('line_tax_minor');
            $table->bigInteger('line_gross_minor');
            $table->decimal('snapshot_tax_rate', 5, 2);
            $table->string('snapshot_peppol_cat', 5)->default('S');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'catalog_item_id'], 'idx_doc_lines_tenant_catalog');
            $table->index(['document_id', 'sort_order'], 'idx_doc_lines_document');
        });

        // 18. PAYMENTS
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUlid('document_id')->constrained('commercial_documents')->restrictOnDelete();
            $table->bigInteger('amount_minor');
            $table->string('method', 50);
            $table->string('status', 30)->default('pending');
            $table->string('transaction_reference', 255)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('commercial_document_lines');
        Schema::dropIfExists('commercial_documents');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('ai_schedules');
        Schema::dropIfExists('ai_generation_logs');
        Schema::dropIfExists('daily_metrics');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('media');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('catalog_items');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('tenant_user');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('tenants');
    }
};
