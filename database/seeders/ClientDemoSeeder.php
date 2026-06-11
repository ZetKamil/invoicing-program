<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\LeadStatus;
use App\Enums\ProductType;
use App\Enums\QuoteStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Bedrijf;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientDemoSeeder extends Seeder
{
    /**
     * Seeds 4 demo logistics clients (bedrijven), each with:
     * - 1 admin user (password: "password")
     * - 5-6 freight leads (B2B shippers)
     * - 4 quotes linked to those leads
     * - 3 invoices with real product line items
     */
    public function run(): void
    {
        // Define 4 real-world Belgian logistics bedrijven
        $clients = [
            [
                'name'     => 'Transport Van Damme',
                'slug'     => 'transport-van-damme',
                'vat'      => 'BE0476123456',
                'peppol'   => '0088:476123456',
                'email'    => 'admin@vandamme-transport.be',
                'packages' => ['2', '3'],
            ],
            [
                'name'     => 'Logistics Pieterszoon BV',
                'slug'     => 'logistics-pieterszoon',
                'vat'      => 'BE0612987654',
                'peppol'   => '0088:612987654',
                'email'    => 'dispatcher@pieterszoon.eu',
                'packages' => ['1', '3'],
            ],
            [
                'name'     => 'Cargoline Express NV',
                'slug'     => 'cargoline-express',
                'vat'      => 'BE0553741852',
                'peppol'   => '0088:553741852',
                'email'    => 'admin@cargoline-express.be',
                'packages' => ['2', '3', '4'],
            ],
            [
                'name'     => 'Janssen Freight Solutions',
                'slug'     => 'janssen-freight',
                'vat'      => 'BE0481369258',
                'peppol'   => '0088:481369258',
                'email'    => 'info@janssen-freight.be',
                'packages' => ['1', '3'],
            ],
        ];

        // Freight shippers / B2B lead prospects â€“ cycled across all 4 bedrijven
        $sampleLeads = [
            ['company_name' => 'Beko Huishoudtoestellen NV',   'contact_person' => 'Ahmed Boulanger',    'email' => 'ahmedb@beko.eu'],
            ['company_name' => 'Decathlon Logistiek BE',        'contact_person' => 'Sophie Mertens',     'email' => 'smertens@decathlon.be'],
            ['company_name' => 'Carrefour Bevoorradingsketen',  'contact_person' => 'Nicolas Dubois',     'email' => 'ndubois@carrefour.fr'],
            ['company_name' => 'Volvo Onderdelen BelgiĂ«',       'contact_person' => 'Lars Andersson',     'email' => 'l.andersson@volvo-parts.be'],
            ['company_name' => 'ArcelorMittal Staal',           'contact_person' => 'Jean-Paul Renard',   'email' => 'jp.renard@arcelormittal.com'],
            ['company_name' => 'Amazon Fulfilment BE',          'contact_person' => 'Emma Schroeder',     'email' => 'e.schroeder@amazon-eu.com'],
            ['company_name' => 'IKEA Distributiecentrum',       'contact_person' => 'Bjorn Lindqvist',    'email' => 'b.lindqvist@ikea-dist.eu'],
            ['company_name' => 'Solvay Chemische Groep',        'contact_person' => 'Marie Fontaine',     'email' => 'm.fontaine@solvay.be'],
            ['company_name' => 'Lidl Benelux Logistiek',        'contact_person' => 'Dirk Vandenberghe',  'email' => 'd.vandenberghe@lidl.be'],
            ['company_name' => 'DHL Supply Chain BelgiĂ«',       'contact_person' => 'Peter Koenig',       'email' => 'pkoenig@dhl.com'],
            ['company_name' => 'Michelin Bandenhandel EU',      'contact_person' => 'FranĂ§ois Leclerc',   'email' => 'f.leclerc@michelin-dist.fr'],
            ['company_name' => 'Electrabel Energietransport',   'contact_person' => 'Annelies De Smedt',  'email' => 'a.desmedt@electrabel.be'],
            ['company_name' => 'UCB Farma Logistiek',           'contact_person' => 'Thomas Nijs',        'email' => 't.nijs@ucb-pharma.be'],
            ['company_name' => 'AB InBev Brouwerij Supply',     'contact_person' => 'Carlos Esteves',     'email' => 'c.esteves@abinbev.com'],
            ['company_name' => 'Proximus Technisch Transport',  'contact_person' => 'Katrien Willems',    'email' => 'k.willems@proximus.be'],
            ['company_name' => 'Aldi Winkels BelgiĂ«',           'contact_person' => 'Hans Meier',         'email' => 'h.meier@aldi.be'],
            ['company_name' => 'Siemens Industrieel BE',        'contact_person' => 'Gertrude Braun',     'email' => 'g.braun@siemens.be'],
            ['company_name' => 'NestlĂ© Benelux Bevoorrading',  'contact_person' => 'Isabelle Morel',     'email' => 'i.morel@nestle.eu'],
            ['company_name' => 'Continental Automotive BE',     'contact_person' => 'Markus Fischer',     'email' => 'm.fischer@continental.be'],
            ['company_name' => 'Bosch Logistiek Europa',        'contact_person' => 'Eva Schulz',         'email' => 'e.schulz@bosch-log.eu'],
            ['company_name' => 'Henkel Bevoorradingsketen',     'contact_person' => 'Leon Bauer',         'email' => 'l.bauer@henkel.eu'],
            ['company_name' => 'Saint-Gobain Distributie',      'contact_person' => 'Pierre Moreau',      'email' => 'p.moreau@saint-gobain.fr'],
            ['company_name' => 'Bridgestone Banden EU',         'contact_person' => 'Yuki Tanaka',        'email' => 'y.tanaka@bridgestone.eu'],
            ['company_name' => 'Auchan Retail Logistiek',       'contact_person' => 'Gilles Petit',       'email' => 'g.petit@auchan.fr'],
        ];

        // Cycle through statuses to show realistic CRM pipeline stages
        $statusCycle = [
            LeadStatus::NEW,
            LeadStatus::AUDITED,
            LeadStatus::CONTACTED,
            LeadStatus::CONVERTED,
            LeadStatus::NEW,
            LeadStatus::CONTACTED,
        ];

        $quoteCycle = [
            QuoteStatus::DRAFT,
            QuoteStatus::SENT,
            QuoteStatus::ACCEPTED,
            QuoteStatus::DECLINED,
        ];

        $invoiceStatusCycle = [
            InvoiceStatus::DRAFT,
            InvoiceStatus::SENT,
            InvoiceStatus::PAID,
        ];

        $leadCursor   = 0;
        $invoiceSeqNr = 100;

        foreach ($clients as $ci => $clientData) {
            // --- 1. Create the logistics company (Bedrijf) ---
            $bedrijf = Bedrijf::create([
                'name'                       => $clientData['name'],
                'slug'                       => $clientData['slug'],
                'vat_number'                 => $clientData['vat'],
                'peppol_id'                  => $clientData['peppol'],
                'stripe_subscription_status' => 'active',
                'active_packages'            => $clientData['packages'],
                'settings'                   => [
                    'currency'     => 'EUR',
                    'contact_info' => $clientData['email'],
                    'theme'        => 'dark',
                ],
            ]);

            // --- 2. Create the primary dispatcher/admin user ---
            User::create([
                'bedrijf_id' => $bedrijf->id,
                'name'      => 'Beheerder ' . $clientData['name'],
                'email'     => $clientData['email'],
                'password'  => Hash::make('password'),
                'role'      => UserRole::ADMIN,
            ]);

            // --- 3. Seed 3 transport service products for this bedrijf ---
            $products = [
                Product::create([
                    'bedrijf_id'    => $bedrijf->id,
                    'name'         => 'Wegtransport â€“ Standaard',
                    'description'  => 'Standaard wegtransport binnen BelgiĂ«',
                    'price'        => 850.00,
                    'type'         => ProductType::SERVICE,
                    'is_recurring' => false,
                ]),
                Product::create([
                    'bedrijf_id'    => $bedrijf->id,
                    'name'         => 'Wegtransport â€“ Express',
                    'description'  => 'Expressleveringen op dezelfde dag',
                    'price'        => 1450.00,
                    'type'         => ProductType::SERVICE,
                    'is_recurring' => false,
                ]),
                Product::create([
                    'bedrijf_id'    => $bedrijf->id,
                    'name'         => 'Internationale Logistiek',
                    'description'  => 'Grensoverschrijdend EU-vrachtbeheer',
                    'price'        => 2450.00,
                    'type'         => ProductType::SERVICE,
                    'is_recurring' => false,
                ]),
            ];

            // --- 4. Seed 5 or 6 freight leads per bedrijf ---
            $numLeads    = ($ci % 2 === 0) ? 6 : 5;
            $bedrijfLeads = [];

            for ($l = 0; $l < $numLeads; $l++) {
                $rawLead = $sampleLeads[$leadCursor % count($sampleLeads)];
                $leadCursor++;

                $lead = Lead::create([
                    'bedrijf_id'      => $bedrijf->id,
                    'company_name'   => $rawLead['company_name'],
                    'contact_person' => $rawLead['contact_person'],
                    'email'          => $rawLead['email'],
                    'phone'          => '+32 ' . rand(470, 499) . ' ' . rand(100000, 999999),
                    'status'         => $statusCycle[$l % count($statusCycle)],
                    'metadata'       => [
                        'bron'       => collect(['websiteformulier', 'telefoongesprek', 'doorverwijzing', 'koude_acquisitie'])->random(),
                        'vrachttype' => collect(['stortgoed', 'gekoeld', 'gevaarlijk', 'automotive', 'retail'])->random(),
                        'trailer_type' => collect(['Huifwagen', 'Koelwagen', 'Container', 'Dieplader', 'Silo', 'Kipper', 'Tankwagen'])->random(),
                        'cargo_weight_kg' => rand(1000, 24000),
                        'pallet_count' => rand(1, 33),
                        'loading_date' => now()->addDays(rand(1, 14))->format('Y-m-d'),
                        'delivery_date' => now()->addDays(rand(15, 30))->format('Y-m-d'),
                        'loading_address' => collect(['Wetstraat 16, 1000 Brussel, BE', 'Meir 1, 2000 Antwerpen, BE', 'Veldstraat 2, 9000 Gent, BE'])->random(),
                        'delivery_address' => collect(['Damrak 1, 1012 LG Amsterdam, NL', 'Coolsingel 1, 3012 AA Rotterdam, NL', 'Champs-Ă‰lysĂ©es 1, 75008 Parijs, FR'])->random(),
                    ],
                ]);

                $bedrijfLeads[] = $lead;
            }

            // --- 5. Create 4 quotes linked to the first 4 leads ---
            $quoteableLeads = array_slice($bedrijfLeads, 0, 4);
            $bedrijfQuotes   = [];

            foreach ($quoteableLeads as $qi => $lead) {
                $product = $products[$qi % count($products)];
                $qty     = rand(1, 3);
                $amount  = $product->price * $qty;

                $quote = Quote::create([
                    'bedrijf_id'    => $bedrijf->id,
                    'lead_id'      => $lead->id,
                    'quote_number' => 'OFT-2026-' . str_pad(($ci * 10) + $qi + 1, 4, '0', STR_PAD_LEFT),
                    'total_amount' => $amount,
                    'valid_until'  => now()->addDays(rand(7, 30)),
                    'status'       => $quoteCycle[$qi % count($quoteCycle)],
                    'trailer_type' => $lead->metadata['trailer_type'] ?? null,
                    'cargo_weight_kg' => $lead->metadata['cargo_weight_kg'] ?? null,
                    'pallet_count' => $lead->metadata['pallet_count'] ?? null,
                    'loading_date' => $lead->metadata['loading_date'] ?? null,
                    'delivery_date' => $lead->metadata['delivery_date'] ?? null,
                    'loading_address' => $lead->metadata['loading_address'] ?? null,
                    'delivery_address' => $lead->metadata['delivery_address'] ?? null,
                    'description'  => 'Goederenvervoer op traject ' . ($lead->metadata['loading_address'] ?? '') . ' -> ' . ($lead->metadata['delivery_address'] ?? ''),
                ]);

                $bedrijfQuotes[] = [
                    'quote'   => $quote,
                    'lead'    => $lead,
                    'product' => $product,
                    'qty'     => $qty,
                ];
            }

            // --- 6. Convert first 3 quotes into invoices with line items ---
            $invoiceable = array_slice($bedrijfQuotes, 0, 3);

            foreach ($invoiceable as $ii => $set) {
                $invoiceSeqNr++;
                $qty       = $set['qty'];
                $unitPrice = $set['product']->price;
                $subtotal  = $qty * $unitPrice;
                $taxTotal  = round($subtotal * 0.21, 2);
                $total     = $subtotal + $taxTotal;
                $status    = $invoiceStatusCycle[$ii % count($invoiceStatusCycle)];

                $invoice = Invoice::create([
                    'bedrijf_id'      => $bedrijf->id,
                    'customer_type'  => Lead::class,
                    'customer_id'    => $set['lead']->id,
                    'invoice_number' => 'FACT-2026-' . $invoiceSeqNr,
                    'subtotal'       => $subtotal,
                    'tax_total'      => $taxTotal,
                    'total_amount'   => $total,
                    'due_date'       => now()->addDays(30),
                    'status'         => $status,
                    'paid_at'        => $status === InvoiceStatus::PAID
                                            ? now()->subDays(rand(1, 20))
                                            : null,
                    'trailer_type'   => $set['lead']->metadata['trailer_type'] ?? collect(['Huifwagen', 'Koelwagen', 'Container'])->random(),
                    'cargo_weight_kg' => $set['lead']->metadata['cargo_weight_kg'] ?? rand(10000, 24000),
                    'pallet_count'   => $set['lead']->metadata['pallet_count'] ?? rand(10, 33),
                    'loading_date'   => $set['lead']->metadata['loading_date'] ?? now()->subDays(rand(10, 20))->format('Y-m-d'),
                    'delivery_date'  => $set['lead']->metadata['delivery_date'] ?? now()->subDays(rand(1, 9))->format('Y-m-d'),
                    'loading_address' => $set['lead']->metadata['loading_address'] ?? 'Industrielaan 1, 1000 Brussel, BE',
                    'delivery_address' => $set['lead']->metadata['delivery_address'] ?? 'Logistiekweg 2, 2000 Antwerpen, BE',
                    'cmr_number'     => 'CMR-' . rand(100000, 999999),
                    'truck_license_plate'  => '1-' . chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90)) . '-' . rand(100, 999),
                    'trailer_license_plate' => 'Q-' . chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90)) . '-' . rand(100, 999),
                    'notes'          => 'Chauffeur meldde een lichte vertraging bij het laden.',
                ]);

                // One line item per invoice matching the original product
                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $set['product']->id,
                    'description' => $set['product']->name . ' â€“ ' . $set['lead']->company_name,
                    'quantity'    => $qty,
                    'unit_price'  => $unitPrice,
                    'tax_rate'    => 21,
                    'total'       => $subtotal,
                ]);
            }
        }
    }
}
