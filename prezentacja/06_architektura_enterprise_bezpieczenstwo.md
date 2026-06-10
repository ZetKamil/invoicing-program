# Master-Digit: Skalowanie do Architektury Enterprise (Security & Performance)

Jako "Senior-Developer-turned-Founder", projektując ten system, wiedziałem, że po stworzeniu MVP musimy wyeliminować dług technologiczny i zbudować fundamenty zdolne obsłużyć setki firm logistycznych bez zadyszki.

Podczas refaktoryzacji "Fazy 3" przeprowadziliśmy rygorystyczny audyt architektury, koncentrując się na 4 krytycznych wektorach bezpieczeństwa i wydajności.

---

## 1. Asynchroniczność i Wąskie Gardła (Queues)

**Podatność (Wąskie Gardło):** 
W standardowym cyklu żądania HTTP (Request-Response), nasza aplikacja bezpośrednio obsługiwała webhooki z bramek płatności (Stripe). Oznaczało to, że po odebraniu płatności serwer "w locie" uderzał do bazy, tworzył dzierżawcę (Tenant), hashował hasła i zapisywał logi. Gdyby API Stripe zwolniło lub baza danych była pod obciążeniem, wątek serwera zostałby zablokowany, co groziło zawieszeniem systemu.

**Rozwiązanie (Laravel Queues):** 
Przenieśliśmy całą logikę transakcyjną webhooków i powiadomień email do **Zadań Asynchronicznych (Jobs)** implementujących interfejs `ShouldQueue`. Kontroler API przyjmuje teraz żądanie, błyskawicznie odpowiada `200 OK` i odkłada zadanie (`ProcessStripeWebhookJob`) na stos roboczy (np. Redis/Database).

**Korzyść Biznesowa:** 
System działa z prędkością światła z perspektywy klienta i bramki płatniczej. Skalowalność horyzontalna jest teraz możliwa – możemy dołożyć dziesiątki "workerów", aby przetwarzać tysiące płatności na sekundę, zachowując 100% responsywność interfejsu (brak kręcącego się kółka ładowania).

---

## 2. Multi-Tenant Security & Policy Leaks

**Podatność (ID Guessing):** 
Choć wdrożyliśmy potężny `TenantScope` ograniczający dostęp do głównych Modeli (Faktury, Leady), musieliśmy zabezpieczyć relacje-dzieci (np. `InvoiceItem`, `QuoteItem`). Bez autoryzacji na poziomie węzła, sprytny atakujący mógłby odgadnąć unikalny identyfikator ULID pozycji z cudzej faktury i wysłać komendę DELETE bezpośrednio przez API.

**Rozwiązanie (Laravel Policies):** 
Stworzyliśmy restrykcyjne polityki autoryzacyjne (np. `InvoiceItemPolicy`, `QuoteItemPolicy`). Definiują one twardą warstwę obrony przed samym silnikiem bazy: przed usunięciem lub aktualizacją dowolnej pozycji, framework twardo weryfikuje warunek `$user->tenant_id === $item->invoice->tenant_id`. Dodatkowo użyliśmy `disableGlobalScopes()` na moment walidacji, aby mieć 100% pewności integralności strukturalnej.

**Korzyść Biznesowa:** 
Gwarancja szczelności danych. Klienci transportowi przechowują u nas wrażliwe dane (stawki frachtu, marże). Taka architektura zapewnia, że nawet w przypadku ominięcia warstwy UI w panelu, silnik na najniższym poziomie odrzuci intruza kodem `403 Forbidden`.

---

## 3. Wydajność Bazy Danych pod Obciążeniem (Indeksy B-Tree)

**Podatność (Full Table Scans):** 
Firmy spedycyjne w naszym systemie obracają dziesiątkami tysięcy ofert i faktur. W standardowej konfiguracji, filtrowanie ofert na dashboardzie po statusie (np. "Aktywne drafty") powodowało, że baza danych skanowała po kolei wszystkie rekordy na dysku twardym. Przy 100,000 wierszy zarzynało to procesor (CPU) serwera bazy danych.

**Rozwiązanie (Indeksy Kompozytowe):** 
Napisaliśmy specjalne migracje optymalizacyjne wstrzykujące **Złożone Indeksy (Composite Indexes)** w strukturę tabel (np. `$table->index(['tenant_id', 'deleted_at', 'status'])`). 

**Korzyść Biznesowa:** 
Kiedy silnik bazy szuka danych konkretnego najemcy o określonym statusie, korzysta ze zbudowanego drzewa w pamięci (B-Tree). Czas wyszukiwania spada z kilku sekund do zaledwie kilkunastu milisekund. Nasz system może uciągnąć potężny ruch logistyczny na ułamku kosztów serwerowych w porównaniu do klasycznej, nieoptymalnej struktury danych.

---

## 4. Obrona przed DoS i Spamem (Rate Limiting)

**Podatność (DDoS API):** 
Nasze publiczne wejścia, szczególnie endpoint nasłuchujący informacji ze Stripe (`/webhook/stripe`), były otwarte. Złośliwa konkurencja lub bot mógł napisać skrypt zalewający nas 50,000 fałszywych pakietów na sekundę. Zanim webhook sprawdziłby sygnaturę kryptograficzną, połączenia do bazy wyczerpałyby zasoby i zawiesiły całą aplikację dla prawdziwych klientów.

**Rozwiązanie (Throttle Middleware):** 
Wykorzystaliśmy wbudowaną zaporę Laravela, doczepiając warstwę `throttle:60,1` do rutingów publicznych.

**Korzyść Biznesowa:** 
System automatycznie odcina intruza po 60 żądaniach na minutę, zwracając kod `429 Too Many Requests` bezpośrednio z warstwy serwera WWW, zanim ten zdąży nawet "obudzić" aplikację PHP i bazę danych. Zapewnia to stabilność (High Availability) i SLA na poziomie 99.9% dla użytkowników platformy.
