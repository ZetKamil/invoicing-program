# KMO-Flow: Master Implementation Roadmap & Dogfooding Architecture
> **Status dokumentu:** Aktywny rejestr wdrożeniowy (Living Execution Plan)  
> **Wersja:** 2.1 (Zgodna ze schematem bazy danych 3NF & Filament v3 Multi-Tenancy)  
> **Data utworzenia:** 2026-10-23  
> **Architektura:** Dual-Execution (Action-First: Człowiek + Agent AI) | Full Dogfooding & Symmetry  

---

## 1. Fundamentalna Zasada Biznesowa: Dogfooding i Pełna Symetria (Fractal Architecture)

### Odpowiedź na pytanie: *„Czy moi klienci mają dokładnie ten sam panel co ja?”*
**TAK! W 100% TAK.** W architekturze KMO-Flow nie tworzymy dwóch osobnych systemów (jednego dla agencji, drugiego dla klientów). Cała platforma opiera się na **symetrii fraktalnej (Dogfooding)**:

```mermaid
graph TD
    subgraph POZIOM 0: PLATFORMA GLOBALNA (Super-Admin)
        Platform["👑 Kamil (Platformbeheer)<br/>Widok globalny: Wszyscy Tenanci, łączny MRR, Tenant Switcher"]
    end

    subgraph POZIOM 1: TWOJA AGENCJA (Tenant #1: Ziemkiewicz Digital)
        T1["🏢 TENANT #1 (Twoja Agencja)<br/><b>Klienci (customers):</b> Flamandzkie MŚP (Jan de Smet, Hodowla Kotów)<br/><b>Produkty (catalog_items):</b> Pakiety WaaS (€39, €100, €200) + Wdrożenia (€1 500)<br/><b>Faktury (commercial_documents):</b> Faktury za abonament i setup strony<br/><b>Płatności Stripe:</b> Link do opłacenia pakietu przez klienta"]
    end

    subgraph POZIOM 2: TWÓJ KLIENT (Tenant #2: np. Hodowla Kotów Brytyjskich)
        T2["🐱 TENANT #2 (Twój Klient)<br/><b>Klienci (customers):</b> Nabywcy kotów / miłośnicy rasy<br/><b>Produkty (catalog_items):</b> Kocięta, Rezerwacje, Wyprawki, Konsultacje<br/><b>Faktury (commercial_documents):</b> Zaliczki na kociaka, faktury sprzedaży<br/><b>Płatności Stripe:</b> Link do opłacenia zaliczki przez nabywcę"]
    end

    subgraph POZIOM 3: TWÓJ KLIENT (Tenant #3: np. De Smet Transport & Logistiek)
        T3["🚛 TENANT #3 (Twój Klient)<br/><b>Klienci (customers):</b> Centra logistyczne, fabryki, producenci<br/><b>Produkty (catalog_items):</b> Kursy FTL, transport paletowy, magazynowanie<br/><b>Faktury (commercial_documents):</b> Faktury transportowe B2B + Peppol BIS 3.0<br/><b>Płatności Stripe:</b> Link do szybkiej płatności Bancontact"]
    end

    Platform -->|Zarządza / Przełącza się w panel| T1
    Platform -->|Zarządza / Przełącza się w panel| T2
    Platform -->|Zarządza / Przełącza się w panel| T3

    T1 -.->|Ten sam moduł Invoices & Stripe| T2
    T2 -.->|Ten sam moduł Invoices & Stripe| T3
```

### Dlaczego to jest potężna przewaga rynkowa?
1. **Dogfooding („Jesz własną karmę”):** Ty jako założyciel agencji wystawiasz swoje własne faktury za pakiety WaaS i wdrożenia przez ten sam panel, którego używają Twoi klienci. Jeśli coś działa świetnie u Ciebie – wiesz w 100%, że działa perfekcyjnie u Twojego klienta.
2. **Zero duplikacji kodu:** Ten sam formularz faktury, ten sam przycisk `[ ✉️ Verstuur Factuur & Betaallink ]`, ten sam moduł Stripe Checkout i ten sam publiczny portal płatności `/pay/{invoice}` obsługuje Ciebie i wszystkich Twoich klientów.
3. **Klienci klientów też się rejestrują:**
   - Wchodzą na stronę WWW tenanta (generowaną z autorskich klocków `pages.blocks`).
   - Wypełniają formularz zapytania lub rezerwacji produktu/usługi (tworzy się rekord w tabeli `leads` lub `customers` z przypisanym `tenant_id`).
   - Otrzymują automatyczną lub wyklikaną ofertę/fakturę z linkiem do płatności online.
   - Płacą przez Stripe (karta, Bancontact, iDEAL).
