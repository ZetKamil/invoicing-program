<?php

namespace App\Support;

use Illuminate\Support\Str;

class TemplateBlocks
{
    public const NICHE_TRANSPORT = 'transport';
    public const NICHE_BREEDING = 'breeding';
    public const NICHE_CRAFTSMAN = 'craftsman';
    public const NICHE_BLANK = 'blank';

    /**
     * Get pre-configured JSON page blocks tailored for a specific business niche.
     *
     * @param  string      $niche        'transport' | 'breeding' | 'craftsman' | 'blank'
     * @param  string      $companyName  Tenant company name
     * @param  string|null $tagline      Optional custom tagline
     * @return array<int, array<string, mixed>>
     */
    public static function getForNiche(string $niche, string $companyName, ?string $tagline = null): array
    {
        return match (strtolower(trim($niche))) {
            self::NICHE_TRANSPORT, 'logistics', 'transport_logistics' => self::getTransportBlocks($companyName, $tagline),
            self::NICHE_BREEDING, 'cattery', 'kennel', 'animals'     => self::getBreedingBlocks($companyName, $tagline),
            self::NICHE_CRAFTSMAN, 'services', 'bouw', 'craftsmen'    => self::getCraftsmanBlocks($companyName, $tagline),
            default                                                  => self::getBlankBlocks($companyName, $tagline),
        };
    }

