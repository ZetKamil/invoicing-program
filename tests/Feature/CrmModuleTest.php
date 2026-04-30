<?php

use App\Actions\Quotes\CreateQuoteAction;
use App\Enums\QuoteStatus;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('create quote action generates a valid quote', function () {
    $tenant = Tenant::create(['name' => 'Test Tenant', 'slug' => 'test-tenant']);
    
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'tenant_id' => $tenant->id,
    ]);
    
    Auth::login($user);

    $lead = Lead::create([
        'company_name' => 'Acme Corp',
        'contact_person' => 'John Doe',
        'email' => 'john@acme.com',
    ]);

    $action = new CreateQuoteAction();
    $quote = $action->handle($lead, 1500.50, 14);

    expect($quote)->toBeInstanceOf(Quote::class)
        ->and($quote->lead_id)->toBe($lead->id)
        ->and($quote->tenant_id)->toBe($tenant->id)
        ->and($quote->total_amount)->toEqual(1500.50)
        ->and($quote->status)->toBe(QuoteStatus::DRAFT)
        ->and($quote->quote_number)->toStartWith('Q-' . now()->format('Ymd') . '-');
});

test('quote overdue scope filters correctly', function () {
    $tenant = Tenant::create(['name' => 'Test Tenant', 'slug' => 'test-tenant2']);
    
    $user = User::create([
        'name' => 'Admin',
        'email' => 'admin2@test.com',
        'password' => bcrypt('password'),
        'tenant_id' => $tenant->id,
    ]);
    
    Auth::login($user);

    $lead = Lead::create([
        'company_name' => 'Beta Corp',
        'contact_person' => 'Jane Doe',
    ]);

    // Active Quote (not overdue)
    Quote::create([
        'lead_id' => $lead->id,
        'quote_number' => 'Q-ACTIVE-001',
        'total_amount' => 1000,
        'valid_until' => now()->addDays(5),
        'status' => QuoteStatus::SENT,
    ]);

    // Overdue Quote
    $overdueQuote = Quote::create([
        'lead_id' => $lead->id,
        'quote_number' => 'Q-OVERDUE-001',
        'total_amount' => 2000,
        'valid_until' => now()->subDays(1),
        'status' => QuoteStatus::SENT,
    ]);

    // Accepted Quote (even if past valid_until, it shouldn't be overdue)
    Quote::create([
        'lead_id' => $lead->id,
        'quote_number' => 'Q-ACCEPTED-001',
        'total_amount' => 3000,
        'valid_until' => now()->subDays(5),
        'status' => QuoteStatus::ACCEPTED,
    ]);

    $overdueQuotes = Quote::overdue()->get();

    expect($overdueQuotes)->toHaveCount(1)
        ->and($overdueQuotes->first()->id)->toBe($overdueQuote->id);
});
