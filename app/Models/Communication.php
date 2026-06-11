<?php

namespace App\Models;

use App\Enums\CommType;
use App\Traits\HasBedrijf;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['bedrijf_id', 'subject', 'body', 'type', 'related_type', 'related_id', 'sent_at', 'opened_at'])]
class Communication extends Model
{
    /** @use HasFactory<\Database\Factories\CommunicationFactory> */
    use HasFactory, HasUlids, HasBedrijf;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CommType::class,
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
        ];
    }

    /**
     * Get the related model (Quote or Invoice).
     */
    public function related_model(): MorphTo
    {
        return $this->morphTo('related');
    }
}