    /**
     * Transport & Logistics Niche Blocks.
     */
    public static function getTransportBlocks(string $companyName, ?string $tagline = null): array
    {
        $tagline = $tagline ?: 'Betrouwbaar nationaal en internationaal transport';

        return [
            [
                'id' => (string) Str::ulid(),
                'type' => 'hero',
                'data' => [
                    'badge' => 'Erkend Transporteur Benelux',
                    'title' => $companyName,
                    'subtitle' => $tagline,
                    'primary_cta_text' => 'Vraag een vrachtofferte aan',
                    'primary_cta_url' => '#contact',
                    'secondary_cta_text' => 'Bekijk onze vloot',
                    'secondary_cta_url' => '#vloot',
                    'image' => null,
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'features',
                'data' => [
                    'title' => 'Waarom kiezen voor onze logistiek?',
                    'subtitle' => 'Stiptheid, veiligheid en transparante communicatie bij elke vracht.',
                    'items' => [
                        [
                            'icon' => 'truck',
                            'title' => 'Moderne Euro 6 Vloot',
                            'description' => 'Uitgerust met geavanceerde GPS-tracking en temperatuurregistratie.',
                        ],
                        [
                            'icon' => 'shield-check',
                            'title' => 'CMR & Transportverzekering',
                            'description' => 'Al uw goederen zijn volledig gedekt conform de hoogste Europese normen.',
                        ],
                        [
                            'icon' => 'clock',
                            'title' => '24/7 Dispatching',
                            'description' => 'Realtime opvolging en directe statusupdates over uw zendingen.',
                        ],
                    ],
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'catalog_grid',
                'data' => [
                    'title' => 'Beschikbare Transportdiensten & Routes',
                    'subtitle' => 'Kies de geschikte capaciteit voor uw vracht.',
                    'limit' => 6,
                    'show_price' => true,
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'faq',
                'data' => [
                    'title' => 'Veelgestelde Vragen',
                    'items' => [
                        [
                            'question' => 'Wat is de gemiddelde levertijd binnen de Benelux?',
                            'answer' => 'Standaard levering gebeurt binnen 24 tot 48 uur na belading. Spoedtransport op dezelfde dag is mogelijk op aanvraag.',
                        ],
                        [
                            'question' => 'Hoe ontvang ik de vrachtbrief (CMR) en factuur?',
                            'answer' => 'Na levering ontvangt u de afgetekende digitale CMR direct in uw klantportaal, samen met een Peppol-conforme factuur.',
                        ],
                    ],
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'contact_form',
                'data' => [
                    'title' => 'Vrijblijvende Vrachtofferte',
                    'subtitle' => 'Laat uw gegevens achter en ontvang binnen 2 uur een scherpe offerte.',
                    'submit_button_text' => 'Verstuur Offerteaanvraag',
                ],
            ],
        ];
    }

    /**
     * Animal Breeding / Cattery Niche Blocks.
     */
    public static function getBreedingBlocks(string $companyName, ?string $tagline = null): array
    {
        $tagline = $tagline ?: 'Verantwoorde en liefdevolle fokkerij met officiële stamboom';

        return [
            [
                'id' => (string) Str::ulid(),
                'type' => 'hero',
                'data' => [
                    'badge' => 'Erkende Fokkerij met Stamboom',
                    'title' => $companyName,
                    'subtitle' => $tagline,
                    'primary_cta_text' => 'Bekijk Beschikbare Kocięta / Pups',
                    'primary_cta_url' => '#aanbod',
                    'secondary_cta_text' => 'Kennisgeving Gezondheid & DNA',
                    'secondary_cta_url' => '#gezondheid',
                    'image' => null,
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'features',
                'data' => [
                    'title' => 'Gezondheid & Welzijn Staan Voorop',
                    'subtitle' => 'Al onze dieren groeien op in huiselijke kring met maximale zorg.',
                    'items' => [
                        [
                            'icon' => 'heart',
                            'title' => 'DNA & Gezondheidstests',
                            'description' => 'Alle ouderdieren zijn negatief getest op erfelijke aandoeningen (HCM, PKD, HD).',
                        ],
                        [
                            'icon' => 'certificate',
                            'title' => 'Officiële Stamboom & Paspoort',
                            'description' => 'Geregistreerd bij erkende stamboekvereniging, gechipt, ontwormd en gevaccineerd.',
                        ],
                        [
                            'icon' => 'home',
                            'title' => 'Socialisatie in Huis',
                            'description' => 'Gewend aan kinderen, huiselijke geluiden en andere huisdieren voor een stabiel karakter.',
                        ],
                    ],
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'catalog_grid',
                'data' => [
                    'title' => 'Beschikbare & Gereserveerde Dieren',
                    'subtitle' => 'Bekijk onze huidige nestjes en plaats een reserveringsaanvraag.',
                    'limit' => 8,
                    'show_price' => true,
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'testimonials',
                'data' => [
                    'title' => 'Ervaringen van Trotse Eigenaars',
                    'items' => [
                        [
                            'author' => 'Familie Claes (Gent)',
                            'role' => 'Eigenaar sinds 2024',
                            'quote' => 'Onze kater is ontzettend sociaal, speels en kerngezond. Het contact en de updates tijdens de eerste weken waren fantastisch!',
                            'rating' => 5,
                        ],
                        [
                            'author' => 'Sophie V. (Antwerpen)',
                            'role' => 'Eigenaar sinds 2025',
                            'quote' => 'Heel professionele begeleiding, inclusief een compleet starterspakket en duidelijke contractvoorwaarden.',
                            'rating' => 5,
                        ],
                    ],
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'contact_form',
                'data' => [
                    'title' => 'Interesse in een toekomstig nestje?',
                    'subtitle' => 'Schrijf u in op de wachtlijst of stel ons gerust een vraag.',
                    'submit_button_text' => 'Plaats Aanvraag',
                ],
            ],
        ];
    }

    /**
     * Craftsman / Local Services (Schrijnwerker, Elektricien, Installateur).
     */
    public static function getCraftsmanBlocks(string $companyName, ?string $tagline = null): array
    {
        $tagline = $tagline ?: 'Vakmanschap, maatwerk en betrouwbare service';

        return [
            [
                'id' => (string) Str::ulid(),
                'type' => 'hero',
                'data' => [
                    'badge' => 'Gecertificeerd Vakmanschap',
                    'title' => $companyName,
                    'subtitle' => $tagline,
                    'primary_cta_text' => 'Vraag een Gratis Plaatsbezoek Aan',
                    'primary_cta_url' => '#contact',
                    'secondary_cta_text' => 'Onze Realisaties',
                    'secondary_cta_url' => '#realisaties',
                    'image' => null,
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'features',
                'data' => [
                    'title' => 'Waarom kiezen voor ons vakmanschap?',
                    'subtitle' => 'Van ontwerp tot vlekkeloze oplevering met 10 jaar garantie.',
                    'items' => [
                        [
                            'icon' => 'wrench',
                            'title' => 'Maatwerk & Duurzaamheid',
                            'description' => 'Hoogwaardige materialen met oog voor detail en perfecte afwerking.',
                        ],
                        [
                            'icon' => 'clipboard-check',
                            'title' => 'Duidelijke Vaste Prijzen',
                            'description' => 'Geen verrassingen achteraf. Heldere en gedetailleerde offertes vooraf.',
                        ],
                        [
                            'icon' => 'shield-check',
                            'title' => '10 Jaar Garantie',
                            'description' => 'Volledig verzekerd en gewaarborgd volgens de geldende Belgische bouwregels.',
                        ],
                    ],
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'catalog_grid',
                'data' => [
                    'title' => 'Diensten & Maatwerkpakketten',
                    'subtitle' => 'Ontdek onze specialisaties en standaardformules.',
                    'limit' => 6,
                    'show_price' => true,
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'contact_form',
                'data' => [
                    'title' => 'Vertel ons over uw project',
                    'subtitle' => 'Wij nemen binnen 24 uur contact op voor een vrijblijvend adviesgesprek.',
                    'submit_button_text' => 'Offerte Aanvragen',
                ],
            ],
        ];
    }

    /**
     * Blank Starter (Zelfbouwen) Blocks.
     */
    public static function getBlankBlocks(string $companyName, ?string $tagline = null): array
    {
        $tagline = $tagline ?: 'Welkom bij ' . $companyName;

        return [
            [
                'id' => (string) Str::ulid(),
                'type' => 'hero',
                'data' => [
                    'badge' => 'Nieuwe Website',
                    'title' => $companyName,
                    'subtitle' => $tagline,
                    'primary_cta_text' => 'Neem Contact Op',
                    'primary_cta_url' => '#contact',
                    'secondary_cta_text' => 'Ontdek Ons Aanbod',
                    'secondary_cta_url' => '#aanbod',
                    'image' => null,
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'catalog_grid',
                'data' => [
                    'title' => 'Ons Aanbod',
                    'subtitle' => 'Bekijk onze nieuwste producten en diensten.',
                    'limit' => 6,
                    'show_price' => true,
                ],
            ],
            [
                'id' => (string) Str::ulid(),
                'type' => 'contact_form',
                'data' => [
                    'title' => 'Contacteer Ons',
                    'subtitle' => 'Heeft u een vraag of wenst u informatie? Stuur ons gerust een bericht.',
                    'submit_button_text' => 'Verzend Bericht',
                ],
            ],
        ];
    }

    /**
     * Validate an array of blocks against the required schema.
     *
     * @param  array<int, mixed> $blocks
     * @return bool
     */
    public static function validateBlocks(array $blocks): bool
    {
        foreach ($blocks as $block) {
            if (!is_array($block)) {
                return false;
            }
            if (!isset($block['id']) || !is_string($block['id'])) {
                return false;
            }
            if (!isset($block['type']) || !is_string($block['type'])) {
                return false;
            }
            if (!isset($block['data']) || !is_array($block['data'])) {
                return false;
            }
        }

        return true;
    }
}
