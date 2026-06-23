# 🧠 Parate Kennis - Theoretische Vragen (100 ptn)

Dit deel van het examen test je algemene programmeerkennis. De jury zal vragen stellen over de theorie, onafhankelijk van je specifieke project. Hieronder vind je een lijst van de meest gestelde onverwachte vragen met antwoorden in professioneel, maar begrijpelijk Nederlands (Vlaams).

---

## 🎨 FRONTEND

### 1. Wat is het verschil tussen Flexbox en CSS Grid?
**Antwoord:** 
"Flexbox is bedoeld voor eendimensionale (1D) lay-outs – ideaal voor het plaatsen van elementen in één rij of één kolom, bijvoorbeeld voor een navigatiebalk (navbar) of werkbalk. CSS Grid is voor tweedimensionale (2D) lay-outs – hiermee kun je rijen en kolommen tegelijkertijd beheren. Ik kies dit voor het bouwen van een volledige page layout, waarbij ik de positionering van de hoofdsecties moet controleren."

### 2. Waarom gebruiken we semantische HTML in plaats van alleen `<div>` tags?
**Antwoord:** 
"Semantische HTML betekent het gebruik van tags die hun betekenis beschrijven (bijv. `<header>`, `<nav>`, `<article>`, `<aside>`, `<main>`, `<footer>` in plaats van alleen maar `<div>`). Dit is cruciaal om twee redenen: SEO en Toegankelijkheid. De bot van een zoekmachine (bijv. Googlebot) begrijpt de structuur en de hiërarchie van de inhoud op de pagina beter. Bovendien kunnen schermlezers (screen readers) voor blinden en slechtzienden hierdoor correct door het document navigeren, wat de basis vormt van de WCAG-standaarden."

### 3. Hoe werkt asynchroniciteit in JavaScript en wat is het verschil tussen DOM-manipulatie en Livewire?
**Antwoord:** 
"In pure JS is asynchroniciteit gebaseerd op de Event Loop-architectuur, `async/await` instructies en `Promises`. We voeren API-verzoeken uit (bijv. via `fetch()`) zonder de hoofdthread van de browser te blokkeren. De DOM (Document Object Model) is de boomstructuur van HTML en via JS kunnen we deze wijzigen na het laden. Livewire automatiseert dit hele proces. Het onderschept gebruikersacties, stuurt op de achtergrond een snel AJAX-verzoek naar de server, en na verwerking van de PHP-code, updatet het de gewijzigde HTML rechtstreeks in de DOM-boom via een slim 'DOM diffing' algoritme."

### 4. Waar en hoe pas je frontend-validatie toe?
**Antwoord:** 
"Aan de frontend voeg ik attributen toe zoals `required`, `type="email"` of `maxlength` in HTML-formulieren. Dit geeft de gebruiker onmiddellijke feedback (Client-Side Validation). Ik ben er mij echter van bewust dat frontend-validatie alleen dient voor de UX (User Experience). De échte veiligheid wordt gegarandeerd door validatie op de backend, omdat HTML makkelijk kan worden gemanipuleerd via de developer tools in de browser."

---

## ⚙️ BACKEND (Laravel / Architectuur)

### 5. Wat is het verschil tussen Authenticatie (Authentication) en Autorisatie (Authorization)?
**Antwoord:** 
"Authenticatie beantwoordt de vraag *'Wie ben je?'* (bijv. inloggen met e-mail en wachtwoord). Autorisatie beantwoordt de vraag *'Wat mag je doen?'* (bijv. of je als ingelogde gebruiker de rechten hebt om een factuur te verwijderen; hiervoor gebruik ik zogenaamde Policies in Laravel)."

### 6. Wat is Middleware precies en waarvoor dient het?
**Antwoord:** 
"Middleware is een tussenlaag (een filter) tussen het inkomende HTTP-verzoek en de controller. Een perfect voorbeeld is authenticatie. De middleware controleert of de gebruiker een actieve sessie heeft. Zo niet, dan voert het een redirect (omleiding) uit naar de loginpagina en breekt het de request-cyclus af, waardoor de resources van de controller worden beschermd tegen onbevoegde toegang."

