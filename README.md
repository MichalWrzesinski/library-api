# Library API
API dla prostego systemu bibliotecznego przygotowane jako zadanie rekrutacyjne.

## Stack technologiczny
* PHP 8.4
* Symfony 7.4 LTS
* API Platform
* Doctrine ORM
* Doctrine Migrations
* PostgreSQL 16
* PHPUnit
* Doctrine Fixtures Bundle
* PHPStan
* PHP CS Fixer
* Docker / Docker Compose

## Funkcjonalności

### API umożliwia:
* dodawanie nowej książki
* usuwanie wybranej książki
* listowanie książek
* aktualizację wybranej książki
* wypożyczanie książek
* zwracanie książek

### Książka posiada:
* sześciocyfrowy numer seryjny
* tytuł
* autora
* informację o aktualnym statusie wypożyczenia

### Wypożyczenie zawiera:
* sześciocyfrowy numer karty czytelnika
* datę wypożyczenia
* opcjonalną przewidywaną datę zwrotu
* datę faktycznego zwrotu

## Założenia projektowe

Autor został wydzielony jako osobna encja, aby uniknąć duplikowania danych autora przy wielu książkach.

Wypożyczenie jest reprezentowane przez encję `Loan`. Aktualny status książki wynika z aktywnego wypożyczenia, czyli rekordu `Loan`, dla którego `returnedAt` jest puste.

Aplikacja nie przechowuje osobnego pola `isBorrowed` w encji `Book`, ponieważ byłoby to powielenie stanu. Źródłem prawdy jest historia wypożyczeń.

Baza danych posiada partial unique index, który zabezpiecza przed utworzeniem więcej niż jednego aktywnego wypożyczenia dla tej samej książki.

## Uruchomienie

```bash
git clone https://github.com/MichalWrzesinski/library-api.git
cd library-api
```

```bash
docker compose up -d --build
```

```bash
docker compose exec app composer install
```

```bash
docker compose exec app php bin/console doctrine:migrations:migrate
```

Aplikacja będzie dostępna pod adresem:

```text
http://localhost:8080
```

Dokumentacja API Platform:

```text
http://localhost:8080/api
```

## Główne endpointy

```text
GET     /api/books
POST    /api/books
GET     /api/books/{id}
PUT     /api/books/{id}
PATCH   /api/books/{id}
DELETE  /api/books/{id}

POST    /api/books/{id}/borrow
POST    /api/books/{id}/return
```

Dodatkowo dostępne są endpointy dla autorów:

```text
GET     /api/authors
POST    /api/authors
GET     /api/authors/{id}
PATCH   /api/authors/{id}
DELETE  /api/authors/{id}
```

## Kolekcja Postman

W katalogu projektu znajduje się kolekcja Postman:

```text
docs/library-api.postman_collection.json
```

## Testy

Testy funkcjonalne korzystają z osobnej bazy danych:

```text
library_api_test
```

Przygotowanie bazy testowej:

```bash
docker compose exec app php bin/console doctrine:database:create --env=test --if-not-exists
```

```bash
docker compose exec app php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

Uruchomienie testów:

```bash
docker compose exec app composer test
```

## Komendy developerskie

```bash
docker compose exec app composer validate
```

```bash
docker compose exec app composer phpstan
```

```bash
docker compose exec app composer cs:check
```

```bash
docker compose exec app composer cs:fix
```

```bash
docker compose exec app composer test
```
