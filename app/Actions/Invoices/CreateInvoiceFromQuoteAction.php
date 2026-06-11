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

            // Tax = subtotal Ă— 21%  â†’ bcmul with scale 2 rounds to cent precision
            $taxTotal    = bcmul($subtotal, bcdiv($taxRate, '100', 10), 2);
            // Total = subtotal + tax â†’ bcadd with scale 2
            $totalAmount = bcadd($subtotal, $taxTotal, 2);

            $currentYear = date('Y');
            $count = Invoice::where('bedrijf_id', $quote->bedrijf_id)
                ->whereYear('created_at', $currentYear)
                ->count();
            
            $invoiceNumber = 'INV-' . $currentYear . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'bedrijf_id'             => $quote->bedrijf_id,
                'customer_type'         => get_class($quote->lead),
                'customer_id'           => $quote->lead_id,
                'quote_id'              => $quote->id,
                'invoice_number'        => $invoiceNumber,
                'trailer_type'          => $quote->trailer_type,
                'cargo_weight_kg'       => $quote->cargo_weight_kg,
                'pallet_count'          => $quote->pallet_count,
                'loading_address'       => $quote->loading_address,
                'loading_date'          => $quote->loading_date,
                'delivery_address'      => $quote->delivery_address,
                'delivery_date'         => $quote->delivery_date,
                'incoterms'             => $quote->incoterms,
                'notes'                 => $quote->description,
                'subtotal'              => $subtotal,
                'tax_total'             => $taxTotal,
                'total_amount'          => $totalAmount,
                'due_date'              => now()->addDays(30),
                'status'                => InvoiceStatus::DRAFT,
            ]);

            // Copy items if they exist, else create a generic one
            if ($quote->items && $quote->items->count() > 0) {
                foreach ($quote->items as $item) {
                    InvoiceItem::create([
                        'invoice_id'  => $invoice->id,
                        'product_id'  => $item->product_id, // can be null
                        'description' => $item->description,
                        'quantity'    => $item->quantity,
                        'unit_price'  => $item->unit_price,
                        'tax_rate'    => $item->tax_rate,
                        'total'       => $item->total,
                    ]);
                }
            } else {
                $product = \App\Models\Product::firstOrCreate(
                    ['bedrijf_id' => $quote->bedrijf_id, 'name' => 'Custom Quote Service'],
                    ['description' => 'Generic service for quotes', 'price' => 0, 'type' => \App\Enums\ProductType::SERVICE]
                );

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $product->id,
                    'description' => 'Services as per Quote ' . $quote->quote_number,
                    'quantity'    => 1,
                    'unit_price'  => $subtotal,
                    'tax_rate'    => $taxRate,
                    'total'       => $subtotal,
                ]);
            }

            return $invoice;
        });
    }
}
