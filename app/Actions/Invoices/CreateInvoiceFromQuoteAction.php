<?php

namespace App\Actions\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class CreateInvoiceFromQuoteAction
{
    /**
     * Convert an accepted Quote into a Draft Invoice.
     */
    public function execute(Quote $quote): Invoice
    {
        return DB::transaction(function () use ($quote) {
            // FINANCIAL PRECISION: BCMath string arithmetic for all tax/total calculations.
            // round() uses PHP float internally and is NOT safe for financial arithmetic.
            // bcmul/bcadd operate on arbitrary-precision decimal strings, eliminating IEEE 754 drift.
            $taxRate  = '21.00';
            $subtotal = (string) $quote->total_amount;

            // Tax = subtotal × 21%  → bcmul with scale 2 rounds to cent precision
            $taxTotal    = bcmul($subtotal, bcdiv($taxRate, '100', 10), 2);
            // Total = subtotal + tax → bcadd with scale 2
            $totalAmount = bcadd($subtotal, $taxTotal, 2);

            $currentYear = date('Y');
            $count = Invoice::where('tenant_id', $quote->tenant_id)
                ->whereYear('created_at', $currentYear)
                ->count();
            
            $invoiceNumber = 'INV-' . $currentYear . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'tenant_id'      => $quote->tenant_id,
                'customer_type'  => get_class($quote->lead),
                'customer_id'    => $quote->lead_id,
                'invoice_number' => $invoiceNumber,
                'subtotal'       => $subtotal,
                'tax_total'      => $taxTotal,
                'total_amount'   => $totalAmount,
                'due_date'       => now()->addDays(30),
                'status'         => InvoiceStatus::DRAFT,
            ]);

            $product = \App\Models\Product::firstOrCreate(
                ['tenant_id' => $quote->tenant_id, 'name' => 'Custom Quote Service'],
                ['description' => 'Generic service for quotes', 'price' => 0, 'type' => \App\Enums\ProductType::SERVICE]
            );

            // Since QuoteItems do not exist, we create a generic line item representing the quote.
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'product_id'  => $product->id,
                'description' => 'Services as per Quote ' . $quote->quote_number,
                'quantity'    => 1,
                'unit_price'  => $subtotal,
                'tax_rate'    => $taxRate,
                'total'       => $subtotal,
            ]);

            return $invoice;
        });
    }
}