4. **Co wyróżnia Ciebie od klientów?**
   - Tylko jedna cecha: flaga `is_super_admin = true` w tabeli `users`. Daje Ci ona dostęp do bocznego modułu **Platformbeheer** (monitorowanie stanu całej floty stron, globalne logi AI, MRR) oraz przełącznika firm (**Tenant Switcher** w nagłówku Filamenta), dzięki któremu w 1 sekundę możesz „wejść w buty” dowolnego klienta i skonfigurować mu ofertę lub klocki strony.

---

## 2. Matryca Odpowiedzialności i Ról (Dual-Execution)

Wszystkie prace i funkcje systemu projektowane są pod podwójne wykonanie (**Action-First**):
* **👤 Człowiek (Filament v3 UI):** Ty lub Twój przyszły pracownik low-code klikacie w formularzach, tabelach i kreatorze klocków.
* **🤖 Agent AI (Structured Tool Calling):** Autonomiczny bot lub harmonogram nocny wykonuje te same operacje wywołując dedykowane klasy `App\Actions\...`.
* **🌐 Klient Końcowy (Public Portal):** Nabywca wchodzi przez stronę www lub link `/pay/{id}`, składa zapytanie i dokonuje płatności.

---

## 3. Plan Realizacji Prac (Work Breakdown Structure & Checklists)

### 📌 FAZA 1: Harmonizacja Modeli Eloquent ze Schematem 3NF v2.1
> **Cel:** Całkowite wyeliminowanie długu technicznego, usunięcie błędów `no such table: bedrijven` oraz dopasowanie encji Eloquent do znormalizowanej bazy 18 tabel.

- [ ] **TASK-1.1: Utworzenie bazowego modelu `Tenant` i zachowanie wstecznej kompatybilności**
  - Stworzyć model `app/Models/Tenant.php` mapujący tabelę `tenants` (klucze ULID, pola: `name`, `slug`, `plan`, `custom_domain`, `vat_number`, `kbo_number`, `peppol_endpoint_id`, `iban`, `bic`, `settings`, `stripe_customer_id`, `stripe_subscription_id`).
  - Zaktualizować `app/Models/Bedrijf.php` jako alias dziedziczący po `Tenant` (`class Bedrijf extends Tenant`), zapobiegając regresji w istniejących kontrolerach.
- [ ] **TASK-1.2: Refaktoryzacja Traitu Wielodostępności (`HasTenant`)**
  - Utworzyć `app/Traits/HasTenant.php` z globalnym zakresem `TenantScope` filtrującym po `tenant_id`.
  - Zaktualizować `app/Traits/HasBedrijf.php` jako nakładkę delegującą do `HasTenant`.
- [ ] **TASK-1.3: Wielodostępność Użytkowników (Relacja N:M `tenant_user`)**
  - Zaktualizować `app/Models/User.php`: usunąć sztywne `tenant_id`/`bedrijf_id`.
  - Zaimplementować relację `tenants(): BelongsToMany` przez tabelę pivot `tenant_user` z obsługą kolumny `role` (`owner`, `admin`, `dispatcher`).
  - Zapewnić obsługę interfejsu Filament v3: `HasTenants`, `canAccessTenant(Model $tenant)`.
- [ ] **TASK-1.4: Aktualizacja Modeli CRM (`Customer` i `Lead`)**
  - Zaktualizować `Customer.php`: kolumny `peppol_endpoint_id`, `address_line1`, `address_line2`, `postal_code`, `city`, `country_code` (BE).
  - Zaktualizować `Lead.php`: relacja z `converted_customer_id`, obsługa źródeł (`contact_form`, `demo_sandbox`, `catalog_inquiry`).
