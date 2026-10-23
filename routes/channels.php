<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channel Authorization
|--------------------------------------------------------------------------
|
| Channel authorization callbacks are the gatekeepers for private WebSocket
| channels. When a browser subscribes to `private-dispatcher.{bedrijfId}`,
| Laravel calls this callback with the authenticated user.
|
| The callback must return true/false. If false, the subscription is rejected
| and the client receives a 403 — preventing unauthorized users from listening
| to another bedrijf's real-time notifications.
|
*/

/**
 * Private channel: dispatcher.{bedrijfId}
 *
 * Authorization rules:
 *  - User must be authenticated (enforced by the 'private-' channel prefix)
 *  - User must belong to the same bedrijf as the channel
 *  - User must have a dispatcher or admin role
 *
 * This prevents a malicious authenticated user from subscribing to a
 * competitor bedrijf's notification channel by guessing their bedrijf ULID.
 */
Broadcast::channel('dispatcher.{tenantId}', function ($user, string $tenantId) {
    // Rule 1: The user must belong to the requested tenant.
    if ($user->tenant_id !== $tenantId && ! $user->is_super_admin) {
        return false;
    }

    // Rule 2: Only dispatchers, owners and admins receive lead notifications.
    return in_array($user->role->value ?? $user->role, [
        \App\Enums\UserRole::ADMIN->value,
        \App\Enums\UserRole::OWNER->value,
        \App\Enums\UserRole::DISPATCHER->value,
    ]);
});
