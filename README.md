# Mathex.cz – Osobní web freelance PHP vývojáře

> Nette Framework · PHP 8.2 · MySQL 8 · Redis · Docker

---

## Obsah / Table of Contents

- [Česky](#česky)
- [English](#english)

---

## Česky

### Požadavky

- [Docker](https://docs.docker.com/get-docker/) ≥ 24
- [Docker Compose](https://docs.docker.com/compose/) ≥ 2.20
- `make` (na Windows: Git Bash nebo WSL)

### Rychlý start

```bash
# 1. Klonování repozitáře
git clone https://github.com/mathexresources/mathex.cz.git
cd mathex.cz

# 2. Spuštění (automaticky zkopíruje .env.example → .env)
make up

# 3. Instalace PHP závislostí
make install

# 4. Spuštění migrací a seedů
make migrate
make seed
```

Po spuštění jsou dostupné tyto adresy:

| Služba    | URL                        | Poznámka                             |
|-----------|----------------------------|--------------------------------------|
| Web       | http://localhost           |                                      |
| Admin     | http://localhost/admin     | user: `admin` / heslo z `.env`       |
| Adminer   | http://localhost:8080      | server: `mysql`, user: `mathex`      |
| Mailpit   | http://localhost:8025      | Zachycená pošta (lokální SMTP)       |

### Konfigurace prostředí

Soubor `.env` se vytvoří automaticky z `.env.example` při prvním `make up`.
Změňte alespoň:

- `APP_SECRET` – náhodný řetězec ≥ 32 znaků
- `ADMIN_PASSWORD` – heslo pro admina (použije se při seedování)
- E-mailové adresy (`MAIL_FROM`)

### Lokální Nette konfigurace

Pro přepsání nastavení mimo Docker (např. `php -S`) vytvořte:

```bash
cp app/Config/local.neon.example app/Config/local.neon
# upravte local.neon dle potřeby
```

Soubor `local.neon` je v `.gitignore` a nikdy se nesmí commitovat.

### Adresářová struktura

```
app/
├── Bootstrap.php          # Nette Configurator
├── Config/                # .neon konfigurační soubory
├── Model/
│   ├── Database/          # Repositáře (Nette Database Explorer)
│   ├── Mail/              # Odesílání e-mailů
│   └── Security/          # Authenticator
├── Presenters/            # Front + Admin presentery
│   └── Admin/
├── Components/            # Znovupoužitelné UI komponenty
│   └── ContactForm/
└── Templates/             # Latte šablony
    ├── @layout.latte
    ├── Homepage/
    ├── Project/
    ├── Contact/
    ├── Error/
    └── Admin/
bin/
├── migrate.php            # Spouštěč migrací
└── seed.php               # Spouštěč seedů
docker/
├── nginx/default.conf     # Nginx konfigurace
├── php/                   # php.ini, xdebug, fpm conf
└── mysql/init/            # SQL skripty spuštěné při prvním startu MySQL
migrations/                # SQL migrační soubory (číslované)
seeds/                     # Seedovací PHP skripty
tests/                     # PHPUnit testy
www/                       # Document root (Nginx/Apache)
├── index.php
├── .htaccess
└── assets/
    ├── css/
    ├── js/
    └── images/
```

### Dostupné příkazy

```bash
make up           # Spustí všechny kontejnery
make down         # Zastaví kontejnery
make restart      # Restartuje kontejnery
make build        # Přestaví Docker image (bez cache)
make shell        # Shell v PHP kontejneru
make shell-mysql  # MySQL klient
make logs         # Sledování logů
make install      # composer install
make migrate      # Spustí migrace
make seed         # Spustí seedery
make db-reset     # migrate + seed
make clear-cache  # Smaže Nette cache v temp/
make stan         # Analýza PHPStan
make test         # PHPUnit testy
```

### Xdebug (PhpStorm)

Xdebug 3 je předkonfigurován pro port `9003`. V PhpStormu:
1. *Settings → PHP → Debug* – ověřte port 9003
2. Přidejte server `localhost` s mapováním `/var/www/html` → kořen projektu
3. Spusťte *Listen for PHP Debug Connections*

### Produkční build

```bash
docker build --target prod -t mathex-prod .
```

---

## English

### Requirements

- [Docker](https://docs.docker.com/get-docker/) ≥ 24
- [Docker Compose](https://docs.docker.com/compose/) ≥ 2.20
- `make`

### Quick start

```bash
# 1. Clone the repository
git clone https://github.com/mathexresources/mathex.cz.git
cd mathex.cz

# 2. Start services (auto-copies .env.example → .env)
make up

# 3. Install PHP dependencies
make install

# 4. Run migrations and seeders
make migrate
make seed
```

Services available after startup:

| Service   | URL                        | Notes                                  |
|-----------|----------------------------|----------------------------------------|
| Website   | http://localhost           |                                        |
| Admin     | http://localhost/admin     | user: `admin` / password from `.env`   |
| Adminer   | http://localhost:8080      | server: `mysql`, user: `mathex`        |
| Mailpit   | http://localhost:8025      | Captured outgoing mail (local SMTP)    |

### Environment configuration

`.env` is created automatically from `.env.example` on first `make up`.
At minimum, change:

- `APP_SECRET` – random string ≥ 32 chars
- `ADMIN_PASSWORD` – admin password used during seeding
- Mail addresses (`MAIL_FROM`)

### Local Nette override

To override settings when running outside Docker:

```bash
cp app/Config/local.neon.example app/Config/local.neon
# edit local.neon as needed
```

`local.neon` is git-ignored and must never be committed.

### Tech stack

| Layer       | Technology                         |
|-------------|-------------------------------------|
| Language    | PHP 8.2                             |
| Framework   | Nette Framework 3.x                 |
| Templates   | Latte 3                             |
| Database    | MySQL 8.0 via Nette Database        |
| Cache       | File cache + Redis sessions         |
| Mail        | Nette Mail / SMTP (Mailpit locally) |
| Web server  | Nginx 1.27                          |
| Container   | Docker + Docker Compose             |
| Debugging   | Tracy + Xdebug 3                    |
| Tests       | PHPUnit 11                          |
| Static analysis | PHPStan level 6                 |

### Production build

The Dockerfile uses multi-stage builds:

- **`dev`** – includes Xdebug, OPcache disabled, mounts source as a volume
- **`prod`** – no dev tools, OPcache on, source copied into image

```bash
# Build production image
docker build --target prod -t mathex-prod .

# Run (supply env vars externally, e.g. via --env-file or Kubernetes secrets)
docker run -p 80:9000 --env-file .env.prod mathex-prod
```

---

## License

Proprietary – all rights reserved.