- [ ] **TASK-1.5: Aktualizacja Modeli Katalogu i Produktów**
  - Zaktualizować `CatalogItem.php`: obsługa `price_minor` (BIGINT centy), rzutowanie `attributes` (JSON), soft-deletes.
  - Zaktualizować `Category.php`: obsługa dyskryminatora `type` (`catalog` lub `post`), unikalność sluga w obrębie tenanta.
- [ ] **TASK-1.6: Wdrożenie Modeli Finansowych (Single Table Inheritance dla Dokumentów Handlowych)**
  - Utworzyć `app/Models/CommercialDocument.php` (tabela `commercial_documents`, obsługa typów: `invoice`, `quote`, `credit_note`, kwoty minor, VCS, Peppol, snapshoty nabywcy).
  - Utworzyć `app/Models/Invoice.php` i `app/Models/Quote.php` dziedziczące po `CommercialDocument`.
  - Utworzyć `app/Models/CommercialDocumentLine.php` (tabela `commercial_document_lines`, relacja z `tax_rates` i `catalog_items`).
  - Utworzyć `app/Models/TaxRate.php` (tabela `tax_rates`, kody Peppol `S`, `Z`, `E`, `AE`).
  - Utworzyć `app/Models/Payment.php` (tabela `payments`, wielowalutowość, obsługa Stripe PaymentIntent).
- [ ] **TASK-1.7: Wdrożenie Modeli CMS, Stron i Telemetrii AI**
  - Utworzyć `app/Models/Page.php` (tabela `pages`, kolumny `blocks` JSON, `is_homepage`, `is_published`, `seo_meta` JSON).
  - Zaktualizować `app/Models/Post.php` (tabela `posts`, kolumny `sections` JSON, statusy, soft-deletes).
  - Utworzyć `app/Models/DailyMetric.php`, `app/Models/AiGenerationLog.php`, `app/Models/AiSchedule.php`.

---

### 📌 FAZA 2: Warstwa Akcji Biznesowych (Action-First / Dual-Execution)
> **Cel:** Przygotowanie czystych klas serwisowych (Actions), które mogą być wywoływane w 100% identycznie przez kliknięcie człowieka w Filament oraz przez prompt / tool calling Agenta AI.

- [ ] **TASK-2.1: Klasa Szablonów Wizualnych Klocków (`TemplateBlocks`)**
  - Utworzyć `app/Support/TemplateBlocks.php` ze strukturami bloków JSON dla kluczowych branż:
    - *Transport & Logistyka:* Hero, Certyfikaty CMR/ISO, Flota pojazdów, Formularz wyceny frachtu, FAQ transportowe.
    - *Hodowla Zwierząt:* Hero ze statusem miotu, Kocięta/Szczenięta (siatka katalogowa), Rodzice i badania genetyczne, Opinie, Formularz rezerwacji.
    - *Rzemiosło / Usługi (Schrijnwerker/Elektricien):* Hero z gwarancją jakości, Galeria realizacji, Cennik usług, Formularz kontaktowy.
    - *Zelfbouwen (Czysty):* Minimalistyczny szkielet startowy do samodzielnego wyklikania.
- [ ] **TASK-2.2: Atomowa Akcja Onboardingu Firmy (`ProvisionTenantAction`)**
  - Utworzyć `app/Actions/Tenants/ProvisionTenantAction.php`.
  - W transakcji DB wykonać:
    1. Utworzenie rekordu `tenants`.
    2. Utworzenie rekordu `users` i powiązanie w `tenant_user` z rolą `owner`.
    3. Wgranie belgijskich stawek Peppol do `tax_rates` (`BE_21`, `BE_12`, `BE_6`, `BE_0_MC`).
    4. Wygenerowanie strony głównej `pages` z klockami z `TemplateBlocks::getForNiche()`.
    5. Zapisanie wpisu w `audit_logs` (kto utworzył: Człowiek czy Agent).
    6. Wysłanie e-maila powitalnego do klienta z bezpiecznym tokenem aktywacji konta.
