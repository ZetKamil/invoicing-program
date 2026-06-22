# 🧠 KROK 5: Płatności Online (Stripe i Webhooki)

**Plik:** `app/Http/Controllers/StripeWebhookController.php`

## O czym jest ten krok?
Wyjaśniasz komunikację między serwerami (Server-to-Server). Twój program musi odnotować, że klient zapłacił kartą, mimo że klient wpisywał numer karty na zewnętrznej, amerykańskiej stronie internetowej firmy Stripe, a nie w Twojej aplikacji. 

## Co musisz wytłumaczyć z kodu?

### 1. Architektura Webhooków (Mechanika eventów)
Zwykle to Twoja przeglądarka wysyła prośbę do serwera (np. odświeżając stronę). Tutaj mechanika jest odwrócona.
- **Tłumaczenie dla Jury:** "Stworzyłem tzw. 'Webhook Endpoint'. To jest specjalny, otwarty portret mojego serwera. Gdy klient skończy proces płatności po stronie Stripe'a, ich ogromne serwery w USA wysyłają do mnie na port 80/443 zapytanie POST w technologii Server-to-Server z zawartością paczki JSON. Nie muszę dzięki temu non-stop 'pytać' Stripe'a czy klient zapłacił, to Stripe sam daje mi znać w sekundę, w której karta przejdzie."

### 2. Bezpieczeństwo i Logika Webhooka
Pokaż im w kodzie linijkę `case 'checkout.session.completed':` i opisz, jak działa ten router.
- **Tłumaczenie dla Jury:** "W tym kontrolerze analizuję typ nadchodzącego zdarzenia. Interesuje mnie tylko to konkretne zdarzenie – sukces płatności (checkout.session.completed). Ze środka, za pomocą tablicy asocjacyjnej, wyciągam wstrzyknięte wcześniej przeze mnie `invoice_id`. Na jego podstawie znajduję odpowiednią fakturę w bazie danych i automatycznie podnoszę jej status na 'PAID'. Dyspozytor po wejściu do panelu widzi od razu zielony znaczek uregulowanej płatności, nie musząc logować się na swoje konto bankowe."