### 7. Wat is het verschil tussen Migrations, Seeders en Factories?
**Antwoord:** 
* **Migrations** bouwen de databankstructuur op (tabellen en kolommen). Dit is 'versiebeheer voor de databank'.
* **Factories** zijn 'recepten' voor het genereren van dummy modellen (ze bepalen dat de kolom `name` een valse naam moet krijgen via Faker).
* **Seeders** zijn de uitvoerende scripts die de Factories aanroepen om daadwerkelijk testrecords in de database te injecteren.

### 8. Wat is Databanknormalisatie en wat is het verschil tussen relaties in Eloquent?
**Antwoord:** 
"Normalisatie is het ontwerpen van een database om redundantie te elimineren en consistentie te garanderen (bijv. via 1NF, 2NF, 3NF). Bijvoorbeeld: in plaats van de naam en het adres van de klant als tekst op elke afzonderlijke factuur in te vullen, sla ik de klant op in de tabel `Customers` met een uniek ID. Op de factuur sla ik dan alleen de referentie `customer_id` (Foreign Key) op.
In het Eloquent ORM-systeem betekent de `HasMany` relatie (één-op-veel) dat één model meerdere afhankelijke modellen bezit (bijv. één `Bedrijf` heeft veel `Invoices`). Vanuit het perspectief van de onderliggende tabel declareren we `BelongsTo`, wat de aanwezigheid van een foreign key impliceert die naar het bovenliggende record verwijst."

### 9. Bespreek de belangrijkste Security bedreigingen en hoe je je daartegen beschermt.
**Antwoord:** 
Laravel beschermt me standaard tegen veel aanvallen, maar voor sommige moet ik zelf zorgen:
* **SQL Injection:** Een aanval via kwaadaardige SQL-code. *Verdediging:* Eloquent maakt gebruik van PDO en "prepared statements". SQL-query's worden gescheiden van tekstvariabelen.
* **XSS (Cross-Site Scripting):** Injectie van een JS script in bijv. een factuurbeschrijving. *Verdediging:* Blade-templates (`{{ $text }}`) gebruiken `htmlspecialchars`, wat scripts omzet in onschadelijke tekst.
* **CSRF (Cross-Site Request Forgery):** Forceren van een ongewenst verzoek via een kwaadaardige site. *Verdediging:* Laravel forceert unieke, verborgen CSRF-tokens voor POST-verzoeken.
* **IDOR (Insecure Direct Object Reference):** Poging om andermans records te bekijken door het ID in de URL aan te passen (bijv. `/invoices/5`). *Verdediging:* Ik heb dit zelf opgelost met behulp van Eloquent Global Scopes (`BedrijfScope`), wat query's automatisch op databaseniveau isoleert.

### 10. MVC-architectuur. Wat is het Model, de View en de Controller?
**Antwoord:** 
"Dit is een ontwerppatroon dat de logica van de applicatie scheidt van de presentatie:
* **Model:** Beheert de data en bedrijfsregels (bijv. het ophalen van facturen via Eloquent).
* **View:** Wat de gebruiker ziet, de grafische interface (de `.blade.php` bestanden).
* **Controller:** Het zenuwcentrum. Het ontvangt het HTTP-verzoek, vraagt het Model om data en geeft deze door aan de View."

---

## 🧹 ALGEMEEN (Code Quality & Clean Code)

### 11. Wat houden de principes DRY en Clean Code (SRP) in, en hoe heb je ze toegepast?
**Antwoord:** 
"DRY (Don't Repeat Yourself) draait om het vermijden van codeherhaling. In plaats van dezelfde controle voor het bedrijf in 10 controllers te schrijven, heb ik deze logica afgezonderd in één Trait `HasBedrijf.php`. De code is schoon, centraal beheerd en makkelijk te onderhouden.
Op het vlak van Clean Code pas ik ook het SRP (Single Responsibility Principle) toe. Al mijn Action classes (bijv. `CreateInvoiceFromQuoteAction`) hebben één specifieke, nauwe verantwoordelijkheid, en hun bestandsnamen geven duidelijk aan wat de code doet."

