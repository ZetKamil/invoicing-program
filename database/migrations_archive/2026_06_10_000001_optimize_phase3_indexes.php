<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3 Performance Hardening — Missing Index Audit
 *
 * This migration closes the index gaps identified in the Phase 3 architectural
 * audit. The following tables had unindexed columns that would cause full-table
 * scans under logistics-scale load (100,000+ rows):
 *
 *  1. quote_items    — FK column quote_id has no explicit B-Tree index.
 *                      SQLite does NOT auto-create indexes for FK constraints.
 *                      Every $quote->items() call scans the full quote_items table.
 *
 *  2. invoice_items  — Same FK index gap as quote_items for invoice_id.
 *
 *  3. invoices       — Missing composite [bedrijf_id, due_date] index.
 *                      The SendInvoiceReminders artisan command filters by
 *                      bedrijf_id + due_date across the full invoices table.
 *                      Without this index, the cron job kills DB performance
 *                      at scale (every night it did a full table scan).
 *
 *  4. communications — No indexes at all on a table that stores polymorphic
 *                      activity logs queried per-invoice and per-quote.
 *                      The composite [bedrijf_id, related_type, related_id] index
 *                      turns polymorphic log queries from O(n) to O(log n).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. quote_items — explicit FK index on quote_id
        // SQLite does not automatically create a B-Tree index when you define
        // a foreign key constraint via foreignUlid()->constrained(). Without
        // this index, every Eloquent $quote->items() relationship call triggers
        // a full table scan across quote_items.
        Schema::table('quote_items', function (Blueprint $table) {
            $table->index('quote_id', 'quote_items_quote_id_index');
        });

        // 2. invoice_items — explicit FK index on invoice_id
        // Same reasoning as quote_items above. The $invoice->items() relationship
        // and the InvoiceItemObserver both fire on this FK column constantly.
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->index('invoice_id', 'invoice_items_invoice_id_index');
        });

        // 3. invoices — composite index on [bedrijf_id, due_date]
        // The SendInvoiceReminders artisan command runs nightly and filters:
        //   WHERE status = 'sent' AND due_date < today()
        // On the invoices table (already indexed by [bedrijf_id, deleted_at, status]),
        // a date-range scan still needs due_date in the index. This composite
        // allows the DB engine to use an index for the full nightly cron query
        // instead of a post-filter on the status index results.
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['bedrijf_id', 'due_date'], 'invoices_bedrijf_due_date_index');
        });

        // 4. communications — composite index on [bedrijf_id, related_type, related_id]
        // The communications table stores polymorphic activity logs. Every time
        // a user views an Invoice or Quote detail page, Filament/Eloquent queries:
        //   WHERE bedrijf_id = ? AND related_type = 'App\Models\Invoice' AND related_id = ?
        // Without this index, every such query scans the full communications table.
        Schema::table('communications', function (Blueprint $table) {
            $table->index(
                ['bedrijf_id', 'related_type', 'related_id'],
                'communications_bedrijf_related_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communications', function (Blueprint $table) {
            $table->dropIndex('communications_bedrijf_related_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_bedrijf_due_date_index');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex('invoice_items_invoice_id_index');
        });

        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropIndex('quote_items_quote_id_index');
        });
    }
};
