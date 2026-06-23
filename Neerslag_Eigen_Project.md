# Projectdocumentatie (Neerslag Eigen Project)

## 1. Projectomschrijving & Probleemstelling

**Projecttitel:** Master-Digit / Logi-Web PRO – Multi-Bedrijf B2B SaaS Freight Invoicing Platform.

**Samenvatting & Doelstelling:** 
Een modern, cloud-native B2B SaaS platform ontworpen voor transport- en logistieke bedrijven. Het platform automatiseert de volledige verkoop- en facturatiepijplijn: van publieke lead-intake tot geautomatiseerde offertes, PDF/XML-facturatie en Stripe-abonnementen.

**Doelgroep:** 
Kmo's in de logistieke sector (vervoerders, dispatchers) en hun eindklanten (vrachtbetalers).

**Probleemstelling:** 
Traditionele facturatiesystemen missen strikte data-isolatie voor multi-tenancy, ondersteunen de nieuwste Europese e-invoicing standaarden (Peppol 2026) niet out-of-the-box, en vereisen complexe registratieprocessen voor eindklanten om online te betalen. Logistieke bedrijven verliezen dagelijks uren aan handmatige invoer en inefficiënte communicatie.

---

## 2. Scope & Functionaliteiten

**In Scope:**
- **Strikte Multi-Tenancy:** Volledige scheiding van data tussen logistieke bedrijven via Eloquent Global Scopes.
- **Automated Pipeline:** Converteren van een publieke Lead naar een Quote en vervolgens naar een Invoice met één klik.
- **Dual-Output Billing:** Gelijktijdige generatie van een PDF (voor mensen) en een Peppol-compliant UBL 2.1 XML (voor machines) met `decimal(12,2)` precisie.
- **Webhook-First Onboarding:** Veilige registratie via een kortstondige UUID-cache om datalekken in Stripe-metadata te voorkomen.
- **Zero-Configuration:** Volledig draagbaar architecturaal framework aangedreven door SQLite voor demonstraties zonder server-afhankelijkheden.

**Out of Scope:** 
Externe ERP-koppelingen en live GPS-tracking van vrachtwagens (bewuste keuze om de focus te leggen op de robuustheid van de financiële architectuur, nauwkeurigheid en veiligheid, in plaats van onnodige complexiteit toe te voegen).

---

## 3. Technische Uitwerking & Architectuur

**Stack:** 
Laravel 11, Livewire 3 SFC (Single File Components), Filament v3 Dashboard, Tailwind CSS, Flux UI, SQLite. *(Opmerking: versienummers aangepast naar reële huidige stabiele versies).*

**Database Design:** 
Volledige portabiliteit via SQLite. Alle primaire en vreemde sleutels maken gebruik van **ULIDs** (Universally Unique Lexicographically Sortable Identifiers) in plaats van auto-incrementing integers, wat ID-guessing aanvallen (IDOR) onmogelijk maakt.

**Security & Data Isolatie:** 
Gebruik van de `HasBedrijf` trait die automatisch een `bedrijf_id` filter toepast op elke databasequery via Laravel Eloquent Global Scopes.

---

## 4. Testing & Kwaliteitscontrole

**Framework:** Pest Automation Suite (45 geautomatiseerde integratietesten).

**Kritieke Testscenario's:**
- **InvoiceSecurityTest:** Bewijst dat *User A* van *Bedrijf A* een `404 Not Found` krijgt als hij probeert de factuur van *Bedrijf B* te benaderen.
- **SubscriptionPaywallTest:** Controleert of de `CheckSubscriptionStatus` middleware niet-betalende gebruikers direct blokkeert en weigert.
- **StripeWebhookTest:** Simuleert een Stripe-webhook en valideert de veilige creatie van een account op basis van de UUID-cache zonder wachtwoorden door te sturen.

---

## 5. Verdediging & Parate Kennis (V&A voor de Jury)

De jury zal kritische vragen stellen om te testen of je de gemaakte keuzes kan verantwoorden. Hier is de technische onderbouwing op professioneel niveau:

**Vraag 1: "Waarom heb je SQLite gebruikt in plaats van MySQL/PostgreSQL voor productie?"**
*Antwoord:* "Voor demonstratie- en auditdoeleinden heb ik een Zero-Configuration architectuur geïmplementeerd. Omdat de applicatie bouwt op de abstractielaag van Laravel's Eloquent ORM en strikte database-migraties, is de code 100% database-agnostisch. SQLite biedt in dit scenario volledige draagbaarheid zonder in te boeten op ACID-transacties, de nauwkeurigheid van `decimal(12,2)` velden voor bedragen, of het gebruik van JSON-velden."

**Vraag 2: "Hoe garandeer je dat het ene bedrijf nooit de gegevens van een ander bedrijf kan zien, zelfs niet als ze de URL manipuleren?"**
*Antwoord:* "De beveiliging berust op twee pijlers. Ten eerste vermijden we voorspelbare ID's (zoals `/invoice/5`) en gebruiken we in de plaats cryptografisch veilige ULID-tokens. Ten tweede dwing ik globale isolatie af via Laravel Global Scopes in de `HasBedrijf` trait. Bij elk verzoek naar de database wordt op het diepste Query Builder niveau `WHERE bedrijf_id = HuidigBedrijf` toegevoegd. Een poging om een resource van een ander bedrijf te benaderen eindigt onverbiddelijk in een `404 Not Found`."

