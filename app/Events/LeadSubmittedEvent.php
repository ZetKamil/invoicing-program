<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a new Lead is submitted via the public landing page form.
 *
 * Broadcasting Architecture:
 *  - Implements ShouldBroadcast so Laravel dispatches this event through the
 *    configured broadcast driver (Reverb WebSocket server).
 *  - Broadcasts on a PRIVATE channel `dispatcher.{tenantId}` — the `private-`
 *    prefix is enforced by Laravel Echo on the frontend, and the channel
 *    authorization callback in routes/channels.php verifies the user is an
 *    authenticated member of the target tenant before subscribing.
 *  - Payload is minimal: only the data a dispatcher needs to display the
 *    real-time notification (company name, package, email). Never expose
 *    sensitive internal IDs or relationships in broadcast payloads.
 *
 * Dispatcher Notification Flow:
 *  Public Form Submit → CreateLeadAction → LeadSubmittedEvent::dispatch()
 *      → Reverb Server → Private Channel → Filament Dashboard Widget
 *      → Dispatcher sees toast notification with lead details IN REAL TIME
 */
class LeadSubmittedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly Lead $lead)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * Uses a PRIVATE channel scoped to the tenant — prevents cross-tenant
     * data leakage via WebSocket subscriptions.
     *
     * @return array<Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('dispatcher.' . $this->lead->tenant_id),
        ];
    }

    /**
     * The event name as it appears in the JavaScript listener.
     *
     * Explicit name prevents Laravel from using the full PHP class name
     * (App\\Events\\LeadSubmittedEvent) as the broadcast event name,
     * which would be verbose and brittle on the frontend.
     */
    public function broadcastAs(): string
    {
        return 'lead.submitted';
    }

    /**
     * Data payload sent to the frontend.
     *
     * Minimal safe payload: only display data, no internal IDs or financial data.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'company_name'   => $this->lead->company_name,
            'contact_person' => $this->lead->contact_person,
            'email'          => $this->lead->email,
            'package'        => $this->lead->metadata['package'] ?? 'unknown',
            'submitted_at'   => $this->lead->created_at?->toIso8601String(),
        ];
    }
}
