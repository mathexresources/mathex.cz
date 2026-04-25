<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class ServiceSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return [];
    }

    public function run(): void
    {
        $this->table('services')->truncate();

        $services = [
            [
                'slug'           => 'webove-aplikace',
                'icon'           => '⚡',
                'title_cs'       => 'Webové aplikace na míru',
                'title_en'       => 'Custom Web Applications',
                'description_cs' => 'Navrhuji a vyvíjím komplexní webové aplikace v PHP a Nette Frameworku. Od jednoduchých firemních webů po složité systémy se správou uživatelů, workflow a integrací třetích stran.',
                'description_en' => 'I design and develop complex web applications in PHP and Nette Framework. From simple corporate websites to complex systems with user management, workflow, and third-party integrations.',
                'features_cs'    => json_encode([
                    'PHP 8.2+ a Nette Framework',
                    'Čistý, testovatelný kód (PSR, PHPStan)',
                    'Responzivní design (mobile-first)',
                    'Optimalizace výkonu a SEO',
                    'Zabezpečení dle OWASP',
                    'CI/CD pipeline a Docker',
                    'Dokumentace a předání zdrojového kódu',
                ]),
                'features_en'    => json_encode([
                    'PHP 8.2+ and Nette Framework',
                    'Clean, testable code (PSR, PHPStan)',
                    'Responsive design (mobile-first)',
                    'Performance optimisation and SEO',
                    'OWASP security best practices',
                    'CI/CD pipeline and Docker',
                    'Documentation and source code handover',
                ]),
                'order_index'    => 1,
                'active'         => 1,
            ],
            [
                'slug'           => 'eshopy',
                'icon'           => '🛒',
                'title_cs'       => 'E-shopy a online prodej',
                'title_en'       => 'E-commerce & Online Stores',
                'description_cs' => 'Stavím výkonné e-shopy s napojením na platební brány (GoPay, Stripe, Comgate), dopravce (Zásilkovna, PPL, GLS), fakturační systémy a XML feedy pro Heureku a Zboží.cz.',
                'description_en' => 'I build high-performance e-commerce stores integrated with payment gateways (GoPay, Stripe, Comgate), couriers (Zásilkovna, PPL, GLS), invoicing systems, and XML feeds for comparison sites.',
                'features_cs'    => json_encode([
                    'Napojení na GoPay, Stripe, Comgate',
                    'Zásilkovna, PPL, GLS, Česká pošta',
                    'XML feedy: Heureka, Zboží.cz, Google Shopping',
                    'Správa skladu a variant produktů',
                    'Věrnostní program a slevové kódy',
                    'Napojení na Pohoda / Money S3',
                    'GDPR a cookies souhlas',
                ]),
                'features_en'    => json_encode([
                    'GoPay, Stripe, Comgate payment gateways',
                    'Zásilkovna, PPL, GLS, Czech Post',
                    'XML feeds: Heureka, Zboží.cz, Google Shopping',
                    'Inventory and product variant management',
                    'Loyalty programme and discount codes',
                    'Pohoda / Money S3 accounting integration',
                    'GDPR and cookie consent',
                ]),
                'order_index'    => 2,
                'active'         => 1,
            ],
            [
                'slug'           => 'rest-api',
                'icon'           => '🔌',
                'title_cs'       => 'REST API a integrace',
                'title_en'       => 'REST API & Integrations',
                'description_cs' => 'Navrhuji a implementuji REST API pro mobilní aplikace, propojení systémů a microservices. Pracuji s OpenAPI specifikací, JWT autentizací a zajišťuji backward compatibility.',
                'description_en' => 'I design and implement REST APIs for mobile apps, system integrations, and microservices. I work with OpenAPI specs, JWT authentication, and ensure backward compatibility.',
                'features_cs'    => json_encode([
                    'REST API dle OpenAPI 3.0 specifikace',
                    'JWT / OAuth2 autentizace',
                    'Rate limiting přes Redis',
                    'Verzování API (v1, v2...)',
                    'Swagger / ReDoc dokumentace',
                    'Webhooks a event-driven integrace',
                    'Postman kolekce a testy',
                ]),
                'features_en'    => json_encode([
                    'REST API per OpenAPI 3.0 spec',
                    'JWT / OAuth2 authentication',
                    'Redis-backed rate limiting',
                    'API versioning (v1, v2...)',
                    'Swagger / ReDoc documentation',
                    'Webhooks and event-driven integrations',
                    'Postman collections and tests',
                ]),
                'order_index'    => 3,
                'active'         => 1,
            ],
            [
                'slug'           => 'konzultace',
                'icon'           => '💡',
                'title_cs'       => 'Technické konzultace & Code Review',
                'title_en'       => 'Technical Consulting & Code Review',
                'description_cs' => 'Pomáhám vývojovým týmům zlepšit kvalitu kódu, nastavit procesy a vybrat správné technologie. Provádím code review, audit bezpečnosti a refaktoring legacy kódu.',
                'description_en' => 'I help development teams improve code quality, set up processes, and choose the right technologies. I provide code reviews, security audits, and legacy code refactoring.',
                'features_cs'    => json_encode([
                    'Code review a audit kvality kódu',
                    'Bezpečnostní audit webové aplikace',
                    'Refaktoring a tech-debt cleanup',
                    'Nastavení CI/CD a DevOps pipeline',
                    'Mentoring junior vývojářů',
                    'Volba technologického stacku',
                    'Konzultace architektury a databáze',
                ]),
                'features_en'    => json_encode([
                    'Code review and quality audit',
                    'Web application security audit',
                    'Refactoring and tech-debt cleanup',
                    'CI/CD and DevOps pipeline setup',
                    'Junior developer mentoring',
                    'Technology stack selection',
                    'Architecture and database consulting',
                ]),
                'order_index'    => 4,
                'active'         => 1,
            ],
        ];

        $this->table('services')->insert($services)->saveData();
        $this->output->writeln('  <info>ServiceSeeder</info>: ' . count($services) . ' services inserted');
    }
}
