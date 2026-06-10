<?php

use App\Actions\Leads\CreateLeadAction;
use App\Actions\Quotes\CreateQuoteAction;
use App\Actions\Communications\LogCommunicationAction;
use App\Enums\LeadStatus;
use App\Enums\QuoteStatus;
use App\Livewire\Public\PackageInquiryForm;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\Tenant;
use App\Models\Communication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('full pipeline: visitor to lead to quote with communication log', function () {
    // 1. Setup Agency Tenant
    $tenant = Tenant::create(['name' => 'Logi-Web PRO', 'slug' => 'logi-web-pro']);
    
    // 2. Visitor submits Livewire Form
    Livewire::test(PackageInquiryForm::class)
        ->set('package', 'Enterprise')
        ->set('company_name', 'Mega Trans')
        ->set('contact_person', 'CEO John')
        ->set('email', 'ceo@megatrans.com')
        ->set('phone', '123456789')
        ->call('submit', app(CreateLeadAction::class))
        ->assertDispatched('notify');

    // 3. Verify Lead was created in DB correctly
    $lead = Lead::where('email', 'ceo@megatrans.com')->first();
    
    expect($lead)->not->toBeNull()
        ->and($lead->company_name)->toBe('Mega Trans')
        ->and($lead->tenant_id)->toBe($tenant->id)
        ->and($lead->metadata['package'])->toBe('Enterprise')
        ->and($lead->status)->toBe(LeadStatus::NEW);

    // 4. Employee converts Lead to Quote
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'tenant_id' => $tenant->id,
    ]);
    $this->actingAs($admin);

    $quoteAction = new CreateQuoteAction();
    $quote = $quoteAction->handle($lead, 5000.00, 30);

    expect($quote)->toBeInstanceOf(Quote::class)
        ->and($quote->lead_id)->toBe($lead->id)
        ->and($quote->total_amount)->toEqual(5000.00)
        ->and($quote->status)->toBe(QuoteStatus::DRAFT);

    // 5. Employee sends Quote via email and logs it
    $logAction = new LogCommunicationAction();
    $log = $logAction->execute($tenant->id, 'Email Sent', $quote, \App\Enums\CommType::EMAIL, 'Quote was sent to CEO John via email.');

    expect($log)->toBeInstanceOf(Communication::class)
        ->and($log->related_id)->toBe($quote->id)
        ->and($log->related_type)->toBe($quote->getMorphClass());
        
    // 6. Verify polymorphism works in DB
    $communications = \App\Models\Communication::where('related_id', $quote->id)->get();
    expect($communications)->toHaveCount(1)
        ->and($communications->first()->subject)->toBe('Email Sent');
});
