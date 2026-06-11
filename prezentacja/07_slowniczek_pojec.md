# 7. Słowniczek Pojęć (Ściągawka na Prezentację)

To zestawienie to Twoja polisa ubezpieczeniowa na prezentacji. Znajdują się tu "po ludzku" wytłumaczone wszystkie najtrudniejsze inżynieryjne określenia (buzzwords), których użyłeś w poprzednich plikach. Opanuj je, a żadne pytanie od technicznego jury Cię nie zaskoczy.

---

### 1. Global Scope (Zakres Globalny)
**Co to jest:** To z góry zdefiniowany filtr w Laravelu, który jest automatycznie i "na sztywno" doczepiany do każdego zapytania w bazie danych.
**Jak to wytłumaczyć:** Zamiast pamiętać o dopisywaniu w kodzie "pobierz tylko dla tej firmy", definiujemy to raz jako Global Scope. Odtąd system sam w tle dodaje ten warunek do zapytań (np. przy każdym pobraniu faktur automatycznie dopisuje `WHERE tenant_id = X`). Całkowicie chroni to system przed ludzkim błędem.

### 2. Query Builder (Kreator Zapytań)
**Co to jest:** Narzędzie wbudowane w framework, pozwalające pisać zapytania do bazy w czystym PHP, zamiast surowym językiem SQL.
**Jak to wytłumaczyć:** Zamiast pisać tekstowe `SELECT * FROM invoices`, wywołuję bezpieczną funkcję PHP `DB::table('invoices')->get()`. Dzięki temu system sam tłumaczy kod na SQL dla wybranej bazy, a przy okazji automatycznie w 100% chroni bazę przed atakami hakerskimi typu *SQL Injection*.

### 3. ORM (w Laravelu: Eloquent)
**Co to jest:** ORM (Object-Relational Mapping) to inteligentny "tłumacz" między klasami PHP a tabelami bazy danych.
**Jak to wytłumaczyć:** To dzięki ORM na prezentacji operujemy na ładnych obiektach programistycznych. Program pobierając wiersz faktury z bazy traktuje go jak Obiekt `$invoice`, na którym można używać funkcji i sprawdzać właściwości. Zwalnia to programistów z pisania brudnego kodu SQL.