- [ ] **TASK-2.3: Akcja Wystawiania i Wysyłki Faktury ze Stripe (`SendCommercialDocumentAction`)**
  - Utworzyć `app/Actions/Invoices/SendCommercialDocumentAction.php`.
  - Generowanie PDF faktury, przypisanie unikalnego tokenu publicznego płatności.
  - Wygenerowanie sesji Stripe Checkout lub linku płatniczego Bancontact.
  - Wysłanie e-maila do klienta (z załącznikiem PDF i dużym przyciskiem: *„Betaal direct online via Bancontact / Kaart”*).
  - Zmiana statusu faktury na `sent`.
- [ ] **TASK-2.4: Akcja Zarządzania Klockami Strony (`BuildPageBlocksAction`)**
  - Utworzyć `app/Actions/Pages/BuildPageBlocksAction.php`.
  - Walidacja poprawności schematu klocków JSON (struktury Hero, About, CatalogGrid, ContactForm).
  - Zapis w kolumnie `pages.blocks`.
  - Czyszczenie pamięci podręcznej witryny publicznej.
- [ ] **TASK-2.5: Rejestr Schematów Narzędzi Agenta (`AgentToolsSchema`)**
  - Utworzyć dokumentację i klasy definicji narzędzi JSON dla modeli AI:
    - `tool_provision_tenant`
    - `tool_build_page_blocks`
    - `tool_create_catalog_item`
    - `tool_send_invoice_payment_link`

---

### 📌 FAZA 3: Filament v3 Dashboard – Symetryczny Interfejs (Dla Ciebie i Klientów)
> **Cel:** Uruchomienie nowoczesnego, szybkiego panelu administracyjnego w języku niderlandzkim, w którym Ty zarządzasz agencją, a Twoi klienci swoimi biznesami.

- [ ] **TASK-3.1: Zasób Oferty i Produktów (`CatalogItemResource` - Aanbod)**
  - Skonfigurować pola: Nazwa, Kategoria, Status (Dostępny / Zarezerwowany / Archiwum), Cena w euro (z konwersją do `price_minor`), Atrybuty JSON (np. wiek kota, ładowność naczepy, rodzaj pakietu), Prefill tekstu WhatsApp.
  - Tabela z filtrami kategorii i statusu.
  - Wykorzystanie: U Ciebie do definiowania pakietów WaaS i wdrożeń; u klientów do ich produktów i usług.
- [ ] **TASK-3.2: Zasób Faktur i Płatności (`CommercialDocumentResource` / Facturen)**
  - Tabela faktur z kolumnami: Numer faktury, Klient/Afnemer, Kwota brutto, Status (Draft, Sent, Paid, Overdue), Data płatności.
  - Dodanie przycisków akcji w tabeli:
    - `[ ✉️ Verstuur Factuur & Betaallink ]`: natychmiastowa wysyłka maila ze Stripe.
    - `[ 💳 Betaalherinnering ]`: wysyłka ponaglenia z bezpośrednim linkiem Stripe Checkout.
    - `[ 📄 Download PDF ]` i `[ 🇧🇪 Peppol UBL ]`.
