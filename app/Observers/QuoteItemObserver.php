<?php

namespace App\Observers;

use App\Models\QuoteItem;

class QuoteItemObserver
{
    /**
     * Handle the QuoteItem "saving" event.
     */
    public function saving(QuoteItem $quoteItem): void
    {
        // FINANCIAL PRECISION: BCMath string arithmetic — same pattern as InvoiceItemObserver.
        // Quote totals flow directly into invoice creation and Peppol UBL XML generation.
        // Float drift at this level propagates into tax mismatches that fail Peppol validation.
        $quantity  = (string) $quoteItem->quantity;
        $unitPrice = (string) $quoteItem->unit_price;
        $taxRate   = (string) $quoteItem->tax_rate;

        $taxMultiplier = bcadd('1', bcdiv($taxRate, '100', 10), 10);
        $lineTotal     = bcmul(bcmul($quantity, $unitPrice, 10), $taxMultiplier, 2);

        $quoteItem->total = $lineTotal;
    }

    /**
     * Handle the QuoteItem "saved" event.
     */
    public function saved(QuoteItem $quoteItem): void
    {
        if ($quoteItem->quote) {
            $quoteItem->quote->recalculateTotals();
        }
    }

    /**
     * Handle the QuoteItem "deleted" event.
     */
    public function deleted(QuoteItem $quoteItem): void
    {
        if ($quoteItem->quote) {
            $quoteItem->quote->recalculateTotals();
        }
    }
}
