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
        $invoiceItem->total = $invoiceItem->quantity * $invoiceItem->unit_price * (1 + $invoiceItem->tax_rate / 100);
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
