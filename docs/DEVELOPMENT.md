# Development Guide / Vývojářský průvodce

## English

### Tech stack

| Layer | Technology |
|---|---|
| Language | PHP 8.2 |
| Framework | Nette 3.2 (Application, Forms, Database, Security, DI) |
| Templates | Latte 3 |
| Database | MySQL 8.0 (Nette Database Explorer) |
| Cache / sessions | Redis 7 (contributte/redis) |
| Localization | contributte/translation (CS + EN) |
| CLI | contributte/console (Symfony Console) |
| Logging | Monolog (PSR-3) |
| Migrations | Phinx |
| Markdown | league/commonmark |
| Frontend | Vanilla JS + CSS custom properties |
| Server | Nginx 1.27 + PHP-FPM 8.2 |
| Dev tools | PHPStan, PHPUnit, Nette Tester, Xdebug |

### Project structure

```
app/
  Command/       Symfony Console commands (app:*)
  Components/    Nette form factory components
  Config/        DI configuration (common.neon, services.neon)
  Lang/          Translation files (messages.cs.neon, messages.en.neon)
  Model/
    Database/    Nette Database repositories
    Mail/        Mail renderer and sender
    Security/    Authenticator
    Service/     Business-logic services (Slug, Markdown, Image, Rate limiter, Logger…)
  Presenters/    Nette presenters (public + Admin/ sub-namespace)
  Router/        RouterFactory.php
  Templates/     Latte templates (mirrors Presenters/ layout)
bin/
  console        Symfony Console entry point
  cleanup.php    Legacy GDPR cleanup script (use app:cleanup instead)
docker/
  nginx/         Nginx configs (dev + prod)
  php/           PHP-FPM configs and php.ini overrides
  scripts/       backup.sh
docs/            This documentation
migrations/      Phinx migration files
seeds/           Phinx seeder files
tests/
  bootstrap.php  PHPUnit bootstrap
  Model/         PHPUnit integration tests
  tester/
    bootstrap.php     Nette Tester bootstrap
    unit/             Unit tests (.phpt)
    integration/      Integration tests with SQLite (.phpt)
www/             Document root
  assets/        CSS, JS, images
```

### Useful make targets

```bash
make up              # Start all dev containers
make down            # Stop all containers
make shell           # PHP container shell
make logs            # Tail all logs
make migrate         # Run phinx migrations (phinx CLI wrapper)
make migrate-cmd     # Run migrations via bin/console
make clear-cache     # Delete temp/cache via bin/console
make stan            # PHPStan analysis
make test            # PHPUnit tests
```

Run Nette Tester tests:

```bash
docker-compose exec app vendor/bin/tester tests/tester/ -s
```

### Adding a new CLI command

1. Create `app/Command/MyCommand.php`:
   ```php
   #[AsCommand(name: 'app:my-command', description: '…')]
   final class MyCommand extends Command { … }
   ```
2. Register in `app/Config/services.neon`:
   ```neon
   - App\Command\MyCommand
   ```
3. Run: `make console cmd="app:my-command"`

### Adding a new route

Edit `app/Router/RouterFactory.php`. Add Czech and English variants:

```php
$router->addRoute('/moje-stranka', 'MyPage:default');
$router->addRoute('/en/my-page', ['presenter' => 'MyPage', 'action' => 'default', 'locale' => 'en']);
```

Add translation keys to `app/Lang/messages.cs.neon` and `app/Lang/messages.en.neon`.

### Translation usage in Latte

```latte
{_'nav.home'}              {* function syntax *}
{'nav.home'|translate}     {* filter syntax *}
```

### Code quality

```bash
make stan    # PHPStan level configured in phpstan.neon
make test    # PHPUnit (tests/Model/)
docker-compose exec app vendor/bin/tester tests/tester/ -s  # Nette Tester
```

---

## Česky

### Technologie

Viz tabulka výše (anglická část).

### Struktura projektu

Viz anglická část.

### Přidání CLI příkazu

1. Vytvořte `app/Command/MojePrikaz.php` (viz výše).
2. Zaregistrujte v `app/Config/services.neon`.
3. Spusťte: `make console cmd="app:moje-prikaz"`

### Přidání nové routy

Upravte `app/Router/RouterFactory.php`, přidejte českou i anglickou verzi.
Přidejte překlady do obou souborů v `app/Lang/`.

### Překlady v Latte šablonách

```latte
{_'nav.home'}
{'nav.home'|translate}
```

### Kontrola kvality kódu

```bash
make stan    # statická analýza
make test    # PHPUnit testy
```
