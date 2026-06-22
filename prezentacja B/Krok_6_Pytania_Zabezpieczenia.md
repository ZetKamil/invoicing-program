# 🧠 KROK 6: Pytania Jury i Zabezpieczenia (Defensywa)

**Plik:** Pytania otwarte z boku. 

## Pytanie 1: "Jak zabezpieczyłeś panel przed atakiem CSRF?"
> "Laravel posiada to natywnie. Do każdego żądania (POST/PUT) wysyłanego z przeglądarki, dołączany jest unikalny kryptograficzny **Token CSRF**. Jeśli atakujący stworzy fałszywą wersję mojej strony na innym serwerze i spróbuje wymusić na zalogowanym dyspozytorze usunięcie faktury, jego żądanie nie będzie zawierało prawidłowego tokenu. Serwer mojego systemu natychmiast odrzuci ten request błędem HTTP 419 (Page Expired)."

## Pytanie 2: "Czy mogę podejrzeć dane innej firmy wchodząc w link np. /customers/3 ?"
> "Nie, jest to atak typu **IDOR** (Insecure Direct Object Reference) i mój system jest na to w 100% uodporniony. Pokazywałem wcześniej klasę `BedrijfScope.php`. Framework sprawdza w każdej ułamkowej sekundzie, czy `bedrijf_id` zasobu numer 3 równa się id firmy obecnie zalogowanego użytkownika. Ochrona działa na najniższym szczeblu bazy danych, haker dostanie po prostu '404 Not Found' – system w ogóle nie przyzna się, że taki klient istnieje."

## Pytanie 3: "A jak strona kontaktuje się z serwerem, że to działa tak płynnie, bez odświeżania okna przeglądarki (np. gdy wysyłam wycenę)?"
> "Panel jest napisany w oparciu o silnik **Livewire**. Oznacza to, że zamiast wymuszać twarde przeładowanie całego szkieletu HTML, system pod spodem używa asynchronicznych requestów Fetch API. Kod w moim PHP (klasy Action) wykonuje operację matematyczną, po czym zwraca zaledwie mały kawałeczek zaktualizowanego widoku HTML (DOM Diffing), który podmienia np. tylko wciśnięty guzik na zielony napis 'Sukces'. Daje to klientowi odczucie płynnej strony (Single Page Application), zachowując bezpieczeństwo serwera PHP."
