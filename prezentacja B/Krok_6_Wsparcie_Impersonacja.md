# 🧠 KROK 6: Wsparcie Techniczne (Impersonacja i Global Scopes Bypass)

**Plik do omówienia:** `app/Livewire/BedrijfSwitcher.php`

## O czym jest ten krok?
To jest ten moment na prezentacji, gdzie udowadniasz, że potrafisz stworzyć nie tylko działającą stronę, ale prawdziwy produkt SaaS z panelem administracyjnym. 
Jury usłyszało już w Kroku 2 o `BedrijfScope`, który absolutnie izoluje dane między firmami. Pojawia się oczywisty problem biznesowy: Jak programista ma sprawdzić, dlaczego u klienta A "coś nie działa", skoro sam nie ma dostępu do jego faktur? Odpowiedzią jest mechanizm **Impersonacji (Wcielania się)**.

## Co musisz wytłumaczyć z kodu (dla Vakjury):

### 1. Zmiana tożsamości w pamięci (Manipulacja Sesją)
System pozwala Ci "wejść w buty" klienta bez podawania jego hasła.
- **Tłumaczenie dla Jury:** "Wcielenie się w inną firmę to nic innego jak żonglowanie zmiennymi w globalnej tablicy `session()`. Klasa `BedrijfSwitcher` podmienia pod spodem identyfikator zalogowanego użytkownika. Z racji, że cały mój system na poziomie bazy danych ufa ID z sesji (dzięki Traitowi HasBedrijf), nagle cały panel dynamicznie ładuje dane tylko dla tej wybranej firmy."

### 2. Ominięcie własnych zabezpieczeń - `withoutGlobalScopes()`
To jest kluczowe! Powrót do konta Super Admina łamałby się przez Twoje własne zabezpieczenia.
- **Tłumaczenie dla Jury:** "Paradoksem szczelności mojego systemu było to, że nie mogłem z niego... wyjść. Będąc wcielonym w firmę klienta, mój własny `BedrijfScope` działał tak bezwzględnie, że ukrywał przede mną moje własne konto Super Administratora. 
- Rozwiązałem ten problem architektoniczny w metodzie `leaveImpersonation()`. Nakazuję Eloquentowi zignorowanie wtyczek filtrujących zapytanie poprzez metodę `withoutGlobalScopes()`. Dzięki temu wyciągam profil administratora z pełnej bazy, omijam Multi-Tenancy na czas tego jednego zapytania, niszczę sesję klienta i na nowo uwierzytelniam własne konto. To dowodzi, że w pełni kontroluję cykl życia modelu w Laravelu."
