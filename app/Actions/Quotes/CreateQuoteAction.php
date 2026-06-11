<?php

namespace App\Actions\Quotes;

use App\Enums\QuoteStatus;
use App\Models\Lead;
use App\Models\Quote;
use Illuminate\Support\Str;

class CreateQuoteAction
{
    /**
     * Handle the generation of a new smart quote from a lead.
     */
    public function handle(Lead $lead, int $validDays = 14): Quote
    {
        $quote = new Quote();
        $quote->tenant_id = $lead->tenant_id;
        $quote->lead_id = $lead->id;
        $quote->quote_number = $this->generateQuoteNumber();
        $quote->valid_until = now()->addDays($validDays);
        $quote->status = QuoteStatus::DRAFT;
        $quote->total_amount = '0.00';
        
        // Copy entire metadata block from lead (so we don't lose rich form data like container types, addresses, etc.)
        $quote->metadata = $lead->metadata;
        
        // ROAD TRANSPORT MVP: Extract official road transport fields if they exist in the lead's metadata
        $quote->trailer_type = $lead->metadata['trailer_type'] ?? null;
        $quote->loading_address = $lead->metadata['loading_address'] ?? null;
        $quote->delivery_address = $lead->metadata['delivery_address'] ?? null;
        $quote->loading_date = $lead->metadata['loading_date'] ?? null;
        $quote->delivery_date = $lead->metadata['delivery_date'] ?? null;
        $quote->cargo_weight_kg = $lead->metadata['cargo_weight_kg'] ?? null;
        $quote->pallet_count = $lead->metadata['pallet_count'] ?? null;
        
        // Use the lead's message as a custom description/cover letter
        $message = $lead->metadata['message'] ?? '';
        $quote->description = $message ? "Bedankt voor uw aanvraag!\n\n" . $message : 'Offerte opgesteld op basis van uw aanvraag.';
        
        $quote->save();

        // SMART QUOTES LOGIC: Map the requested package to actual products
        $package = strtolower($lead->metadata['package'] ?? 'unknown');
        
        // Search for a product that loosely matches the package name for this tenant
        $product = \App\Models\Product::where('tenant_id', $lead->tenant_id)
            ->where('name', 'like', "%{$package}%")
            ->first();

        if ($product) {
            $quote->items()->create([
                'description' => $product->name . ($product->description ? " - " . $product->description : ''),
                'quantity' => 1,
                'unit_price' => $product->price,
                'tax_rate' => 21.00, // Or whatever the product tax rate is
            ]);
        } else {
            // Fallback if product not found in database
            $fallbackAmount = match($package) {
                'start'      => '99.00',
                'pro'        => '199.00',
                'enterprise' => '0.00',
                default      => '500.00',
            };
            
            $quote->items()->create([
                'description' => 'Pakiet: ' . ucfirst($package) . ' (Uzupełnij opis szczegółowy)',
                'quantity' => 1,
                'unit_price' => $fallbackAmount,
                'tax_rate' => 21.00,
            ]);
        }
        
        // Recalculate totals (the observer handles line totals, we just trigger the roll-up)
        $quote->recalculateTotals();

        return $quote;
    }

    /**
     * Generate a unique quote number.
     */
    protected function generateQuoteNumber(): string
    {
        $prefix = 'Q-' . now()->format('Ymd') . '-';
        $random = strtoupper(Str::random(4));
        
        $quoteNumber = $prefix . $random;

        // Ensure uniqueness
        while (Quote::where('quote_number', $quoteNumber)->exists()) {
            $random = strtoupper(Str::random(4));
            $quoteNumber = $prefix . $random;
        }

        return $quoteNumber;
    }
}
