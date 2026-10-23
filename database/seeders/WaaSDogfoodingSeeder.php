<?php

namespace Database\Seeders;

use App\Models\CatalogItem;
use App\Models\Category;
use App\Models\Page;
use App\Models\TaxRate;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TemplateBlocks;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WaaSDogfoodingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Resolve or create Tenant #1: Agency Ziemkiewicz Digital
        $agency = Tenant::firstOrCreate(
            ['slug' => 'logi-web-pro'],
            [
                'id' => (string) Str::ulid(),
                'name' => 'Ziemkiewicz Digital (KMO-Flow Agency)',
                'plan' => 'scale',
                'status' => 'active',
                'vat_number' => 'BE0798123456',
                'iban' => 'BE68539007547034',
                'bic' => 'GEBABEBB',
                'settings' => [
                    'theme' => ['primary_color' => '#2563eb', 'niche' => 'services'],
                    'payment_gateways' => ['stripe' => ['secret_key' => null, 'publishable_key' => null]],
                ],
            ]
        );

        $this->seedTaxRatesForTenant($agency);

        // Seed Agency WaaS Packages in catalog_items (Dogfooding: Agency products are WaaS packages!)
        $waasCategory = Category::firstOrCreate(
            ['tenant_id' => $agency->id, 'slug' => 'waas-pakketten'],
            [
                'id' => (string) Str::ulid(),
                'type' => 'catalog',
                'name' => 'WaaS & Turnkey Pakketten',
                'sort_order' => 1,
            ]
        );

        $packages = [
            [
                'name' => 'KMO-Flow Zelfbouwen (Doe-het-zelf)',
                'slug' => 'kmo-flow-zelfbouwen',
                'price_minor' => 3900, // €39.00 / maand
                'description' => 'Eigen moderne website met visuele klocken editor, contactformulier, SSL en hosting. Geen WordPress, 100% veilig.',
                'status' => 'available',
                'attributes' => [
                    'Doelgroep' => 'Zelfstandigen & Starters',
                    'Support' => 'E-mail support + Helpcenter',
                    'AI-Blog' => 'Optioneel add-on',
                ],
            ],
            [
                'name' => 'KMO-Flow Turnkey Business (Start)',
                'slug' => 'kmo-flow-turnkey-start',
                'price_minor' => 10000, // €100.00 / maand
                'description' => 'Volledige inrichting in 1 avond door Ziemkiewicz Digital. Inclusief 4 blogartikelen per maand via Gemini AI.',
                'status' => 'available',
                'attributes' => [
                    'Wachttijd' => 'Binnen 24 uur live',
                    'AI-artikelen' => '4 per maand inbegrepen',
                    'Onderhoud' => '100% ontzorging door agency',
                ],
            ],
            [
                'name' => 'KMO-Flow Turnkey Business (Pro Transport & Catalogus)',
                'slug' => 'kmo-flow-turnkey-pro',
                'price_minor' => 20000, // €200.00 / maand
                'description' => 'Branchespecifiek klockenpakket (Transport vloot of Fokkerij nestjes), 12 AI-artikelen/mc en WhatsApp direct intake.',
                'status' => 'available',
                'attributes' => [
                    'Doelgroep' => 'KMO Transporteurs & Lokale Dienstverleners',
                    'AI-artikelen' => '12 per maand inbegrepen',
                    'WhatsApp' => 'Directe Lead Intake knop',
                ],
            ],
            [
                'name' => 'KMO-Flow Scale Enterprise & Peppol ERP',
                'slug' => 'kmo-flow-scale-enterprise',
                'price_minor' => 35000, // €350.00 / maand
                'description' => 'Volledig facturatiesysteem conform Belgische Peppol BIS 3.0 wetgeving, onbeperkte dispatching en Stripe betalingen.',
                'status' => 'available',
                'attributes' => [
                    'Facturatie' => 'Peppol BIS 3.0 + UBL XML',
                    'Betalingen' => 'Stripe Bancontact integratie',
                    'AI-artikelen' => '30 per maand',
                ],
            ],
        ];

        foreach ($packages as $pkg) {
            CatalogItem::firstOrCreate(
                ['tenant_id' => $agency->id, 'slug' => $pkg['slug']],
                [
                    'id' => (string) Str::ulid(),
                    'category_id' => $waasCategory->id,
                    'name' => $pkg['name'],
                    'price_minor' => $pkg['price_minor'],
                    'description' => $pkg['description'],
                    'status' => $pkg['status'],
                    'attributes' => $pkg['attributes'],
                    'whatsapp_prefill_text' => "Hallo Kamil, ik heb interesse in het pakket '{$pkg['name']}'. Kunnen we de intake inplannen?",
                ]
            );
        }

        // 2. Seed Tenant Showcase: "Cattery Van Het Waasland" (Animal Breeding Niche)
        $cattery = Tenant::firstOrCreate(
            ['slug' => 'cattery-van-het-waasland'],
            [
                'id' => (string) Str::ulid(),
                'name' => 'Cattery Van Het Waasland',
                'plan' => 'pro',
                'status' => 'active',
                'vat_number' => 'BE0812345678',
                'iban' => 'BE45732012345678',
                'bic' => 'KREDBEBB',
                'settings' => [
                    'theme' => ['primary_color' => '#ec4899', 'niche' => 'breeding'],
                ],
            ]
        );

        $this->seedTaxRatesForTenant($cattery);

        // Owner user for Cattery
        $catteryOwner = User::firstOrCreate(
            ['email' => 'katrien@cattery-waasland.be'],
            [
                'id' => (string) Str::ulid(),
                'name' => 'Katrien Van den Bossche',
                'password' => Hash::make('password'),
                'is_super_admin' => false,
            ]
        );

        $cattery->users()->syncWithoutDetaching([
            $catteryOwner->id => [
                'id' => (string) Str::ulid(),
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Cattery Homepage with Breeding Blocks
        Page::firstOrCreate(
            ['tenant_id' => $cattery->id, 'slug' => 'home'],
            [
                'id' => (string) Str::ulid(),
                'title' => 'Home',
                'is_homepage' => true,
                'is_published' => true,
                'blocks' => TemplateBlocks::getForNiche(TemplateBlocks::NICHE_BREEDING, $cattery->name),
                'seo_meta' => [
                    'meta_title' => 'Cattery Van Het Waasland | Erkende Brits Korthaar Fokkerij',
                    'meta_description' => 'Liefdevol gefokte Brits Korthaar kittens met officiële stamboom en DNA-gezondheidscertificaat.',
                ],
            ]
        );

        // Cattery Products (Kittens in catalog_items)
        $catCategory = Category::firstOrCreate(
            ['tenant_id' => $cattery->id, 'slug' => 'brits-korthaar'],
            [
                'id' => (string) Str::ulid(),
                'type' => 'catalog',
                'name' => 'Brits Korthaar Kittens',
                'sort_order' => 1,
            ]
        );

        $kittens = [
            [
                'name' => 'Apollo (Brits Korthaar Blauw)',
                'slug' => 'apollo-brits-korthaar-blauw',
                'price_minor' => 125000, // € 1 250.00
                'status' => 'available',
                'description' => 'Prachtige blauwe kater met ronde kophouding en koperkleurige ogen. Erg speels en aanhankelijk.',
                'attributes' => [
                    'Geslacht' => 'Kater',
                    'Kleur' => 'Solid Blue',
                    'Geboortedatum' => '2026-08-15',
                    'Stamboom' => 'Felikat / FIFe',
                    'Gezondheid' => 'Ouders HCM/PKD negatief getest',
                ],
            ],
            [
                'name' => 'Bella (Brits Korthaar Lilac)',
                'slug' => 'bella-brits-korthaar-lilac',
                'price_minor' => 135000, // € 1 350.00
                'status' => 'reserved',
                'description' => 'Lief en rustig lilac poesje met een fluwelen vacht. Gereserveerd voor familie Peeters.',
                'attributes' => [
                    'Geslacht' => 'Poes',
                    'Kleur' => 'Lilac',
                    'Geboortedatum' => '2026-08-15',
                    'Stamboom' => 'Felikat / FIFe',
                    'Gezondheid' => 'Volledig ingeënt & gechipt',
                ],
            ],
            [
                'name' => 'Casper (Brits Korthaar Bicolor)',
                'slug' => 'casper-brits-korthaar-bicolor',
                'price_minor' => 120000, // € 1 200.00
                'status' => 'available',
                'description' => 'Nieuwsgierige blauw-witte kater met een heel zachtaardig karakter. Klaar voor adoptie per november.',
                'attributes' => [
                    'Geslacht' => 'Kater',
                    'Kleur' => 'Blauw-Wit Bicolor',
                    'Geboortedatum' => '2026-08-20',
                    'Stamboom' => 'Felikat / FIFe',
                    'Gezondheid' => 'Europees paspoort & garantiecertificaat',
                ],
            ],
        ];

        foreach ($kittens as $k) {
            CatalogItem::firstOrCreate(
                ['tenant_id' => $cattery->id, 'slug' => $k['slug']],
                [
                    'id' => (string) Str::ulid(),
                    'category_id' => $catCategory->id,
                    'name' => $k['name'],
                    'price_minor' => $k['price_minor'],
                    'description' => $k['description'],
                    'status' => $k['status'],
                    'attributes' => $k['attributes'],
                    'whatsapp_prefill_text' => "Hallo Katrien, ik heb interesse in kitten '{$k['name']}'. Is een kennismaking mogelijk?",
                ]
            );
        }
    }

    protected function seedTaxRatesForTenant(Tenant $tenant): void
    {
        $rates = [
            ['code' => 'BE_21', 'name' => '21% Normaal tarief', 'rate_percentage' => 21.00, 'peppol_category_code' => 'S', 'is_default' => true],
            ['code' => 'BE_12', 'name' => '12% Verlaagd tarief', 'rate_percentage' => 12.00, 'peppol_category_code' => 'S', 'is_default' => false],
            ['code' => 'BE_6', 'name' => '6% Verlaagd tarief', 'rate_percentage' => 6.00, 'peppol_category_code' => 'S', 'is_default' => false],
            ['code' => 'BE_0_MC', 'name' => '0% Medecontractant / Verlegd', 'rate_percentage' => 0.00, 'peppol_category_code' => 'AE', 'is_default' => false],
        ];

        foreach ($rates as $r) {
            TaxRate::firstOrCreate(
                ['tenant_id' => $tenant->id, 'code' => $r['code']],
                [
                    'id' => (string) Str::ulid(),
                    'name' => $r['name'],
                    'rate_percentage' => $r['rate_percentage'],
                    'peppol_category_code' => $r['peppol_category_code'],
                    'is_default' => $r['is_default'],
                    'is_active' => true,
                ]
            );
        }
    }
}