### 4. Trait (Cecha)
**Co to jest:** Moduł z kodem, który można wstrzyknąć do dowolnej innej klasy jak "klocek Lego" (np. `HasTenant`).
**Jak to wytłumaczyć:** Gwarantuje zachowanie czystości kodu i zasady DRY (Don't Repeat Yourself). Jeśli model faktury i model leada potrzebują weryfikacji firmy, piszę jeden logiczny "Trait" i wpinam go używając słówka `use` w obu miejscach zamiast dublować kod.

### 5. Relacja Polimorficzna (Polymorphic Relation / MorphTo)
**Co to jest:** Zdolność rekordu w bazie (np. faktury) do bycia połączonym dynamicznie z *wieloma różnymi* tabelami.
**Jak to wytłumaczyć:** Faktura nie jest powiązana twardo tylko z "Użytkownikiem". Ma zdefiniowane kolumny `customer_id` oraz inteligentny `customer_type`. Zapisując tam typ np. "Lead" lub "User", możemy przypiąć jeden dokument faktury do zupełnie odrębnych encji w bazie bez tworzenia pustych kolumn i psucia struktury tabel.

### 6. Middleware
**Co to jest:** Ochroniarz (bramkarz) stojący pomiędzy żądaniem przeglądarki a systemem.
**Jak to wytłumaczyć:** Każde kliknięcie w panelu zanim dotrze do kontrolera przechodzi przez "Stos warstw" (Middlewares). To one sprawdzają m.in., czy użytkownik ma uprawnienia, czy firma opłaciła subskrypcję i szyfrują ciasteczka. Jeśli warunek nie jest spełniony, Middleware odrzuca żądanie natychmiast, chroniąc głębsze warstwy logiki.

### 7. Webhook
**Co to jest:** Pasywna forma komunikacji API. Serwer daje komuś znać, gdy tylko coś się wydarzy.
**Jak to wytłumaczyć:** Zamiast wysyłać zapytania co 5 minut do Stripe'a z pytaniem "Czy jest nowa wpłata?", dajemy Stripe'owi tzw. Webhook (Adres URL). Stripe sam inicjuje do nas potajemne zaszyfrowane zapytanie, w momencie gdy w banku zostanie zablokowana opłata. Gwarantuje to asynchroniczne odpalenie naszego systemu (np. księgowego).

### 8. Mass Assignment Vulnerability
**Co to jest:** Dziura w bezpieczeństwie (podatność), która pozwala hakerowi dopisać w formularzu niewidoczne pole (np. "jestem_adminem = 1") i oszukać bazę.
**Jak to wytłumaczyć:** Bronimy się przed tym używając nowoczesnych "Atrybutów" (np. `#[Fillable]`) w PHP 8. Definiujemy tam twardą, "białą listę" pól (np. tylko Imię i Nazwisko), ignorując wszystko inne. Zapobiega to masowemu, ślepemu zatwierdzaniu danych przychodzących od złośliwych formularzy wstrzykniętych na naszą stronę.

### 9. ULID (Universally Unique Lexicographically Sortable Identifiers)
**Co to jest:** Typ identyfikatora (numeru) generowany jako 26 znakowy string zamiast kolejnych cyfr 1,2,3. 
**Jak to wytłumaczyć:** Standardowe auto-numerowanie (1, 2, 3) rodzi ryzyko Enumeracji (konkurencja zakłada konta i licząc przerwy między "ID", bada nasz ruch firmowy). ULID rozwiązuje ten problem. A od starszego rozwiązania o nazwie "UUID" różni się tym, że pozwala zachować chronologiczne sortowanie (bo ma zaszyty w sobie "timestamp" - datę), dzięki czemu wyszukiwanie rośnie wykładniczo bez rozwalania ułożenia tabel w bazie danych (B-Tree Fragmentations).

### 10. Interfejs (Kontrakt / np. `implements Scope`)
**Co to jest:** Prawna umowa w kodzie, zmuszająca klasę do posiadania określonych funkcji.
**Jak to wytłumaczyć:** Kiedy dopisuję do klasy `implements Scope`, zmuszam ją do przestrzegania zasad narzuconych przez system. To jak zatrudnienie ochroniarza – musi mieć wpisane w umowie umiejętności, inaczej system wyrzuci błąd. Gwarantuje to, że żadna ważna metoda (jak np. filtr bazy danych) nie zostanie "zapomniana" przez programistę.

### 11. Silnie Typowany Interfejs (Strongly Typed Interface)
**Co to jest:** Zabezpieczenie przed przesyłaniem złych typów danych.
**Jak to wytłumaczyć:** Mój system nie "zgaduje" z czym ma do czynienia. Kiedy framework prosi o przekazanie mu filtru "Scope", przyjmie tylko i wyłącznie klasę, która oficjalnie zadeklarowała, że nim jest. Całkowicie wyklucza to błędy (tzw. bugs), w których jeden moduł wywołuje drugi z niepoprawnymi danymi.

### 12. Architektura SOLID
**Co to jest:** Złote, ogólnoświatowe zasady inżynierii oprogramowania i tworzenia bezawaryjnego kodu.
**Jak to wytłumaczyć:** Mówiąc o zachowaniu SOLID, daję jury jasny sygnał: "nie pisałem tego kodu byle jak (tzw. spaghetti code)". Mój kod spełnia rygorystyczne normy inżynieryjne (Wzorce Projektowe) i nawet jeśli za 3 lata usiądzie do niego inny programista z drugiego końca świata, od razu zrozumie jego strukturę.
