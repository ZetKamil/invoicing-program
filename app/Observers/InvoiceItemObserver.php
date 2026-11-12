<?php

namespace App\Observers;

use App\Models\InvoiceItem;

class InvoiceItemObserver
{
    /**
     * Handle the InvoiceItem "saving" event.
     */
    public function saving(InvoiceItem $invoiceItem): void
    {
        // FINANCIAL PRECISION: Use BCMath string arithmetic — never PHP float multiplication.
        // IEEE 754 float drift (e.g. 3 × 14.90 × 1.21 = 54.0869999...) causes cent mismatches
        // that fail Peppol UBL 2.1 schema validation and produce incorrect Stripe charges.
        //
        // Pattern: calculate (quantity × unit_price) × (1 + tax_rate / 100)
        // All operands cast to string, intermediate scale=10 for precision, final scale=2.
        $quantity  = (string) $invoiceItem->quantity;
        $unitPrice = (string) $invoiceItem->unit_price;
        $taxRate   = (string) $invoiceItem->tax_rate;

        $taxMultiplier = bcadd('1', bcdiv($taxRate, '100', 10), 10);
        $lineTotal     = bcmul(bcmul($quantity, $unitPrice, 10), $taxMultiplier, 2);

        $invoiceItem->total = $lineTotal;
    }

    /**
     * Handle the InvoiceItem "saved" event.
     */
    public function saved(InvoiceItem $invoiceItem): void
    {
        if ($invoiceItem->invoice) {
            $invoiceItem->invoice->recalculateTotals();
        }
    }

    /**
     * Handle the InvoiceItem "deleted" event.
     */
    public function deleted(InvoiceItem $invoiceItem): void
    {
        if ($invoiceItem->invoice) {
            $invoiceItem->invoice->recalculateTotals();
        }
    }
}
