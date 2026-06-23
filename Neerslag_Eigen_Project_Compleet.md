# 📝 Neerslag Eigen Project (Volledige Projectdocumentatie)

*Dit document bevat de volledige schriftelijke verantwoording van het eindproject, gestructureerd volgens de officiële vereisten van de jury (100 punten).*

---

## 1. Projectomschrijving

* **Titel van het project:** Master-Digit / Logi-Web PRO
* **Korte samenvatting:** Een modern, cloud-native B2B SaaS-platform ontworpen voor transport- en logistieke bedrijven. Het systeem automatiseert het volledige proces van klantenaanvraag tot facturatie en online betaling via Stripe.
* **Doelstelling:** Het elimineren van handmatige data-invoer door een geautomatiseerde pijplijn (Quote-to-Invoice) te bieden, inclusief de generatie van mensleesbare (PDF) en machineleesbare (Peppol UBL XML) facturen.
* **Doelgroep:** KMO's in de logistieke sector (zoals dispatchers en kleine transporteurs) enerzijds, en hun eindklanten (vrachtbetalers) anderzijds.
* **Probleemstelling of behoefte:** Bestaande oplossingen zijn vaak duur, missen strikte data-isolatie voor meerdere transportbedrijven op één platform, en bieden geen naadloze integratie met moderne betaalproviders en e-invoicing standaarden. Transportbedrijven verliezen daardoor uren aan administratie.

---

## 2. Scope

* **Wat werd gebouwd (In Scope):** 
  Een veilig Multi-Tenant CRM, een offerte- en facturatiemodule met absolute wiskundige precisie (`BCMath`), een asynchroon e-mailsysteem (Queues) en een publiek betaalportaal gekoppeld aan Stripe Webhooks.
* **Wat bewust niet werd gebouwd (Out of Scope):** 
  Live GPS-tracking van vrachtwagens en externe ERP-koppelingen. Dit was een bewuste keuze: de robuustheid van de databeveiliging en de financiële correctheid kregen absolute prioriteit boven het toevoegen van onstabiele extra features.
* **Belangrijkste functionaliteiten:**
  - Veilige Multi-Tenancy (elke vervoerder ziet enkel eigen data).
  - One-click conversie van Lead naar Offerte naar Factuur.
  - Generatie van Peppol UBL 2.1 XML en PDF.
* **Uitbreidingsmogelijkheden:** 
  In de toekomst kan een fleet-management module (voor het toewijzen van ritten aan specifieke chauffeurs) en meertaligheid (Multi-Language support) worden toegevoegd.

---

## 3. Analyse

* **Functionele vereisten:**
  - Gebruikers moeten klanten, producten en facturen kunnen beheren via een afgeschermd dashboard.
  - Eindklanten moeten facturen online kunnen betalen via een creditcard (Stripe).
  - Het systeem moet automatisch e-mails verzenden bij nieuwe facturen.
* **Niet-functionele vereisten:**
  - *Security:* 100% data-isolatie tussen bedrijven.
  - *Precisie:* Financiële afrondingsfouten vermijden (`decimal(12,2)` datatypes).
  - *Performantie:* Single Page Application (SPA) ervaring zonder pagina-herladingen.
* **Gebruikersrollen:**
  1. *SuperAdmin:* Kan technische ondersteuning bieden via 'Impersonation'.
  2. *TenantAdmin (Vervoerder):* Beheert zijn eigen bedrijf, facturen en klanten.
  3. *Customer (Eindklant):* Heeft enkel toegang tot het publieke betaalportaal via een unieke URL.
* **User Stories (Voorbeelden):**
  - "Als dispatcher wil ik een offerte met één klik omzetten naar een factuur, zodat ik geen lijnen handmatig moet overtypen."
  - "Als eindklant wil ik veilig online betalen, zodat de transporteur onmiddellijk ziet dat de factuur voldaan is."

---

## 4. Technische uitwerking

* **Gebruikte technologieën:** 
  Laravel 13, Livewire 4 (SFC), Filament v3, Tailwind CSS, SQLite en Stripe PHP.
* **Projectstructuur (Component-Based MVC):**
  In plaats van logge traditionele controllers, maakt dit project gebruik van het Filament-ecosysteem waar 'Pages' (zoals `CreateInvoice`) en 'Action-classes' (`CreateInvoiceFromQuoteAction`) de processen dirigeren. De UI is opgebouwd via object-georiënteerde PHP-componenten.
* **Belangrijke bestanden of componenten:**
  - `HasBedrijf.php` (Trait voor Laravel Global Scopes die multi-tenancy afdwingt).
  - `StripeWebhookController.php` (Verwerkt asynchrone server-to-server betalingsbevestigingen).
