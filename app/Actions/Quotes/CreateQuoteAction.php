<?php

namespace App\Actions\Quotes;

use App\Enums\QuoteStatus;
use App\Models\Lead;
use App\Models\Quote;
use Illuminate\Support\Str;

class CreateQuoteAction
{
    /**
     * Handle the generation of a new quote from a lead.
     */
    public function handle(Lead $lead, float $totalAmount, int $validDays = 14): Quote
    {
        $quote = new Quote();
        $quote->lead_id = $lead->id;
        $quote->quote_number = $this->generateQuoteNumber();
        $quote->total_amount = $totalAmount;
        $quote->valid_until = now()->addDays($validDays);
        $quote->status = QuoteStatus::DRAFT;
        
        $quote->save();

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
