# Neerslag Eigen Project

## 1. Projectomschrijving & Probleemstelling

**Projecttitel:** Master-Digit / Logi-Web PRO – Multi-Tenant B2B SaaS Freight Invoicing Platform.

**Samenvatting & Doelstelling:** 
Een modern, cloud-native B2B SaaS platform ontworpen voor transport- en logistieke bedrijven. Het platform automatiseert de volledige verkoop- en facturatiepijplijn: van publieke lead-intake tot geautomatiseerde offertes, PDF/XML-facturatie en Stripe-abonnementen.

**Doelgroep:** 
Kmo's in de logistieke sector (vervoerders, dispatchers) en hun eindklanten (vrachtbetalers).

**Probleemstelling:** 
Traditionele facturatiesystemen missen strikte data-isolatie voor multi-tenancy, ondersteunen de nieuwste Europese e-invoicing standaarden (Peppol 2026) niet out-of-the-box, en vereisen complexe registratieprocessen voor eindklanten om online te betalen.

---

## 2. Scope & Functionaliteiten

**In Scope:**
- **Strikte Multi-Tenancy:** Volledige scheiding van data tussen logistieke bedrijven via Eloquent Global Scopes.
- **Automated Pipeline:** Converteren van een publieke Lead naar een Quote en vervolgens naar een Invoice met één klik.
- **Dual-Output Billing:** Gelijktijdige generatie van een PDF (voor mensen) en een Peppol-compliant UBL 2.1 XML (voor machines) met `decimal(12,2)` precisie.
- **Webhook-First Onboarding:** Veilige registratie via een kortstondige UUID-cache om datalekken in Stripe-metadata te voorkomen.
- **Zero-Configuration:** Volledig draagbare architecturaal framework aangedreven door SQLite voor demonstraties zonder server-afhankelijkheden (geen WAMP/XAMPP nodig).

**Out of Scope:** 
Externe ERP-koppelingen en live GPS-tracking van vrachtwagens (bewuste keuze om de focus te leggen op de robuustheid van de financiële architectuur en security).

---

## 3. Technische Uitwerking & Architectuur

**Stack:** 
Laravel 13, Livewire 4 SFC (Single File Components), Filament v3 Dashboard, Tailwind CSS, Flux UI, SQLite.

**Database Design:** 
Migratie naar SQLite voor 100% portabiliteit. Alle primaire en vreemde sleutels maken gebruik van **ULIDs** (Universally Unique Lexicographically Sortable Identifiers) in plaats van auto-incrementing integers, wat ID-guessing aanvallen onmogelijk maakt.

**Security & Data Isolatie:** 
Gebruik van de `HasTenant` trait die automatisch een `tenant_id` filter toepast op elk databasequery via Eloquent Global Scopes.

---

## 4. Testing & Kwaliteitscontrole

**Framework:** Pest Automation Suite (45 integratietesten).

**Kritieke Testscenario's:**
- **InvoiceSecurityTest:** Bewijst dat *User A* van *Tenant A* een `404 Not Found` krijgt als hij probeert de factuur van *Tenant B* te benaderen.
- **SubscriptionPaywallTest:** Controleert of de `CheckSubscriptionStatus` middleware niet-betalende gebruikers direct blokkeert en naar `/billing/inactive` stuurt.
- **StripeWebhookTest:** Simuleert een Stripe-webhook en valideert de veilige creatie van een Tenant op basis van de UUID-cache.


Część 2: Verdediging & Parate Kennis (Twoja tarcza na egzamin)
Jury składa się z programistów (vakjury). Nie będą oceniać projektu jak laicy – będą szukać dziury w całym. Oto 4 najtrudniejsze pytania, które mogą Ci zadać, wraz z odpowiedziami na poziomie Senior Engineera.

Pytanie 1: "Dlaczego użyłeś SQLite zamiast MySQL/PostgreSQL na produkcję?"
Twoja odpowiedź: "Dla celów demonstracyjnych i audytowych wdrożyłem architekturę Zero-Configuration. Ponieważ cała aplikacja opiera się na warstwie abstrakcji Laravel Eloquent ORM oraz restrykcyjnych migracjach, baza jest w 100% uniezależniona od drivera. SQLite pozwala na pełną przenośność projektu bez utraty wydajności przy transakcjach ACID, zachowując precyzję typów decimal(12,2) i obsługę pól JSON."

Pytanie 2: "Jak zapewniłeś, że jedna firma nie zobaczy faktur drugiej firmy? Co jeśli zmienią ID w adresie URL?"
Twoja odpowiedź: "Bezpieczeństwo opiera się na dwóch liniach obrony. Po pierwsze, nie używamy podatnych na iterację numerów ID (jak 1, 2, 3), lecz losowych, bezpiecznych kryptograficznie tokenów ULID. Po drugie, wdrożyłem globalny mechanizm isolation za pomocą Laravel Global Scopes w traicie HasTenant. Każde zapytanie do bazy danych automatycznie dokleja warunek WHERE tenant_id = current_tenant. Jeśli użytkownik spróbuje wpisać ULID innej firmy, system zareaguje statusem 404 Not Found, traktując ten zasób jako nieistniejący."

Pytanie 3: "Widzę, że przy rejestracji wysyłasz dane do Stripe. Czy hasło użytkownika przesyłane jest w metadanych sesji Stripe?"
Twoja odpowiedź: "Absolutnie nie. Przesyłanie poufnych danych tekstowych lub nawet hashowanych przez Stripe Metadata to poważna podatność bezpieczeństwa (data leakage). Zastosowałem autorski wzorzec Webhook-First Provisioning. Podczas rejestracji pełny payload jest bezpiecznie szyfrowany lokalnie w Cache aplikacji pod unikalnym kluczem UUID. Do Stripe przesyłamy wyłącznie ten bezwartościowy dla osób trzecich token UUID. Dopiero po autoryzacji płatności, asynchroniczny Webhook odbiera UUID ze Stripe, pobiera dane z lokalnego Cache, tworzy konto i natychmiast czyści pamięć podręczną."

Pytanie 4: "Jak rozwiązałeś problem z autoryzacją w Webhooku Stripe? Przecież Stripe nie jest zalogowany w Twojej aplikacji."
Twoja odpowiedź: "To była jedna z głównych trudności architektonicznych. Ponieważ webhook to komunikacja server-to-server, standardowa autoryzacja sesji użytkownika nie istnieje. W StripeWebhookController wyłączyłem weryfikację CSRF, ale zabezpieczyłem endpoint poprzez weryfikację podpisu cyfrowego Stripe (Stripe-Signature). Aby zapisać dane w bazie, system tymczasowo omija globalne zabezpieczenia multi-tenancy za pomocą metody Invoice::withoutGlobalScopes(), ponieważ operacja ta jest wykonywana przez zaufany proces systemowy, a nie zalogowanego klienta."