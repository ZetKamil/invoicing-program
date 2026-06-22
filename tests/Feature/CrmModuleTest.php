<?php

use App\Actions\Quotes\CreateQuoteAction;
use App\Enums\QuoteStatus;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\Bedrijf;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('create quote action generates a valid quote', function () {
    $bedrijf = Bedrijf::create(['name' => 'Test Bedrijf', 'slug' => 'test-bedrijf']);
    
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'bedrijf_id' => $bedrijf->id,
    ]);
    
    Auth::login($user);

    $lead = Lead::create([
        'bedrijf_id' => $bedrijf->id,
        'company_name' => 'Acme Corp',
        'contact_person' => 'John Doe',
        'email' => 'john@acme.com',
    ]);

    $action = new CreateQuoteAction();
    $quote = $action->handle($lead, 1500.50, 14);

    expect($quote)->toBeInstanceOf(Quote::class)
        ->and($quote->customer->email)->toBe($lead->email)
        ->and($quote->bedrijf_id)->toBe($bedrijf->id)
        ->and($quote->total_amount)->toEqual(1500.50)
        ->and($quote->status)->toBe(QuoteStatus::DRAFT)
        ->and($quote->quote_number)->toStartWith('Q-' . now()->format('Ymd') . '-');
});

test('quote overdue scope filters correctly', function () {
    $bedrijf = Bedrijf::create(['name' => 'Test Bedrijf 2', 'slug' => 'test-bedrijf2']);
    
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin2@test.com',
        'password' => bcrypt('password'),
        'bedrijf_id' => $bedrijf->id,
    ]);
    
    Auth::login($user);

    $customer = Customer::create([
        'bedrijf_id' => $bedrijf->id,
        'company_name' => 'Beta Corp',
        'contact_person' => 'Jane Doe',
    ]);

    // Active Quote (not overdue)
    Quote::create([
        'bedrijf_id' => $bedrijf->id,
        'customer_id' => $customer->id,
        'quote_number' => 'Q-ACTIVE-001',
        'total_amount' => 1000,
        'valid_until' => now()->addDays(5),
        'status' => QuoteStatus::SENT,
    ]);

    // Overdue Quote
    $overdueQuote = Quote::create([
        'bedrijf_id' => $bedrijf->id,
        'customer_id' => $customer->id,
        'quote_number' => 'Q-OVERDUE-001',
        'total_amount' => 2000,
        'valid_until' => now()->subDays(1),
        'status' => QuoteStatus::SENT,
    ]);

    // Accepted Quote (even if past valid_until, it shouldn't be overdue)
    Quote::create([
        'bedrijf_id' => $bedrijf->id,
        'customer_id' => $customer->id,
        'quote_number' => 'Q-ACCEPTED-001',
        'total_amount' => 3000,
        'valid_until' => now()->subDays(5),
        'status' => QuoteStatus::ACCEPTED,
    ]);

    $overdueQuotes = Quote::overdue()->get();

    expect($overdueQuotes)->toHaveCount(1)
        ->and($overdueQuotes->first()->id)->toBe($overdueQuote->id);
});
