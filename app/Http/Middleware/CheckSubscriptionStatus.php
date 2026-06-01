<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // If the user is authenticated and belongs to a tenant
        if ($user && $user->tenant) {
            $status = $user->tenant->stripe_subscription_status;

            // Allow access if status is active or trialing
            if (!in_array($status, ['active', 'trialing'])) {
                // To prevent redirect loops, ensure we are not already on the billing page
                if (!$request->routeIs('billing.inactive')) {
                    return redirect()->route('billing.inactive');
                }
            }
        }

        return $next($request);
    }
}
