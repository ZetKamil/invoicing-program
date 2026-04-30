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
}
