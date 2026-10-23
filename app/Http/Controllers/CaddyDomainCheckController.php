<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaddyDomainCheckController extends Controller
{
    /**
     * Verify whether a given custom domain belongs to an active tenant.
     *
     * Used by Caddy Server's On-Demand TLS feature:
     *   on_demand_tls {
     *       ask http://localhost/caddy/check-domain
     *   }
     *
     * This endpoint prevents SSL exhaustion DDoS attacks by ensuring certificates
     * are ONLY issued for domains verified in the tenants table.
     */
    public function check(Request $request): Response
    {
        $domain = trim(strtolower($request->query('domain', '')));

        if (empty($domain)) {
            return response('Missing domain parameter', 400);
        }

        // Allow app primary host
        $appHost = parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST);
        if ($domain === $appHost || $domain === 'localhost') {
            return response('Primary host authorized', 200);
        }

        // Check if custom_domain belongs to an active tenant
        $isAuthorized = Tenant::where('custom_domain', $domain)
            ->where('status', 'active')
            ->exists();

        if ($isAuthorized) {
            return response('Domain authorized for SSL issuance', 200);
        }

        return response('Domain unauthorized', 404);
    }
}