- [ ] **TASK-3.3: Zasób Stron i Modułowy Page Builder (`PageResource` - Pagina's)**
  - Formularz z komponentem `Builder` Filamenta mapującym kolumnę `pages.blocks` (JSON).
  - Dostępne klocki:
    - 🧱 **HeroBlock:** Tytuł, podtytuł, zdjęcie w tle, przyciski CTA (np. Zadzwoń / Zapytaj o wycenę).
    - 🧱 **AboutBlock:** Tekst o firmie, lata doświadczenia, ikony wyróżników.
    - 🧱 **CatalogGridBlock:** Dynamiczne pobieranie produktów z `catalog_items` (kocięta lub usługi).
    - 🧱 **ContactFormBlock:** Formularz z polami dopasowanymi do branży, generujący rekordy w `leads`.
    - 🧱 **PricingTableBlock:** Tabela cenowa (używana u Ciebie na stronie głównej do prezentacji pakietów WaaS €39/€100/€200).
    - 🧱 **FaqBlock:** Rozwijane pytania i odpowiedzi (akordeon).
- [ ] **TASK-3.4: Zasób Klientów i Leadów (`CustomerResource` & `LeadResource`)**
  - Kartoteka klientów z weryfikacją numeru BTW/KBO i statusem Peppol.
  - Tabela leadów ze statusem konwersji do stałego klienta.
- [ ] **TASK-3.5: Moduł Super-Admina (`Platformbeheer` - Tylko dla Ciebie)**
  - Zasób widoczny wyłącznie gdy `auth()->user()->is_super_admin`:
    - Lista wszystkich firm (Tenantów) ze statusem abonamentu Stripe (`active`, `past_due`, `canceled`).
    - Przycisk `[ 🔑 Log in als deze onderneming ]` (Tenant Impersonation).
    - Globalny pulpit metryk: łączny MRR platformy, liczba wygenerowanych wpisów AI, zużycie tokenów.

---

### 📌 FAZA 4: Klient Końcowy i Portal Płatności (Frontend & Public Web)
> **Cel:** Umożliwienie klientom Twoich klientów przeglądania oferty, wysyłania zapytań i płacenia za faktury/zaliczki online.

- [ ] **TASK-4.1: Publiczny Portal Płatności `/pay/{id}`**
  - Dedykowany, responsywny widok Livewire/Blade zoptymalizowany pod urządzenia mobilne.
  - Wyświetlanie szczegółów faktury/oferty, danych wystawcy i nabywcy.
  - Integracja ze Stripe Checkout (obsługa kart płatniczych, Bancontact, iDEAL).
  - Natychmiastowe potwierdzenie płatności i zmiana statusu w bazie na `paid` (oraz wpis w tabeli `payments`).
- [ ] **TASK-4.2: Silnik Renderowania Klocków Strony (`Modular Page Engine`)**
  - Kontroler publiczny pobierający stronę główną tenanta na podstawie subdomeny lub domeny własnej (`custom_domain`).
  - Szablony Blade renderujące poszczególne klocki z `pages.blocks`.
  - Ekstremalna optymalizacja wydajności: zero zewnętrznych bibliotek, natywny CSS, czas ładowania poniżej 0.4s.
- [ ] **TASK-4.3: Publiczny Formularz Zgłoszeniowy (Lead & Order Intake)**
  - Bezpieczny komponent formularza na stronie klienta.
  - Tworzenie rekordu w tabeli `leads` powiązanego z danym tenantem.
  - Opcjonalne wysłanie powiadomienia e-mail/SMS do właściciela firmy: *„Nowe zapytanie od klienta!”*.

---

### 📌 FAZA 5: Seeder Referencyjny (Dogfooding) i Testy Automatyczne (Pest 100% Green)
> **Cel:** Kompletna weryfikacja automatyczna oraz przygotowanie gotowego środowiska demonstracyjnego.

- [ ] **TASK-5.1: Seeder Demonstracyjny (`DatabaseSeeder`)**
  - Wyczyszczenie i zasiew bazy:
    1. **Tenant #1 (Ziemkiewicz Digital):** Super-Admin Kamil, pakiety WaaS (€39, €100, €200, €350) w `catalog_items`, strona główna agencji w `pages`.
    2. **Tenant #2 (Jan de Smet Transport & Logistiek):** Właściciel, usługi transportowe, faktura transportowa oczekująca na płatność.
    3. **Tenant #3 (Kattenkwekerij Van Het Waasland):** Właściciel, kocięta w `catalog_items`, oferta w `commercial_documents`.
- [ ] **TASK-5.2: Zestaw Testów Jednostkowych i Integracyjnych (Pest PHP)**
  - Test izolacji multi-tenant (Tenant A nie widzi danych Tenanta B).
  - Test akcji `ProvisionTenantAction` (automatyczne wygenerowanie klocków i stawek podatkowych).
  - Test akcji `SendCommercialDocumentAction` i portalu płatności `/pay/{id}`.
  - Test uprawnień Super-Admina vs zwykłego Tenanta.
  - Wszystkie testy kończące się wynikiem: **100% PASSING**.

---

## 4. Tabela Śledzenia Postępów (Live Progress Tracker)

| Identyfikator | Zadanie / Moduł | Faza | Rola | Status | Weryfikacja |
|---|---|---|---|---|---|
| `TASK-1.1` | Modele `Tenant` i alias `Bedrijf` | Faza 1 | 👤 Architekt | ⏳ Oczekuje | Kod PHP / Migracja |
| `TASK-1.2` | Trait `HasTenant` & `TenantScope` | Faza 1 | 👤 Architekt | ⏳ Oczekuje | Izolacja SQL |
| `TASK-1.3` | Relacja `tenant_user` N:M w `User` | Faza 1 | 👤 Architekt | ⏳ Oczekuje | Autoryzacja Filament |
| `TASK-1.4` | Modele CRM `Customer` i `Lead` | Faza 1 | 👤 Architekt | ⏳ Oczekuje | Testy modeli |
| `TASK-1.5` | Modele Katalogu `CatalogItem` (Minor) | Faza 1 | 👤 Architekt | ⏳ Oczekuje | Testy minor cents |
| `TASK-1.6` | Modele Finansowe `CommercialDocument` (STI) | Faza 1 | 👤 Architekt | ⏳ Oczekuje | Faktury & Peppol |
| `TASK-1.7` | Modele CMS `Page` (Blocks) & Telemetria | Faza 1 | 👤 Architekt | ⏳ Oczekuje | JSON schema |
| `TASK-2.1` | Szablony klocków `TemplateBlocks` | Faza 2 | 👤/🤖 Dual | ⏳ Oczekuje | Podgląd szablonów |
| `TASK-2.2` | Akcja `ProvisionTenantAction` | Faza 2 | 👤/🤖 Dual | ⏳ Oczekuje | Test transakcji DB |
| `TASK-2.3` | Akcja wysyłki faktury & Stripe Link | Faza 2 | 👤/🤖 Dual | ⏳ Oczekuje | E-mail + Stripe URL |
| `TASK-2.4` | Akcja `BuildPageBlocksAction` | Faza 2 | 👤/🤖 Dual | ⏳ Oczekuje | Zapis w `pages.blocks` |
| `TASK-3.1` | Filament `CatalogItemResource` (Aanbod) | Faza 3 | 👤 UI | ⏳ Oczekuje | Formularz Filament |
| `TASK-3.2` | Filament `InvoiceResource` + Akcja Stripe | Faza 3 | 👤 UI | ⏳ Oczekuje | Tabela + Akcja wysyłki |
| `TASK-3.3` | Filament `PageResource` (Visual Builder) | Faza 3 | 👤 UI | ⏳ Oczekuje | Drag & Drop Klocków |
| `TASK-3.4` | Filament `Customer` & `Lead` Resource | Faza 3 | 👤 UI | ⏳ Oczekuje | Formularze CRM |
| `TASK-3.5` | Filament `Platformbeheer` (Super-Admin) | Faza 3 | 👑 Admin | ⏳ Oczekuje | Switcher + MRR |
| `TASK-4.1` | Publiczny Portal Płatności `/pay/{id}` | Faza 4 | 🌐 Public | ⏳ Oczekuje | Płatność Stripe test |
| `TASK-4.2` | Renderer Klocków Strony Blade | Faza 4 | 🌐 Public | ⏳ Oczekuje | Render HTML/CSS |
| `TASK-4.3` | Formularz Zapytania Ofertowego | Faza 4 | 🌐 Public | ⏳ Oczekuje | Zapis w tabeli `leads` |
| `TASK-5.1` | Seeder Bazy (Dogfooding Tenant #1) | Faza 5 | 👤 Dev | ⏳ Oczekuje | `artisan db:seed` |
| `TASK-5.2` | Pakiet Testów Pest (100% Green) | Faza 5 | 👤 Dev | ⏳ Oczekuje | `pest` 100% passing |

---

## 5. Jak Śledzić i Aktualizować Ten Dokument?
* Każde zakończone zadanie w kodzie zmienia status w powyższej tabeli z `⏳ Oczekuje` na `✅ Zakończone`, a odpowiedni checkbox z `[ ]` na `[x]`.
* Wszelkie decyzje techniczne i zmiany architektoniczne są dodatkowo rejestrowane w dzienniku [`DEV_JOURNAL.md`](file:///c:/wamp64/www/facturatieprogramma/DEV_JOURNAL.md).