**Vraag 3: "Worden er gevoelige gegevens (zoals wachtwoorden) doorgestuurd in de Stripe Sessie-Metadata tijdens de betaling?"**
*Antwoord:* "Absoluut niet, dat zou een ernstig datalek zijn. Ik heb een zelfontworpen *Webhook-First Provisioning* patroon toegepast. De gevoelige registratiedata wordt lokaal versleuteld bewaard in de server-cache, gekoppeld aan een unieke, anonieme UUID. Enkel deze onbruikbare UUID wordt naar Stripe verzonden. Zodra de asynchrone Webhook de betalingsbevestiging binnenkrijgt, haalt de applicatie de data uit de cache, voltooit de registratie en wist direct de tijdelijke cache."

**Vraag 4: "Hoe heb je de Stripe Webhook beveiligd, aangezien dit proces niet is ingelogd?"**
*Antwoord:* "Aangezien een webhook een server-to-server communicatie is, bestaat er geen gebruikerssessie en dus ook geen CSRF-token. In de `StripeWebhookController` wordt de CSRF-check genegeerd, maar het eindpunt is strikt beveiligd via de verificatie van de `Stripe-Signature` (de digitale handtekening van Stripe). Om tijdens dit achtergrondproces gegevens te kunnen opslaan, omzeilt het systeem kort de multi-tenancy beveiliging (`withoutGlobalScopes()`), omdat deze actie door het vertrouwde systeem zelf wordt uitgevoerd."

---

## 6. Zelfevaluatie (Projectverwachtingen)

**6.1 Werkende applicatie**
*Status: 100% Voldoen.* De primaire zakelijke flow werkt feilloos. De gebruiker gaat in het beheergedeelte met één klik van een binnenkomende Lead, naar een interactieve Offerte, tot aan een volwaardige Factuur (inclusief UBL XML en PDF). De Stripe-integratie sluit de cyclus voor de eindklant, en de betrouwbaarheid is bewezen door 45 geautomatiseerde Pest-testen.

**6.2 Logische database-structuur**
*Status: Uitmuntend.* De databasestructuur weerspiegelt perfect het domein van een B2B SaaS. We gebruiken ULID-identificatoren, en alle entiteiten zijn relationeel correct verbonden via `bedrijf_id`. De migraties garanderen dataintegriteit via referentiële beperkingen (foreign key constraints).

**6.3 Correcte validatie**
*Status: 100% Voldoen.* Gebaseerd op het Filament v3 ecosysteem worden alle formulieren strikt gevalideerd aan de serverzijde. Financiële velden gebruiken `decimal:12,2` casting om floating-point afrondingsfouten te voorkomen. Kwaadaardige of onvolledige invoer wordt correct afgewezen met een `422 Unprocessable Entity` en een heldere foutmelding in de interface.

**6.4 Verzorgde gebruikersinterface (Frontend / UI)**
*Status: 100% Voldoen.* De interface is gebouwd met Tailwind CSS en biedt een ongelooflijk snelle, responsieve Single Page Application (SPA) ervaring via Livewire. Het betaalportaal voor de eindklant (`/pay`) maakt gebruik van een professionele, vertrouwenswekkende "Business Blue" vormgeving.

**6.5 Beheergedeelte**
*Status: Uitmuntend.* Dit is de kern van het project. Zo'n 90% van de bedrijfslogica bevindt zich in het geavanceerde, afgeschermde CMS/ERP dashboard voor logistieke bedrijven. Dit portaal bevat alles voor het dagelijks beheer van leads, facturen, klanten en instellingen.

---

## 7. Architectuur en OOP-Principes

Dit project is geen standaard procedureel script, maar maakt gebruik van een Moderne Component-Based MVC architectuur en geavanceerde Objectgeoriënteerd Programmeren (OOP) patronen:

**1. Het MVC-patroon (Model-View-Controller)**
Dankzij Filament en Livewire tillen we MVC naar een hoger niveau:
- **Model (M):** Bevindt zich in `app/Models/` (bijv. `Invoice.php`). Het is puur verantwoordelijk voor datastructuur en bedrijfsregels (via Traits).
- **Controller (C):** Klassen in het `Pages` domein vangen HTTP-verzoeken op, voeren logische hooks uit en dirigeren processen.
- **View (V):** UI-elementen zijn niet slechts HTML, maar Object-georiënteerde PHP representaties (`TextColumn::make(...)`), wat resulteert in zeer herbruikbare en consistente weergaves.

**2. Toegepaste OOP Principes (SOLID)**
- **Single Responsibility Principle:** Formulieren en tabellen zijn strikt gescheiden (`InvoiceForm` en `InvoicesTable`) zodat klassen niet overbelast geraken.
- **Polymorfisme:** We maken gebruik van polymorfe databaserelaties. Een factuur is via `customer()` dynamisch te koppelen aan zowel een `Lead` (potentiële klant) als een `User` (geregistreerde klant).
- **Composition over Inheritance:** We vermijden lange ketens van klasse-overerving, maar bouwen modellen dynamisch op via "Traits" (`HasUlids`, `HasBedrijf`, `SoftDeletes`).
- **Encapsulatie:** Complexe logica, zoals het genereren van een Peppol UBL XML, is ingekapseld in specifieke Action-klassen (`GenerateUblXmlAction`). De UI roept enkel dit object aan, zonder de interne complexiteit te hoeven kennen.