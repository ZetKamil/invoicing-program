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
            $taxRate = 21.00;
            $subtotal = $quote->total_amount;
            $taxTotal = round($subtotal * ($taxRate / 100), 2);
            $totalAmount = round($subtotal + $taxTotal, 2);

            $currentYear = date('Y');
            $count = Invoice::where('tenant_id', $quote->tenant_id)
                ->whereYear('created_at', $currentYear)
                ->count();
            
            $invoiceNumber = 'INV-' . $currentYear . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'tenant_id' => $quote->tenant_id,
                'customer_type' => get_class($quote->lead),
                'customer_id' => $quote->lead_id,
                'invoice_number' => $invoiceNumber,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'total_amount' => $totalAmount,
                'due_date' => now()->addDays(30),
                'status' => InvoiceStatus::DRAFT,
            ]);

            // Since QuoteItems do not exist, we create a generic line item representing the quote.
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Services as per Quote ' . $quote->quote_number,
                'quantity' => 1,
                'unit_price' => $subtotal,
                'tax_rate' => $taxRate,
                'total' => $subtotal,
            ]);

            return $invoice;
        });
    }
}
