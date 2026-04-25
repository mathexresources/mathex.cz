# Deployment Guide / Průvodce nasazením

## English

### Requirements

- Server with Docker 24+ and Docker Compose v2
- Domain pointed to server IP
- Let's Encrypt certificate (or your own)

### 1. Obtain TLS certificate

```bash
apt install certbot
certbot certonly --standalone -d mathex.cz -d www.mathex.cz
```

Certificates land at `/etc/letsencrypt/live/mathex.cz/`.

### 2. Configure environment

```bash
cp .env.example .env
# Set APP_ENV=production, APP_URL=https://mathex.cz
# Set strong passwords for DB_PASSWORD, DB_ROOT_PASSWORD
# Set real MAIL_HOST, MAIL_PORT, MAIL_FROM
```

### 3. First deploy

```bash
./deploy.sh
```

This will:
1. Pull latest code
2. Build the production Docker image (`target: prod`)
3. Run database migrations
4. Clear application cache
5. Regenerate `sitemap.xml`
6. Restart all containers

### 4. Subsequent deploys

```bash
./deploy.sh
```

The script is idempotent.

### 5. Scheduled tasks (cron)

Add to root crontab (`crontab -e`):

```cron
# GDPR data retention cleanup — every Sunday at 03:00
0 3 * * 0 cd /srv/mathex.cz && docker-compose -f docker-compose.prod.yml exec -T app php bin/console app:cleanup >> /var/log/mathex-cleanup.log 2>&1

# Daily backup at 02:00
0 2 * * * cd /srv/mathex.cz && COMPOSE_FILE=docker-compose.prod.yml bash docker/scripts/backup.sh >> /var/log/mathex-backup.log 2>&1

# Weekly sitemap regeneration — Monday at 04:00
0 4 * * 1 cd /srv/mathex.cz && docker-compose -f docker-compose.prod.yml exec -T app php bin/console app:generate-sitemap >> /var/log/mathex-sitemap.log 2>&1
```

### 6. Monitoring

Health check endpoint: `https://mathex.cz/health`

Expected response:
```json
{"status":"ok","db":"ok","redis":"ok"}
```

HTTP 503 is returned when any dependency is down.

---

## Česky

### Požadavky

- Server s Dockerem 24+ a Docker Compose v2
- Doména směrující na IP serveru
- Let's Encrypt certifikát (nebo vlastní)

### 1. Získání TLS certifikátu

```bash
apt install certbot
certbot certonly --standalone -d mathex.cz -d www.mathex.cz
```

### 2. Konfigurace prostředí

```bash
cp .env.example .env
# Nastavte APP_ENV=production, APP_URL=https://mathex.cz
# Nastavte silná hesla pro DB_PASSWORD, DB_ROOT_PASSWORD
# Nastavte reálné MAIL_HOST, MAIL_PORT, MAIL_FROM
```

### 3. První nasazení

```bash
./deploy.sh
```

### 4. Aktualizace

```bash
./deploy.sh
```

### 5. Plánované úlohy

Přidejte do cronu (viz anglická část výše).

### 6. Monitoring

Endpoint pro kontrolu stavu: `https://mathex.cz/health`
