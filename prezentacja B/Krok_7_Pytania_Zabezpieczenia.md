# 🧠 KROK 6: Pytania Jury i Zabezpieczenia (Defensywa)

**Plik:** Pytania otwarte z boku. 

## Pytanie 1: "Jak zabezpieczyłeś panel przed atakiem CSRF?"
> "Laravel posiada to natywnie. Do każdego żądania (POST/PUT) wysyłanego z przeglądarki, dołączany jest unikalny kryptograficzny **Token CSRF**. Jeśli atakujący stworzy fałszywą wersję mojej strony na innym serwerze i spróbuje wymusić na zalogowanym dyspozytorze usunięcie faktury, jego żądanie nie będzie zawierało prawidłowego tokenu. Serwer mojego systemu natychmiast odrzuci ten request błędem HTTP 419 (Page Expired)."

## Pytanie 2: "Czy mogę podejrzeć dane innej firmy wchodząc w link np. /customers/3 ?"
> "Nie, próba ta kwalifikuje się jako atak **IDOR** (Insecure Direct Object Reference) i mój system jest na niego systemowo uodporniony. Omawialiśmy to w kroku dotyczącym architektury Multi-Tenancy. Dzięki podpięciu `BedrijfScope` wewnątrz Traita `HasBedrijf`, framework modyfikuje każde zapytanie typu SELECT (w Query Builderze) zanim w ogóle dotrze ono do fizycznej bazy SQL. Zapytanie zyskuje klauzulę `WHERE`, przez co sprawdza wyłącznie rekordy o `bedrijf_id` zalogowanego użytkownika. Ochrona działa na tak niskim poziomie, że złośliwy użytkownik otrzyma odpowiedź '404 Not Found' – z perspektywy jego izolowanej sesji ten klient po prostu 'nie istnieje'."

## Pytanie 3: "A jak strona kontaktuje się z serwerem, że to działa tak płynnie, bez odświeżania okna przeglądarki (np. gdy wysyłam wycenę)?"
> "Panel jest napisany w oparciu o silnik **Livewire**. Oznacza to, że zamiast wymuszać twarde przeładowanie całego szkieletu HTML, system pod spodem używa asynchronicznych requestów Fetch API. Kod w moim PHP (klasy Action) wykonuje operację matematyczną, po czym zwraca zaledwie mały kawałeczek zaktualizowanego widoku HTML (DOM Diffing), który podmienia np. tylko wciśnięty guzik na zielony napis 'Sukces'. Daje to klientowi odczucie płynnej strony (Single Page Application), zachowując bezpieczeństwo serwera PHP."
