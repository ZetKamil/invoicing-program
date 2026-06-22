# 🧠 KROK 2: Ochrona Danych (Multi-Tenant & Global Scopes)

**Plik:** `app/Models/Scopes/BedrijfScope.php`

## O czym jest ten krok?
Kiedy utworzysz już klienta i wejdziesz w jego szczegóły, pojawia się pytanie o bezpieczeństwo. Skąd system wie, który klient należy do Twojej firmy, a który do konkurencji, która wynajmuje miejsce na tym samym serwerze (Multi-Tenancy)? To jest kluczowy punkt, na którym zdobędziesz szacunek Jury.

## Co musisz wytłumaczyć z kodu?

### 1. Wzorzec "Global Scope"
Global Scope to strażnik zapytania bazodanowego.
- **Jak to działa:** Zamiast pamiętać, żeby w każdym kontrolerze (CustomersController, QuotesController) dopisywać do zapytań SQL warunek `WHERE bedrijf_id = 1`, Laravel automatycznie przechwytuje zapytanie.
- **Linijka z kodu:** `$builder->where($model->getTable() . '.bedrijf_id', $user->bedrijf_id);`
- **Tłumaczenie:** "Framework dokleja ten warunek niewidzialnie pod spodem przed wysłaniem zapytania do bazy MariaDB/MySQL. Chroni to system przed błędem ludzkim programisty."

### 2. Ochrona przed atakami IDOR (Insecure Direct Object Reference)
- Co jeśli użytkownik zmieni ID w pasku adresu (np. z `/customers/2` na `/customers/5`) licząc na zobaczenie klienta innej firmy?
- **Tłumaczenie dla Jury:** "Nawet jeśli haker zgadnie ID klienta konkurencji i spróbuje wymusić wyświetlenie go z paska adresu URL, mój `BedrijfScope` zareaguje natychmiast. Baza danych odpowie kodem HTTP 404 (Not Found), ponieważ system szuka rekordu, który jednocześnie ma ID 5 ORAZ `bedrijf_id` pasujące do mojej firmy. Wyciek danych w architekturze Multi-Tenant jest tu fizycznie niemożliwy."
