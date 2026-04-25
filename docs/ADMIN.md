# Admin Panel Guide / Průvodce administrací

## English

### Accessing the admin panel

URL: `/admin` → redirects to `/admin/sign/in`

Default credentials are set during setup (see SETUP.md). Sessions expire after **2 hours of inactivity**.

### Sections

| Section | URL | Description |
|---|---|---|
| Dashboard | `/admin/dashboard` | Page views, messages, meetings overview |
| Blog | `/admin/blog` | Create/edit/delete articles, manage comments |
| Projects | `/admin/projects` | Portfolio items |
| Services | `/admin/services` | Service offerings |
| Pricing | `/admin/pricing` | Pricing plans |
| Messages | `/admin/messages` | Contact form submissions |
| Meetings | `/admin/meetings` | Booking requests (calendar view) |
| Newsletter | `/admin/newsletter` | Subscriber list, send campaign |
| Analytics | `/admin/analytics` | Page views chart and top pages |
| Skills | `/admin/skills` | Skills/technologies displayed on About page |
| Testimonials | `/admin/testimonials` | Client testimonials |
| Settings | `/admin/settings` | GA4 tag, site-wide settings |
| Profile | `/admin/profile` | Change your password |

### Sending a newsletter

1. Open **Newsletter** in the admin panel.
2. Click **Nový newsletter** (New newsletter).
3. Write the subject and HTML body.
4. Click **Odeslat** (Send).

Or via CLI (useful for automation):

```bash
# From a file
make console cmd="app:send-newsletter 'Monthly update' --body-file=newsletter.html"

# Dry run (shows recipient count without sending)
make console cmd="app:send-newsletter 'Test' --dry-run"
```

### Exporting meetings

```bash
# All meetings
make export-meetings args="--output=/tmp/meetings.csv"

# Date range
make export-meetings args="--from=2024-01-01 --to=2024-12-31 --output=/tmp/2024.csv"
```

### CLI reference

```bash
make console cmd="<command>"  # run any bin/console command inside Docker

php bin/console list           # list all available commands
php bin/console app:migrate    # run pending migrations
php bin/console app:seed       # seed sample data
php bin/console app:create-admin email password [name]
php bin/console app:clear-cache
php bin/console app:generate-sitemap
php bin/console app:cleanup    # GDPR data retention
php bin/console app:export-meetings [--from=Y-m-d] [--to=Y-m-d] [--output=file.csv]
php bin/console app:send-newsletter subject [--body-file=file.html] [--dry-run]
```

---

## Česky

### Přístup do administrace

URL: `/admin` → přesměruje na `/admin/sign/in`

Přihlašovací údaje se nastaví při inicializaci (viz SETUP.md). Sezení vyprší po **2 hodinách nečinnosti**.

### Sekce administrace

| Sekce | URL | Popis |
|---|---|---|
| Dashboard | `/admin/dashboard` | Přehled návštěv, zpráv a rezervací |
| Blog | `/admin/blog` | Tvorba/editace/mazání článků, komentáře |
| Projekty | `/admin/projects` | Portfolio |
| Služby | `/admin/services` | Nabídka služeb |
| Ceník | `/admin/pricing` | Cenové plány |
| Zprávy | `/admin/messages` | Kontaktní formulář |
| Rezervace | `/admin/meetings` | Kalendář rezervací |
| Newsletter | `/admin/newsletter` | Odběratelé, odesílání kampaně |
| Analytika | `/admin/analytics` | Graf návštěvnosti a nejnavštěvovanější stránky |
| Dovednosti | `/admin/skills` | Technologie na stránce O mně |
| Reference | `/admin/testimonials` | Recenze od klientů |
| Nastavení | `/admin/settings` | GA4 tag a globální nastavení |
| Profil | `/admin/profile` | Změna hesla |

### CLI příkazy

Viz anglická část výše.
