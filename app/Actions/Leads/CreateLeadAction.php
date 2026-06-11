<?php

namespace App\Actions\Leads;

use App\Enums\LeadStatus;
use App\Events\LeadSubmittedEvent;
use App\Models\Lead;
use App\Models\Bedrijf;

class CreateLeadAction
{
    /**
     * Execute the action to create a lead.
     *
     * After saving the Lead, dispatches LeadSubmittedEvent which broadcasts
     * via Laravel Reverb to the private `dispatcher.{bedrijfId}` WebSocket channel.
     * Dispatchers see a real-time notification in the Filament dashboard without
     * refreshing â€” eliminating the polling latency bottleneck.
     */
    public function execute(array $data): Lead
    {
        // For public inquiries, we assign to the primary agency bedrijf (Logi-Web PRO)
        $agency = Bedrijf::where('slug', 'logi-web-pro')->firstOrFail();

        $lead = Lead::create([
            'bedrijf_id'      => $agency->id,
            'company_name'   => $data['company_name'],
            'contact_person' => $data['contact_person'],
            'email'          => $data['email'],
            'phone'          => $data['phone'] ?? null,
            'status'         => LeadStatus::NEW,
            'metadata'       => [
                'package' => $data['package'] ?? 'unknown',
                'source'  => 'landing_page',
                'message' => $data['message'] ?? null,
            ],
        ]);

        // Broadcast real-time notification to all authenticated dispatchers
        // belonging to this bedrijf via the private Reverb WebSocket channel.
        // Uses ShouldBroadcast â€” dispatched through the queue for zero HTTP latency.
        LeadSubmittedEvent::dispatch($lead);

        return $lead;
    }
}

