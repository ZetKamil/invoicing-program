<?php

namespace App\Actions\Tenants;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Page;
use App\Models\TaxRate;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TemplateBlocks;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProvisionTenantAction
{
    /**
     * Provision a complete tenant environment in an atomic database transaction.
     *
     * @param  array<string, mixed> $data
     * @return array{tenant: Tenant, user: User, plain_password: ?string, homepage: Page}
     */
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $companyName = trim($data['company_name']);
            $baseSlug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($companyName);
            $slug = $this->generateUniqueSlug($baseSlug);
            $niche = $data['niche'] ?? TemplateBlocks::NICHE_BLANK;
            $plan = $data['plan'] ?? 'start';
            $provisionedBy = $data['provisioned_by'] ?? 'human';
            $operatorUserId = $data['operator_user_id'] ?? null;

            // 1. Prepare Settings with encrypted payment credentials if provided
            $stripeSecret = $data['stripe_secret_key'] ?? null;
            $stripeWebhookSecret = $data['stripe_webhook_secret'] ?? null;

            // Auto-register Stripe Webhook via Stripe API if secret is provided but webhook is empty
            if (!empty($stripeSecret) && empty($stripeWebhookSecret)) {
                $stripeWebhookSecret = app(\App\Services\StripeService::class)->autoRegisterWebhook($stripeSecret);
            }

            $settings = [
                'theme' => [
                    'primary_color' => $data['primary_color'] ?? '#2563eb',
                    'niche' => $niche,
                ],
                'ai' => [
                    'monthly_post_limit' => match ($plan) {
                        'scale', 'enterprise' => 30,
                        'pro' => 12,
                        default => 4,
                    },
                ],
                'payment_gateways' => [
                    'primary_provider' => $data['primary_payment_provider'] ?? 'mollie',
                    'mollie' => [
                        'api_key' => !empty($data['mollie_api_key']) ? Crypt::encryptString($data['mollie_api_key']) : null,
                    ],
                    'stripe' => [
                        'secret_key' => !empty($stripeSecret) ? Crypt::encryptString($stripeSecret) : null,
                        'publishable_key' => !empty($data['stripe_publishable_key']) ? Crypt::encryptString($data['stripe_publishable_key']) : null,
                        'webhook_secret' => !empty($stripeWebhookSecret) ? Crypt::encryptString($stripeWebhookSecret) : null,
                    ],
                ],
            ];

            // 2. Create Tenant record
            $tenant = Tenant::create([
                'id' => (string) Str::ulid(),
                'name' => $companyName,
                'slug' => $slug,
                'custom_domain' => !empty($data['custom_domain']) ? trim(strtolower($data['custom_domain'])) : null,
                'plan' => $plan,
                'status' => 'active',
                'vat_number' => $data['vat_number'] ?? null,
                'kbo_number' => $data['kbo_number'] ?? null,
                'peppol_endpoint_id' => $data['peppol_endpoint_id'] ?? $data['vat_number'] ?? null,
                'iban' => $data['iban'] ?? null,
                'bic' => $data['bic'] ?? null,
                'settings' => $settings,
            ]);

            // 3. User setup: find or create admin
            $adminEmail = strtolower(trim($data['admin_email']));
            $plainPassword = $data['admin_password'] ?? null;
            $user = User::where('email', $adminEmail)->first();

            if (!$user) {
                if (!$plainPassword) {
                    $plainPassword = Str::password(16);
                }
                $user = User::create([
                    'id' => (string) Str::ulid(),
                    'name' => $data['admin_name'] ?? $companyName . ' Admin',
                    'email' => $adminEmail,
                    'password' => Hash::make($plainPassword),
                    'is_super_admin' => false,
                ]);
            }

            // Attach user to tenant in pivot with 'owner' role and ULID
            $tenant->users()->syncWithoutDetaching([
                $user->id => [
                    'id' => (string) Str::ulid(),
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // 4. Seed Belgian Peppol-compliant Tax Rates
            $this->seedBelgianTaxRates($tenant);

            // 5. Seed default Catalog and Blog Categories
            Category::create([
                'id' => (string) Str::ulid(),
                'tenant_id' => $tenant->id,
                'type' => 'catalog',
                'name' => 'Algemeen',
                'slug' => 'algemeen',
                'sort_order' => 0,
            ]);

            Category::create([
                'id' => (string) Str::ulid(),
                'tenant_id' => $tenant->id,
                'type' => 'post',
                'name' => 'Nieuws & Kennisbank',
                'slug' => 'nieuws-kennisbank',
                'sort_order' => 0,
            ]);

            // 6. Anti-Cannibalization Layout Generation:
            // - For DIY / Zelfbouwen (€199 plan), generate a neutral Wireframe / Blank skeleton.
            //   The merchant must populate their own copy, motivating upsell to Turnkey or expert copywriting add-on (€45/h).
            // - For Turnkey / Pro / Scale (€1 000+ plan), generate rich Flemish sales copywriting and tailored industry blocks.
            $isDiyZelfbouw = in_array(strtolower($plan), ['starter', 'zelfbouwen', 'diy']);

            if ($isDiyZelfbouw) {
                $blocks = TemplateBlocks::getBlankBlocks(
                    $tenant->name,
                    'Uw professionele slogan komt hier. Bewerk deze pagina via het dashboard.'
                );
                $seoMeta = [
                    'meta_title' => $tenant->name,
                    'meta_description' => 'Welkom op de officiële website van ' . $tenant->name,
                ];
            } else {
                $blocks = TemplateBlocks::getForNiche($niche, $tenant->name, $data['tagline'] ?? null);
                $seoMeta = [
                    'meta_title' => $tenant->name . ' | Officiële Website',
                    'meta_description' => $data['tagline'] ?? ($tenant->name . ' - Kwaliteit en betrouwbare service.'),
                ];
            }

            $homepage = Page::create([
                'id' => (string) Str::ulid(),
                'tenant_id' => $tenant->id,
                'title' => 'Home',
                'slug' => 'home',
                'is_homepage' => true,
                'is_published' => true,
                'blocks' => $blocks,
                'seo_meta' => $seoMeta,
            ]);

            // 7. Audit Log recording
            AuditLog::create([
                'id' => (string) Str::ulid(),
                'tenant_id' => $tenant->id,
                'user_id' => $operatorUserId,
                'action' => 'tenant.provisioned',
                'auditable_type' => Tenant::class,
                'auditable_id' => $tenant->id,
                'new_values' => [
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'plan' => $tenant->plan,
                    'niche' => $niche,
                    'owner_email' => $user->email,
                    'provisioned_by' => $provisionedBy,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'KMO-Flow Provisioning Engine',
                'created_at' => now(),
            ]);

            return [
                'tenant' => $tenant->fresh(['users']),
                'user' => $user,
                'plain_password' => $plainPassword,
                'homepage' => $homepage,
            ];
        });
    }

    /**
     * Ensure a slug is unique by appending an incrementing suffix if necessary.
     */
    protected function generateUniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Seed Belgian Peppol BIS 3.0 VAT rates for the tenant.
     */
    protected function seedBelgianTaxRates(Tenant $tenant): void
    {
        $rates = [
            [
                'code' => 'BE_21',
                'name' => '21% Normaal tarief',
                'rate_percentage' => 21.00,
                'peppol_category_code' => 'S',
                'peppol_exemption_code' => null,
                'is_default' => true,
            ],
            [
                'code' => 'BE_12',
                'name' => '12% Verlaagd tarief',
                'rate_percentage' => 12.00,
                'peppol_category_code' => 'S',
                'peppol_exemption_code' => null,
                'is_default' => false,
            ],
            [
                'code' => 'BE_6',
                'name' => '6% Verlaagd tarief (Renovatie / Voeding)',
                'rate_percentage' => 6.00,
                'peppol_category_code' => 'S',
                'peppol_exemption_code' => null,
                'is_default' => false,
            ],
            [
                'code' => 'BE_0_MC',
                'name' => '0% Verlegd (Medecontractant / Intracommunautair)',
                'rate_percentage' => 0.00,
                'peppol_category_code' => 'AE',
                'peppol_exemption_code' => 'VATEX-EU-AE',
                'is_default' => false,
            ],
        ];

        foreach ($rates as $rate) {
            TaxRate::create([
                'id' => (string) Str::ulid(),
                'tenant_id' => $tenant->id,
                'code' => $rate['code'],
                'name' => $rate['name'],
                'rate_percentage' => $rate['rate_percentage'],
                'peppol_category_code' => $rate['peppol_category_code'],
                'peppol_exemption_code' => $rate['peppol_exemption_code'],
                'is_default' => $rate['is_default'],
                'is_active' => true,
            ]);
        }
    }
}
