# Verdediging & Parate Kennis (Tarcza na egzamin)

Jury składa się z programistów (vakjury). Nie będą oceniać projektu jak laicy – będą szukać przysłowiowej "dziury w całym". Oto najtrudniejsze pytania, które mogą Ci zadać, wraz z profesjonalnymi odpowiedziami na poziomie Senior Engineera.

### Pytanie 1: "Dlaczego użyłeś SQLite zamiast MySQL/PostgreSQL na produkcję?"
**Twoja odpowiedź:** 
> "Dla celów demonstracyjnych i audytowych wdrożyłem architekturę *Zero-Configuration*. Ponieważ cała aplikacja opiera się na warstwie abstrakcji Laravel Eloquent ORM oraz restrykcyjnych migracjach, baza jest w 100% uniezależniona od drivera. SQLite pozwala na pełną przenośność projektu bez utraty wydajności przy transakcjach ACID, zachowując przy tym ścisłą precyzję typów `decimal(12,2)` i wbudowaną obsługę pól JSON."

### Pytanie 2: "Jak zapewniłeś, że jedna firma nie zobaczy faktur drugiej firmy? Co jeśli zmienią ID w adresie URL?"
**Twoja odpowiedź:** 
> "Bezpieczeństwo opiera się na dwóch liniach obrony. Po pierwsze, nie używamy podatnych na iterację numerów ID (jak 1, 2, 3), lecz losowych, bezpiecznych kryptograficznie tokenów **ULID**. Po drugie, wdrożyłem globalny mechanizm izolacji za pomocą Laravel **Global Scopes** w dedykowanym traicie `HasTenant`. Każde zapytanie do bazy danych automatycznie i na najniższym poziomie dokleja warunek `WHERE tenant_id = current_tenant`. Jeśli użytkownik spróbuje wpisać ULID innej firmy, system zareaguje statusem `404 Not Found`, natychmiastowo blokując dostęp."

### Pytanie 3: "Widzę, że przy rejestracji wysyłasz dane do Stripe. Czy hasło użytkownika przesyłane jest w metadanych sesji Stripe?"
**Twoja odpowiedź:** 
> "Absolutnie nie. Przesyłanie poufnych danych tekstowych (lub nawet hashowanych) przez Stripe Metadata to poważna podatność bezpieczeństwa (*data leakage*). Zastosowałem autorski wzorzec **Webhook-First Provisioning**. Podczas rejestracji pełny payload jest bezpiecznie szyfrowany lokalnie w Cache aplikacji pod unikalnym kluczem UUID. Do Stripe przesyłamy wyłącznie ten bezwartościowy dla osób trzecich token. Dopiero po autoryzacji płatności, asynchroniczny Webhook odbiera UUID ze Stripe, pobiera dane z lokalnego Cache, tworzy bezpiecznie konto i natychmiastowo niszczy pamięć podręczną."

### Pytanie 4: "Jak rozwiązałeś problem z autoryzacją w Webhooku Stripe? Przecież Stripe nie jest zalogowany w Twojej aplikacji."
**Twoja odpowiedź:** 
> "To była jedna z głównych trudności architektonicznych. Ponieważ webhook to komunikacja server-to-server, standardowa autoryzacja sesji użytkownika tam nie istnieje. W `StripeWebhookController` wyłączyłem weryfikację CSRF dla tej jednej ścieżki, ale zabezpieczyłem endpoint poprzez bezwzględną weryfikację podpisu cyfrowego Stripe (**Stripe-Signature**). Aby zapisać dane nowej firmy w bazie, system tymczasowo omija globalne zabezpieczenia multi-tenancy za pomocą natywnej metody `Invoice::withoutGlobalScopes()`, ponieważ ta konkretna operacja jest wykonywana przez zaufany proces systemowy, a nie przez zalogowanego klienta."
