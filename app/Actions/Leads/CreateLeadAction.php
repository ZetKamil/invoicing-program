<?php

namespace App\Actions\Leads;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Tenant;

class CreateLeadAction
{
    /**
     * Execute the action to create a lead.
     */
    public function execute(array $data): Lead
    {
        // For public inquiries, we assign to the primary agency tenant (Logi-Web PRO)
        $agency = Tenant::where('slug', 'logi-web-pro')->firstOrFail();

        return Lead::create([
            'tenant_id' => $agency->id,
            'company_name' => $data['company_name'],
            'contact_person' => $data['contact_person'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => LeadStatus::NEW,
            'metadata' => [
                'package' => $data['package'] ?? 'unknown',
                'source' => 'landing_page',
                'message' => $data['message'] ?? null,
            ],
        ]);
    }
}
