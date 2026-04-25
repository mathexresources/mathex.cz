# Setup Guide / Průvodce nastavením

## English

### Prerequisites

- Docker 24+ and Docker Compose v2
- Git

### Quick start

```bash
git clone <repo-url> mathex.cz
cd mathex.cz
make up          # copies .env.example → .env, builds images, starts containers
make migrate     # runs all Phinx migrations
make seed        # seeds sample data
```

The app is now running at **http://localhost**.
Adminer (DB browser): http://localhost:8080 — server `mysql`, user `mathex`.
Mailpit (mail catcher): http://localhost:8025.

### First admin account

```bash
make create-admin email=admin@example.com password=secret123 name="Admin"
```

Or directly via the CLI:

```bash
make console cmd="app:create-admin admin@example.com secret123 Admin"
```

### Environment variables

Copy `.env.example` to `.env` and adjust:

| Variable | Default | Description |
|---|---|---|
| `APP_ENV` | `development` | `development` or `production` |
| `APP_URL` | `http://localhost` | Public URL (used in e-mails, sitemap) |
| `DB_PASSWORD` | `mathex_secret` | MySQL user password |
| `DB_ROOT_PASSWORD` | `root_secret` | MySQL root password |
| `MAIL_HOST` | `mailpit` | SMTP host |
| `MAIL_FROM` | `noreply@mathex.cz` | Sender address |
| `REDIS_HOST` | `redis` | Redis hostname |

---

## Česky

### Požadavky

- Docker 24+ a Docker Compose v2
- Git

### Rychlý start

```bash
git clone <repo-url> mathex.cz
cd mathex.cz
make up          # zkopíruje .env.example → .env, sestaví image, spustí kontejnery
make migrate     # spustí všechny Phinx migrace
make seed        # naplní ukázková data
```

Aplikace běží na **http://localhost**.
Adminer: http://localhost:8080 — server `mysql`, uživatel `mathex`.
Mailpit: http://localhost:8025.

### První administrátorský účet

```bash
make create-admin email=admin@example.com password=secret123 name="Admin"
```

### Proměnné prostředí

Zkopírujte `.env.example` do `.env` a upravte hodnoty (viz tabulka výše).
