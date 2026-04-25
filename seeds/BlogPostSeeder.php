<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class BlogPostSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->table('blog_comments')->truncate();
        $this->table('blog_posts')->truncate();
        $this->execute('SET FOREIGN_KEY_CHECKS=1');

        $authorId = (int) $this->fetchRow('SELECT id FROM users LIMIT 1')['id'];

        $posts = [
            [
                'slug'             => 'docker-pro-php-vyvojare',
                'title_cs'         => 'Docker pro PHP vývojáře: Kompletní průvodce od vývoje k produkci',
                'title_en'         => 'Docker for PHP Developers: Complete Guide from Dev to Production',
                'perex_cs'         => 'Naučte se využít Docker pro vývoj PHP aplikací na Nette Frameworku. Pokryjeme vše od základní konfigurace přes multi-stage buildy až po CI/CD pipeline s GitHub Actions.',
                'perex_en'         => 'Learn how to use Docker for PHP application development with Nette Framework. We cover everything from basic configuration to multi-stage builds and CI/CD pipelines with GitHub Actions.',
                'content_cs'       => '<h2>Proč Docker?</h2><p>Klasický problém každého vývojáře: „U mě to funguje." Docker tento problém řeší tím, že celé vývojové prostředí zabalí do kontejnerů, které se chovají stejně na MacOS, Windows i Linux serveru.</p><h2>Základní docker-compose.yml pro PHP/Nette</h2><pre><code>services:
  app:
    build: .
    volumes:
      - .:/var/www/html
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
  mysql:
    image: mysql:8.0
  redis:
    image: redis:alpine</code></pre><h2>Multi-stage Dockerfile</h2><p>Multi-stage build odděluje vývojové a produkční prostředí v jednom souboru. Ve vývojové fázi máme Xdebug a vypnutý OPcache. V produkci je OPcache zapnutý a Xdebug chybí.</p><h2>CI/CD s GitHub Actions</h2><p>Automatický build a deploy na každý push do main větve zajistí, že do produkce nikdy neprojde nefunkční kód.</p>',
                'content_en'       => '<h2>Why Docker?</h2><p>The classic developer problem: "It works on my machine." Docker solves this by packaging the entire development environment into containers that behave identically on macOS, Windows, and Linux servers.</p><h2>CI/CD with GitHub Actions</h2><p>Automatic build and deploy on every push to the main branch ensures broken code never reaches production.</p>',
                'cover_image'      => '/assets/images/blog/docker-php.webp',
                'tags'             => json_encode(['docker', 'php', 'devops', 'nette', 'ci-cd']),
                'author_id'        => $authorId,
                'status'           => 'published',
                'reading_time_min' => 9,
                'views_count'      => 847,
                'published_at'     => '2024-02-12 09:00:00',
                'created_at'       => '2024-02-10 14:00:00',
                'updated_at'       => '2024-02-12 09:00:00',
                'seo_title_cs'     => 'Docker pro PHP vývojáře – průvodce | Mathex.cz',
                'seo_desc_cs'      => 'Kompletní průvodce Dockerem pro PHP a Nette vývojáře. Multi-stage buildy, docker-compose a CI/CD s GitHub Actions.',
            ],
            [
                'slug'             => 'proc-nette-framework-2024',
                'title_cs'         => 'Proč jsem v roce 2024 stále věrný Nette Frameworku',
                'title_en'         => 'Why I Still Choose Nette Framework in 2024',
                'perex_cs'         => 'Laravel? Symfony? Po více než 8 letech vývoje v PHP vysvětluji, proč pro většinu projektů volím Nette Framework a kdy bych sáhl po alternativě.',
                'perex_en'         => 'Laravel? Symfony? After 8+ years of PHP development I explain why I choose Nette Framework for most projects and when I would reach for an alternative.',
                'content_cs'       => '<h2>Čistá architektura bez magie</h2><p>Nette Framework vás nenechá schovat špatný kód za fasádu elegantní syntaxe. Každá závislost je explicitní, dependency injection kontejner je průhledný a výsledný kód je snadné číst i po dvou letech.</p><h2>Tracy – nejlepší debugger pro PHP</h2><p>Tracy Debugger je z mého pohledu nejlepší nástroj pro ladění PHP aplikací. Bluescreen s kompletním call stackem, profiler, dump funkcí – vše co potřebujete.</p><h2>Kdy bych volil Laravel?</h2><p>Pokud potřebuji rychlý prototyp s rozsáhlým ekosystémem balíčků, nebo klient hledá vývojáře na long-term maintenance a preferuje více rozšířenou platformu.</p><h2>Závěr</h2><p>Nette není pro každého. Ale pro vývojáře, který chce mít kontrolu nad kódem a netoleruje magii, je to skvělá volba i v roce 2024.</p>',
                'content_en'       => '<h2>Clean Architecture Without Magic</h2><p>Nette Framework won\'t let you hide bad code behind elegant syntax. Every dependency is explicit, the DI container is transparent, and the code is easy to read even two years later.</p><h2>When Would I Choose Laravel?</h2><p>If I need a quick prototype with an extensive package ecosystem, or a client is looking for developers for long-term maintenance and prefers a more widely adopted platform.</p>',
                'cover_image'      => '/assets/images/blog/nette-framework.webp',
                'tags'             => json_encode(['php', 'nette', 'framework', 'architektura']),
                'author_id'        => $authorId,
                'status'           => 'published',
                'reading_time_min' => 7,
                'views_count'      => 1243,
                'published_at'     => '2024-03-05 09:00:00',
                'created_at'       => '2024-03-03 11:00:00',
                'updated_at'       => '2024-03-05 09:00:00',
                'seo_title_cs'     => 'Proč Nette Framework v roce 2024 | Mathex.cz',
                'seo_desc_cs'      => 'Porovnání PHP frameworků z pohledu freelancera. Proč volit Nette Framework a kdy sáhnout po Laravelu nebo Symfony.',
            ],
            [
                'slug'             => 'redis-session-handler-nette',
                'title_cs'         => 'Redis jako session handler v Nette: kompletní průvodce',
                'title_en'         => 'Redis as Session Handler in Nette: Complete Guide',
                'perex_cs'         => 'Výchozí file-based sessions v PHP nestačí pro horizontálně škálované aplikace. Naučte se nahradit je Redisem pomocí contributte/redis v Nette Frameworku.',
                'perex_en'         => 'Default file-based PHP sessions don\'t work for horizontally scaled applications. Learn how to replace them with Redis using contributte/redis in Nette Framework.',
                'content_cs'       => '<h2>Proč Redis pro sessions?</h2><p>Pokud provozujete aplikaci na více serverech (load balancer), file-based sessions selžou – uživatel přihlášený na serveru #1 bude na serveru #2 odhlášen. Redis jako centralizované úložiště tento problém eliminuje.</p><h2>Instalace</h2><pre><code>composer require contributte/redis</code></pre><h2>Konfigurace v neon</h2><pre><code>extensions:
    redis: Contributte\Redis\DI\RedisExtension

redis:
    connection:
        default:
            uri: redis://redis:6379

session:
    handler: @redis.connection.default.sessionHandler</code></pre><h2>Testování</h2><p>Ověřte funkčnost příkazem <code>redis-cli monitor</code> – při každém requestu uvidíte SESSION operace v logu Redisu.</p>',
                'content_en'       => '<h2>Why Redis for Sessions?</h2><p>If you run your application on multiple servers behind a load balancer, file-based sessions will fail — a user logged in on server #1 will be logged out on server #2. Redis as centralised storage eliminates this problem.</p>',
                'cover_image'      => '/assets/images/blog/redis-sessions.webp',
                'tags'             => json_encode(['redis', 'php', 'nette', 'sessions', 'scaling']),
                'author_id'        => $authorId,
                'status'           => 'published',
                'reading_time_min' => 6,
                'views_count'      => 634,
                'published_at'     => '2024-04-18 09:00:00',
                'created_at'       => '2024-04-16 15:00:00',
                'updated_at'       => '2024-04-18 09:00:00',
                'seo_title_cs'     => 'Redis session handler v Nette Frameworku | Mathex.cz',
                'seo_desc_cs'      => 'Jak nakonfigurovat Redis jako session handler v Nette Frameworku. Průvodce krok za krokem s contributte/redis.',
            ],
            [
                'slug'             => 'phpstan-pro-zacatecniky',
                'title_cs'         => 'PHPStan pro začátečníky: statická analýza, která zachrání váš projekt',
                'title_en'         => 'PHPStan for Beginners: Static Analysis That Will Save Your Project',
                'perex_cs'         => 'PHPStan odhaluje chyby v PHP kódu bez spuštění aplikace. Ukážu vám, jak ho zapojit do projektu, co jednotlivé levely znamenají a jak zpracovat první vlnu chyb.',
                'perex_en'         => 'PHPStan finds bugs in PHP code without running the application. I\'ll show you how to integrate it, what the levels mean, and how to handle the first wave of errors.',
                'content_cs'       => '<h2>Co je statická analýza?</h2><p>PHPStan analyzuje váš kód a hledá typové nesrovnalosti, volání neexistujících metod nebo přístup k neexistujícím proměnným – vše bez toho, aby kód spustil.</p><h2>Instalace</h2><pre><code>composer require --dev phpstan/phpstan phpstan/phpstan-nette</code></pre><h2>Konfigurace phpstan.neon</h2><pre><code>parameters:
    level: 5
    paths:
        - app</code></pre><h2>Jak postupovat s legacy kódem?</h2><p>Začněte na levelu 0 a postupně zvyšujte. Nepokoušejte se opravit vše najednou – opravujte po modulech.</p>',
                'content_en'       => '<h2>What Is Static Analysis?</h2><p>PHPStan analyses your code looking for type mismatches, calls to non-existent methods, or access to undefined variables — all without actually running the code.</p>',
                'cover_image'      => '/assets/images/blog/phpstan.webp',
                'tags'             => json_encode(['php', 'phpstan', 'quality', 'testing']),
                'author_id'        => $authorId,
                'status'           => 'published',
                'reading_time_min' => 5,
                'views_count'      => 412,
                'published_at'     => '2024-05-22 09:00:00',
                'created_at'       => '2024-05-20 13:00:00',
                'updated_at'       => '2024-05-22 09:00:00',
                'seo_title_cs'     => 'PHPStan – statická analýza kódu pro PHP | Mathex.cz',
                'seo_desc_cs'      => 'Průvodce PHPStan pro PHP vývojáře. Jak nainstalovat, nakonfigurovat a používat statickou analýzu na projektu v Nette Frameworku.',
            ],
            [
                'slug'             => 'jak-ocenit-webovy-projekt',
                'title_cs'         => 'Jak správně ocenit webový projekt: zkušenosti z praxe',
                'title_en'         => 'How to Price a Web Project Correctly: Lessons from Practice',
                'perex_cs'         => 'Podceňování ceny zakázky je nejčastější chybou freelancerů. Sdílím svůj postup při tvorbě cenové nabídky a nejčastější chyby, které mě stály tisíce korun.',
                'perex_en'         => 'Underpricing is the most common freelancer mistake. I share my process for creating project estimates and the costly errors that taught me hard lessons.',
                'content_cs'       => '<h2>Hodinová sazba nebo fixní cena?</h2><p>Pro jasně definované projekty s podrobnou specifikací preferuji fixní cenu – klient ví, co zaplatí, já vím, co dodat. Pro projekty s nejasným rozsahem volím hodinovou sazbu.</p><h2>Jak odhadnout hodinovou náročnost?</h2><p>Rozložím projekt na dílčí úkoly, každý odhadnu v hodinách a přidám 30% buffer na nepředvídané komplikace. Lepší je nabídnout vyšší cenu a vrátit část peněz, než skončit ve ztrátě.</p><h2>Co nezapomenout do cenové nabídky</h2><ul><li>Analýza a návrh architektury</li><li>Testování a code review</li><li>Deployment a konfigurace serveru</li><li>Zaškolení klienta</li><li>Dokumentace</li></ul>',
                'content_en'       => '<h2>Fixed Price or Hourly Rate?</h2><p>For clearly defined projects with detailed specs, I prefer fixed price — the client knows what they\'ll pay, I know what to deliver. For projects with unclear scope, I use hourly billing.</p>',
                'cover_image'      => '/assets/images/blog/pricing-project.webp',
                'tags'             => json_encode(['freelance', 'business', 'pricing', 'cenotvorba']),
                'author_id'        => $authorId,
                'status'           => 'draft',
                'reading_time_min' => 8,
                'views_count'      => 0,
                'published_at'     => null,
                'created_at'       => '2024-06-01 11:00:00',
                'updated_at'       => '2024-06-01 11:00:00',
                'seo_title_cs'     => null,
                'seo_desc_cs'      => null,
            ],
        ];

        $this->table('blog_posts')->insert($posts)->saveData();
        $this->output->writeln('  <info>BlogPostSeeder</info>: ' . count($posts) . ' posts inserted (4 published, 1 draft)');
    }
}
