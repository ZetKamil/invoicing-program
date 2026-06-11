<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Livewire\Attributes\On;

/**
 * Real-Time Lead Notification Widget for the Filament Dispatcher Dashboard.
 *
 * Broadcasting Architecture:
 *  This widget listens for the `lead.submitted` broadcast event via Laravel Reverb
 *  WebSockets. When a potential client submits a lead inquiry on the public website,
 *  the dispatcher sees an instant in-dashboard notification toast WITHOUT refreshing.
 *
 *  Technical Flow:
 *  1. Visitor submits PackageInquiryForm on the public landing page
 *  2. CreateLeadAction saves the Lead → dispatches LeadSubmittedEvent
 *  3. LeadSubmittedEvent broadcasts on `private-dispatcher.{tenantId}` via Reverb
 *  4. Filament dashboard receives the event via the persistent WebSocket connection
 *  5. Livewire's #[On('echo-private:dispatcher.{tenantId},lead.submitted')] fires
 *  6. $this->latestLeads array is updated → Livewire re-renders the widget
 *  7. Dispatcher sees the new lead appear in their dashboard in real time
 *
 *  The `echo-private:` prefix is the Livewire 4 convention for listening to
 *  Laravel Echo private channel events via Reverb WebSocket.
 */
class LiveLeadNotificationWidget extends Widget
{
    protected string $view = 'filament.widgets.live-lead-notification-widget';

    /**
     * Widget positioning — shown prominently at the top of the dashboard.
     */
    protected static ?int $sort = -2;

    /**
     * Full width across the Filament dashboard grid.
     */
    protected int | string | array $columnSpan = 'full';

    /**
     * Buffer of the most recent live leads received via WebSocket.
     * Maximum 5 entries to avoid unbounded memory growth.
     *
     * @var array<array<string, string>>
     */
    public array $latestLeads = [];

    /**
     * Count of leads received since the dispatcher last cleared the notifications.
     */
    public int $unseenCount = 0;

    /**
     * Listen for the real-time lead.submitted broadcast event.
     *
     * The attribute name follows the Livewire 4 Echo naming convention:
     *   echo-private:{channelName},{eventName}
     *
     * {tenantId} is resolved dynamically by Livewire from the authenticated user's
     * tenant_id — preventing the dispatcher from subscribing to wrong channels.
     *
     * @param  array<string, string> $data  The broadcastWith() payload from LeadSubmittedEvent
     */
    #[On('echo-private:dispatcher.{tenant_id},lead.submitted')]
    public function onLeadSubmitted(array $data): void
    {
        // Prepend to keep newest first, cap at 5 entries
        array_unshift($this->latestLeads, [
            'company_name'   => $data['company_name'] ?? 'Unknown',
            'contact_person' => $data['contact_person'] ?? '',
            'email'          => $data['email'] ?? '',
            'package'        => $data['package'] ?? 'unknown',
            'received_at'    => now()->format('H:i:s'),
        ]);

        $this->latestLeads = array_slice($this->latestLeads, 0, 5);
        $this->unseenCount++;

        // Send a Filament toast notification alongside the widget update
        \Filament\Notifications\Notification::make()
            ->title('🚛 New Lead Received!')
            ->body(($data['company_name'] ?? 'Unknown') . ' — ' . ($data['package'] ?? '') . ' package')
            ->success()
            ->duration(8000)
            ->send();
    }

    /**
     * Resolve the tenant_id for channel name substitution.
     * Livewire replaces {tenant_id} in the #[On] attribute with this value.
     */
    public function getTenantIdProperty(): string
    {
        return auth()->user()?->tenant_id ?? '';
    }

    /**
     * Clear the notification buffer.
     */
    public function clearNotifications(): void
    {
        $this->latestLeads = [];
        $this->unseenCount = 0;
    }
}