* **Databankstructuur:**
  Relaties lopen centraal via de tabel `Bedrijfs`. Tabellen zoals `Users`, `Leads`, `Quotes` en `Invoices` bevatten allemaal een `bedrijf_id` foreign key. Om ID-guessing aanvallen te vermijden, zijn alle primaire sleutels cryptografisch veilige **ULIDs**.
  
  *Hieronder het visuele Entity-Relationship (ER) diagram van de kerntabellen:*

  ```mermaid
  erDiagram
      Bedrijfs ||--o{ Users : "heeft (Tenant)"
      Bedrijfs ||--o{ Customers : "beheert"
      Bedrijfs ||--o{ Leads : "ontvangt"
      Bedrijfs ||--o{ Quotes : "maakt"
      Bedrijfs ||--o{ Invoices : "factureert"
      Bedrijfs ||--o{ Products : "biedt aan"

      Customers ||--o{ Quotes : "vraagt aan"
      Customers ||--o{ Invoices : "ontvangt"
      
      Quotes ||--|{ QuoteItems : "bevat (Lijnen)"
      Invoices ||--|{ InvoiceItems : "bevat (Lijnen)"

      Bedrijfs {
          string id PK "ULID"
          string name
          string vat_number
      }
      Users {
          string id PK "ULID"
          string bedrijf_id FK
          string email
          string password
      }
      Customers {
          string id PK "ULID"
          string bedrijf_id FK
          string company_name
          string vat_number
      }
      Quotes {
          string id PK "ULID"
          string bedrijf_id FK
          string customer_id FK
          decimal total_amount "BCMath (12,2)"
          string status
      }
      Invoices {
          string id PK "ULID"
          string bedrijf_id FK
          string customer_id FK
          decimal total_amount "BCMath (12,2)"
          string status
      }
  ```
* **API’s:** 
  Stripe API (Checkout Sessions & Webhooks) is geïntegreerd voor betalingsverwerking.

---

## 5. Ontwerp en UX

* **Wireframes, screenshots of designkeuzes:** 
  De keuze viel op een strakke, no-nonsense interface omdat voor een B2B ERP-systeem overzichtelijkheid cruciaal is. 
* **Motivatie van kleuren, typografie en layout:** 
  Het thema gebruikt "Business Blue" accentkleuren, wat psychologisch geassocieerd wordt met betrouwbaarheid en financiële veiligheid. De typografie gebruikt moderne, schreefloze lettertypes (sans-serif) uit het Tailwind ecosysteem voor hoge leesbaarheid op grote tabellen.
* **Responsive gedrag:** 
  Dankzij Filament en Tailwind is het beheergedeelte volledig mobiel responsief (automatisch inklappende sidebars, tabellen met scroll of stack-weergave op smartphones).
* **Toegankelijkheid:** 
  Hoog contrast, duidelijke call-to-action (CTA) knoppen en heldere (rode) foutmeldingen bij formuliervalidatie (`422 Unprocessable Entity`).

---

## 6. Testing en kwaliteitscontrole

* **Wat werd getest:** 
  Het systeem bevat een Pest Automation Suite met 45 geautomatiseerde integratietesten. Gekritiseerde processen, zoals het genereren van facturen en factuurisolatie (security), zijn strikt getest.
* **Gekende fouten of beperkingen:** 
  De SQLite database is perfect voor deze demonstratie en architectuur, maar kent beperkingen bij honderden gelijktijdige 'heavy writes' per seconde in een grootschalige productieomgeving.
* **Hoe bugs werden opgelost (Edge-cases):** 
  Bugs gerelateerd aan floating-point drift (het beruchte $0.01 verschil) werden opgelost door het integreren van de PHP `BCMath` extensie voor alle prijsberekeningen, gecombineerd met test-driven development (TDD).

---

## 7. Reflectie

* **Wat goed ging:** 
  De integratie van Filament v3 zorgde voor een razendsnelle ontwikkeling van het beheergedeelte zonder in te boeten op veiligheid. De SPA-ervaring voelt extreem vloeiend aan.
* **Wat moeilijk was:** 
  Het beveiligen van de Stripe Webhook en registratieflow. Omdat een webhook buiten de actieve gebruikerssessie opereert, was er een hoog risico op datalekken via Stripe Metadata.
* **Wat ik heb geleerd:** 
  Dit heb ik opgelost door een geavanceerd *Webhook-First Provisioning* systeem te schrijven: data wordt lokaal in de cache versleuteld onder een UUID, en enkel die onbruikbare UUID gaat naar Stripe. Ik heb geleerd hoe je asynchrone processen veilig koppelt aan een Multi-Tenant architectuur via `withoutGlobalScopes()`.
* **Wat bij een volgende versie beter zou kunnen:** 
  Migratie naar een PostgreSQL database voor schaalbaarheid in productie, en het toevoegen van meertaligheid en multi-valuta (currencies) ondersteuning.

---

## 8. Bronnen en hulpmiddelen

* **Gebruikte documentatie:** 
  Officiële documentatie van Laravel (laravel.com/docs), FilamentPHP (filamentphp.com/docs), en Stripe API Reference.
* **Gebruikte AI-tools:** 
  LLM's en AI-coding assistants werden gebruikt als "Sparring Partner" voor complexe architecturale beslissingen (zoals de BCMath logica en het opzetten van de Pest Test Suite).
* **Gebruikte libraries, packages of frameworks:** 
  - `laravel/framework` (Core)
  - `filament/filament` (Admin Panel)
  - `stripe/stripe-php` (Payment gateway)
  - `livewire/flux` (UI Components)
* **Correcte vermelding:** 
  Er zijn geen externe knip-en-plak templates gebruikt; de volledige front- en back-end zijn opgebouwd binnen het Filament/Tailwind ecosysteem.


9. Canban board
https://github.com/users/ZetKamil/projects/5/views/2