<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Page;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    /**
     * Resolve and render either a dynamic block-based page or the default homepage.
     */
    public function render(Request $request, ?string $slug = null): View
    {
        $tenant = $this->resolveTenant($request);

        // 1. If explicit slug provided or query param '?page=...'
        $targetSlug = $slug ?: $request->query('page');

        if ($tenant) {
            $pageQuery = Page::where('tenant_id', $tenant->id)->where('is_published', true);

            if ($targetSlug) {
                $page = $pageQuery->where('slug', $targetSlug)->first();
            } else {
                $page = $pageQuery->where('is_homepage', true)->first();
            }

            if ($page && !empty($page->blocks)) {
                return view('pages.dynamic', [
                    'page' => $page,
                    'tenant' => $tenant,
                ]);
            }
        }

        // 2. Fallback to default presentation
        return view('pages.home', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Handle public contact form submissions and atomically register a Lead.
     */
    public function submitContact(Request $request): RedirectResponse
    {
        // Bot honeypot check
        if (!empty($request->input('website_hp'))) {
            return back()->with('contact_success', 'Uw bericht is ontvangen.');
        }

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string|max:3000',
        ]);

        Lead::create([
            'id' => (string) Str::ulid(),
            'tenant_id' => $validated['tenant_id'],
            'name' => $validated['name'],
            'company_name' => $validated['name'],
            'contact_person' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'source' => 'contact_form',
            'status' => 'new',
            'notes' => $validated['message'],
        ]);

        return back()->with(
            'contact_success',
            'Hartelijk dank voor uw bericht! Wij hebben uw aanvraag goed ontvangen en nemen spoedig contact met u op.'
        );
    }

    /**
     * Resolve the active tenant based on custom domain, query param, or system default.
     */
    protected function resolveTenant(Request $request): ?Tenant
    {
        // 1. Custom Domain match
        $host = $request->getHost();
        $byDomain = Tenant::where('custom_domain', $host)->first();
        if ($byDomain) {
            return $byDomain;
        }

        // 2. Query param '?tenant=slug'
        if ($request->filled('tenant')) {
            $bySlug = Tenant::where('slug', $request->query('tenant'))->first();
            if ($bySlug) {
                return $bySlug;
            }
        }

        // 3. Fallback to first registered tenant
        return Tenant::first();
    }
}
