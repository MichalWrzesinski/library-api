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

## Uruchomienie
```
git clone https://github.com/MichalWrzesinski/library-api.git
cd library-api
```

```
docker compose up -d --build
```

```
docker compose exec app composer install
```

```
docker compose exec app php bin/console about
```

## Komendy developerskie
```
docker compose exec app composer validate
docker compose exec app composer phpstan
docker compose exec app composer cs:check
docker compose exec app composer test
```
