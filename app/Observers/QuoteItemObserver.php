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
        $quoteItem->total = $quoteItem->quantity * $quoteItem->unit_price * (1 + $quoteItem->tax_rate / 100);
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
