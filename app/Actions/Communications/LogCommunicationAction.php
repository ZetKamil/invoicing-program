<?php

namespace App\Actions\Communications;

use App\Enums\CommType;
use App\Models\Communication;

class LogCommunicationAction
{
    /**
     * Record a communication log in the database.
     */
    public function execute(string $bedrijfId, string $subject, $relatedModel, CommType $type, ?string $body = null): Communication
    {
        return Communication::create([
            'bedrijf_id' => $bedrijfId,
            'subject' => $subject,
            'body' => $body ?? '',
            'related_type' => get_class($relatedModel),
            'related_id' => $relatedModel->id,
            'type' => $type,
            'sent_at' => now(),
        ]);
    }
}
